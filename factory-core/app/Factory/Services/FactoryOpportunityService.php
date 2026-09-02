<?php

namespace App\Factory\Services;

use App\Models\FactoryIntake;
use App\Models\FactoryOpportunity;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class FactoryOpportunityService
{
    public function materializeApprovedAnalysis(FactoryIntake $intake): array
    {
        if ($intake->analysis_status !== 'approved') {
            throw new InvalidArgumentException('Only an approved Factory AI analysis can create opportunities.');
        }

        if ($intake->output_mode !== 'opportunity') {
            throw new InvalidArgumentException('Factory intake is not configured for opportunity output.');
        }

        $analysis = $intake->ai_analysis ?? [];
        $opportunities = Arr::get($analysis, 'opportunities', []);

        if (! is_array($opportunities)) {
            throw new InvalidArgumentException('Approved analysis has no valid opportunities collection.');
        }

        $created = [];

        foreach ($opportunities as $item) {
            if (! is_array($item) || empty($item['title']) || empty($item['opportunity_type'])) {
                continue;
            }

            $opportunity = FactoryOpportunity::updateOrCreate(
                [
                    'intake_id' => $intake->id,
                    'title' => $item['title'],
                    'source_url' => $item['source_url'] ?? null,
                ],
                [
                    'product_id' => $intake->product_id,
                    'profile_type' => Arr::get($analysis, 'profile_type'),
                    'opportunity_type' => $item['opportunity_type'],
                    'status' => $item['status'] ?? 'identified',
                    'organization' => $item['organization'] ?? null,
                    'territory' => $item['territory'] ?? null,
                    'source' => $item['source'] ?? null,
                    'deadline_at' => $item['deadline_at'] ?? null,
                    'match_score' => $item['match_score'] ?? null,
                    'match_analysis' => $item['match_analysis'] ?? null,
                    'requirements' => $item['requirements'] ?? null,
                    'gaps' => $item['gaps'] ?? null,
                    'action_plan' => $item['action_plan'] ?? null,
                    'evidence' => $item['evidence'] ?? null,
                    'opportunity_dna' => array_merge($item['opportunity_dna'] ?? [], [
                        'generated_from_intake_id' => $intake->id,
                        'factory_contract' => 'factory.intake.analysis.v3',
                        'profile_type' => Arr::get($analysis, 'profile_type'),
                    ]),
                    'qualified_at' => ($item['match_score'] ?? 0) > 0 ? now() : null,
                ]
            );

            $created[] = $opportunity;
        }

        $intake->forceFill([
            'status' => 'converted',
            'intake_dna' => array_merge($intake->intake_dna ?? [], [
                'opportunities_materialized' => true,
                'opportunities_count' => count($created),
                'materialized_at' => now()->toIso8601String(),
            ]),
        ])->save();

        return $created;
    }

    public function recalculateMatching(FactoryOpportunity $opportunity, FactoryOpportunityMatchingEngine $matching): FactoryOpportunity
    {
        $opportunity->loadMissing(['intake', 'product']);

        $profileDna = $opportunity->intake?->profile_dna
            ?? $opportunity->product?->product_dna
            ?? [];

        if (! is_array($profileDna) || $profileDna === []) {
            throw new InvalidArgumentException('Opportunity has no profile DNA available for matching.');
        }

        $request = $matching->buildAssessmentRequest($opportunity, $profileDna);
        $assessment = $this->callRoteia(
            $request,
            'Você é o avaliador de oportunidades da Vitrine IA Pro Factory. Retorne somente JSON válido aderente ao contrato. Avalie evidências por critério, não calcule a nota final e não invente documentos, elegibilidade ou fatos.'
        );

        $result = $matching->calculate($assessment, null, $opportunity->profile_type);
        $level = (string) ($result['match_level'] ?? 'low');

        $opportunity->forceFill([
            'match_score' => $result['match_score'] ?? 0,
            'match_analysis' => $result,
            'gaps' => $result['gaps'] ?? [],
            'action_plan' => $result['action_plan'] ?? [],
            'status' => in_array($level, ['partial', 'high', 'very_high'], true)
                ? 'qualified'
                : ($opportunity->status === 'qualified' ? 'identified' : $opportunity->status),
            'qualified_at' => in_array($level, ['partial', 'high', 'very_high'], true) ? now() : null,
            'opportunity_dna' => array_merge($opportunity->opportunity_dna ?? [], [
                'matching_contract' => FactoryOpportunityMatchingEngine::CONTRACT,
                'matching_engine' => $result['engine'] ?? null,
                'matching_recalculated_at' => now()->toIso8601String(),
            ]),
        ])->save();

        return $opportunity->refresh();
    }

    public function generateActions(FactoryOpportunity $opportunity, FactoryOpportunityActionEngine $engine): array
    {
        if (! is_array($opportunity->match_analysis) || $opportunity->match_analysis === []) {
            throw new InvalidArgumentException('Run opportunity matching before generating actions.');
        }

        $request = $engine->buildActionRequest($opportunity);
        $plan = $this->callRoteia(
            $request,
            'Você é o planejador operacional da Vitrine IA Pro Factory. Retorne somente JSON válido aderente ao contrato. Transforme gaps, requisitos não atendidos, riscos e bloqueios em ações concretas. Não marque ações como concluídas e não invente evidências.'
        );

        return $engine->materialize($opportunity->fresh(), $plan);
    }

    private function callRoteia(array $request, string $system): array
    {
        $baseUrl = rtrim((string) env('ROTEIA_BASE_URL', 'https://api.roteia.ai/v1'), '/');
        $apiKey = trim((string) env('ROTEIA_API_KEY', ''));
        $model = (string) env('ROTEIA_MODEL', 'deepseek/deepseek-v4-flash');
        $timeout = max(10, (int) env('ROTEIA_TIMEOUT', 60));

        if ($apiKey === '') {
            throw new RuntimeException('ROTEIA_API_KEY ausente no ambiente da Factory.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    [
                        'role' => 'user',
                        'content' => json_encode($request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    ],
                ],
                'temperature' => 0.1,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Roteia HTTP '.$response->status().': '.mb_substr($response->body(), 0, 1000));
        }

        $content = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
        if ($content === '') {
            throw new RuntimeException('Roteia respondeu sem conteúdo em choices[0].message.content.');
        }

        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
            $content = preg_replace('/\s*```$/', '', $content) ?? $content;
            $content = trim($content);
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Roteia retornou JSON inválido: '.json_last_error_msg());
        }

        return $decoded;
    }
}
