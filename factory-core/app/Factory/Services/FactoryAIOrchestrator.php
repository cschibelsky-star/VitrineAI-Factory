<?php

namespace App\Factory\Services;

use App\Models\FactoryBlueprint;
use App\Models\FactoryCapability;
use App\Models\FactoryIntake;
use App\Models\FactoryMission;
use App\Models\FactoryProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class FactoryAIOrchestrator
{
    public function __construct(
        private readonly FactoryProfileSchemaRegistry $profileSchemas,
    ) {
    }

    public function buildAnalysisRequest(FactoryIntake $intake): array
    {
        $requiredOutput = [
            'profile_type',
            'profile_dna',
            'master_prompt',
            'analysis',
            'reference_assessment',
            'assumptions',
            'open_decisions',
        ];

        if ($intake->output_mode === 'opportunity') {
            $requiredOutput[] = 'opportunities';
        } else {
            array_push($requiredOutput, 'project', 'blueprint', 'capabilities', 'missions');
        }

        return [
            'contract' => 'factory.intake.analysis.v3',
            'objective' => 'Transform a short human need into the correct persistent profile/DNA and route it to either product construction or opportunity operation.',
            'input' => [
                'title' => $intake->title,
                'request' => $intake->request,
                'origin' => $intake->origin,
                'output_mode' => $intake->output_mode,
                'type' => $intake->type,
                'priority' => $intake->priority,
                'references' => $intake->references ?? [],
                'linked_product' => $intake->product ? [
                    'id' => $intake->product->id,
                    'name' => $intake->product->name,
                    'slug' => $intake->product->slug,
                    'category' => $intake->product->category,
                    'product_dna' => $intake->product->product_dna,
                ] : null,
            ],
            'profile_classification' => [
                'instruction' => 'Classify the need into the best profile type before building the DNA. Use generic only when no specialized schema fits.',
                'available_schemas' => $this->profileSchemas->all(),
            ],
            'routing' => [
                'product' => 'Create or evolve Product -> Blueprint -> Capabilities -> Missions. Build, HML, Release and Deploy remain controlled pipeline stages.',
                'opportunity' => 'Return opportunities with type, source, deadline, match score, requirements, gaps, action plan and evidence. Do not force opportunity work into software build/deploy stages.',
            ],
            'rules' => [
                'references_are_context_not_copy_source' => true,
                'do_not_invent_repository_domain_credentials_or_external_integrations' => true,
                'separate_known_facts_from_recommendations' => true,
                'preserve_client_identity_and_improve_weak_patterns' => true,
                'suggest_direction_when_references_are_missing' => true,
                'prefer_reusable_blueprints_and_capabilities' => true,
                'catalog_product_origin_must_provision_not_redesign' => true,
                'reference_project_origin_must_extract_patterns_not_clone_client_data' => true,
                'deployment_execution_is_external_controlled' => true,
                'profile_dna_must_follow_selected_schema' => true,
                'opportunity_matches_must_explain_score_and_gaps' => true,
            ],
            'required_output' => $requiredOutput,
        ];
    }

    public function executeAnalysis(FactoryIntake $intake): FactoryIntake
    {
        $request = $this->buildAnalysisRequest($intake->fresh(['product']));
        $baseUrl = rtrim((string) env('ROTEIA_BASE_URL', 'https://api.roteia.ai/v1'), '/');
        $apiKey = trim((string) env('ROTEIA_API_KEY', ''));
        $model = (string) env('ROTEIA_MODEL', 'deepseek/deepseek-v4-flash');
        $timeout = max(10, (int) env('ROTEIA_TIMEOUT', 60));

        if ($apiKey === '') {
            throw new RuntimeException('ROTEIA_API_KEY ausente no ambiente da Factory.');
        }

        $prompt = "Você é o arquiteto de intake da Vitrine IA Pro Factory. Analise o contrato abaixo e responda SOMENTE com um objeto JSON válido, sem markdown, sem comentários e sem texto fora do JSON. Preserve fatos conhecidos, não invente credenciais, domínios, repositórios ou integrações.\n\n"
            . json_encode($request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Retorne apenas JSON estritamente válido aderente ao contrato solicitado.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.2,
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

        $analysis = json_decode($content, true);
        if (! is_array($analysis) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Roteia retornou JSON inválido: '.json_last_error_msg());
        }

        $intake->forceFill([
            'intake_dna' => array_merge($intake->intake_dna ?? [], [
                'ai_request' => $request,
                'ai_contract' => $request['contract'],
                'ai_provider' => 'roteia',
                'ai_model' => $model,
                'provider_execution_status' => 'completed',
            ]),
        ])->save();

        return $this->applyAnalysis($intake->fresh(['product']), $analysis);
    }

    public function applyAnalysis(FactoryIntake $intake, array $analysis): FactoryIntake
    {
        $required = ['profile_type', 'profile_dna', 'master_prompt'];
        $required = $intake->output_mode === 'opportunity'
            ? array_merge($required, ['opportunities'])
            : array_merge($required, ['project', 'blueprint', 'capabilities', 'missions']);

        foreach ($required as $key) {
            if (! array_key_exists($key, $analysis)) {
                throw new InvalidArgumentException("Factory AI analysis missing required key: {$key}");
            }
        }

        if (! in_array($analysis['profile_type'], $this->profileSchemas->types(), true)) {
            throw new InvalidArgumentException('Factory AI analysis returned an unknown profile_type.');
        }

        if (! is_array($analysis['profile_dna']) || ! is_string($analysis['master_prompt'])) {
            throw new InvalidArgumentException('Factory AI analysis has invalid profile_dna or master_prompt type.');
        }

        if ($intake->output_mode === 'opportunity' && ! is_array($analysis['opportunities'])) {
            throw new InvalidArgumentException('Factory AI opportunity analysis must return an opportunities array.');
        }

        $schema = $this->profileSchemas->get($analysis['profile_type']);
        $analysis['profile_schema'] = [
            'type' => $analysis['profile_type'],
            'label' => $schema['label'],
            'version' => '1',
        ];

        $intake->forceFill([
            'profile_dna' => $analysis['profile_dna'],
            'master_prompt' => trim($analysis['master_prompt']),
            'ai_analysis' => $analysis,
            'analysis_status' => 'ready',
            'analyzed_at' => now(),
            'intake_dna' => array_merge($intake->intake_dna ?? [], [
                'ai_contract' => 'factory.intake.analysis.v3',
                'profile_type' => $analysis['profile_type'],
                'profile_schema_label' => $schema['label'],
                'output_mode' => $intake->output_mode,
                'analysis_materialized' => false,
            ]),
        ])->save();

        return $intake->refresh();
    }

    public function materializeApprovedAnalysis(FactoryIntake $intake): FactoryProduct
    {
        if ($intake->output_mode === 'opportunity') {
            throw new InvalidArgumentException('Opportunity output must be materialized by FactoryOpportunityService.');
        }

        if ($intake->analysis_status !== 'approved') {
            throw new InvalidArgumentException('Only an approved Factory AI analysis can be materialized.');
        }

        $analysis = $intake->ai_analysis ?? [];
        $projectData = Arr::get($analysis, 'project', []);
        $blueprintData = Arr::get($analysis, 'blueprint', []);
        $capabilities = Arr::get($analysis, 'capabilities', []);
        $missions = Arr::get($analysis, 'missions', []);

        if (! is_array($projectData) || empty($projectData['name'])) {
            throw new InvalidArgumentException('Approved analysis does not contain a valid project definition.');
        }

        return DB::transaction(function () use ($intake, $analysis, $projectData, $blueprintData, $capabilities, $missions) {
            $product = $this->resolveProduct($intake, $projectData);
            $blueprint = $this->resolveBlueprint($intake, $product, $blueprintData);

            foreach ($capabilities as $capabilityData) {
                if (! is_array($capabilityData) || empty($capabilityData['name'])) {
                    continue;
                }

                $slug = $capabilityData['slug'] ?? Str::slug($capabilityData['name']);
                $capability = FactoryCapability::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $capabilityData['name'],
                        'category' => $capabilityData['category'] ?? $product->category,
                        'type' => $capabilityData['type'] ?? 'business',
                        'status' => 'active',
                        'version' => $capabilityData['version'] ?? '0.1',
                        'description' => $capabilityData['description'] ?? null,
                        'capability_dna' => array_merge($capabilityData['capability_dna'] ?? [], [
                            'generated_from_intake_id' => $intake->id,
                            'source' => 'factory_ai_orchestrator',
                            'profile_type' => $analysis['profile_type'] ?? 'generic',
                        ]),
                    ]
                );

                $blueprint->capabilities()->syncWithoutDetaching([$capability->id]);
            }

            foreach ($missions as $index => $missionData) {
                if (! is_array($missionData) || empty($missionData['title'])) {
                    continue;
                }

                FactoryMission::updateOrCreate(
                    ['product_id' => $product->id, 'title' => $missionData['title']],
                    [
                        'blueprint_id' => $blueprint->id,
                        'status' => 'planned',
                        'priority' => $missionData['priority'] ?? $intake->priority ?? 'normal',
                        'objective' => $missionData['objective'] ?? null,
                        'mission_dna' => array_merge($missionData['mission_dna'] ?? [], [
                            'generated_from_intake_id' => $intake->id,
                            'order' => $index + 1,
                            'execution_mode' => 'controlled',
                            'ai_provider' => 'roteia',
                            'profile_type' => $analysis['profile_type'] ?? 'generic',
                        ]),
                    ]
                );
            }

            $intake->forceFill([
                'product_id' => $product->id,
                'status' => 'converted',
                'intake_dna' => array_merge($intake->intake_dna ?? [], [
                    'analysis_materialized' => true,
                    'materialized_at' => now()->toIso8601String(),
                    'blueprint_id' => $blueprint->id,
                ]),
            ])->save();

            return $product->refresh();
        });
    }

    private function resolveProduct(FactoryIntake $intake, array $projectData): FactoryProduct
    {
        if (in_array($intake->origin, ['catalog_product', 'existing_evolution'], true) && $intake->product) {
            return $intake->product;
        }

        $slug = $projectData['slug'] ?? Str::slug($projectData['name']);

        return FactoryProduct::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $projectData['name'],
                'category' => $projectData['category'] ?? 'custom',
                'status' => 'architecture',
                'version' => $projectData['version'] ?? '0.1',
                'description' => $projectData['description'] ?? $intake->request,
                'product_dna' => array_merge($intake->profile_dna ?? [], $projectData['product_dna'] ?? [], [
                    'generated_from_intake_id' => $intake->id,
                    'master_prompt' => $intake->master_prompt,
                    'origin' => $intake->origin,
                    'profile_type' => data_get($intake->intake_dna, 'profile_type', 'generic'),
                ]),
            ]
        );
    }

    private function resolveBlueprint(FactoryIntake $intake, FactoryProduct $product, array $blueprintData): FactoryBlueprint
    {
        $name = $blueprintData['name'] ?? ('Blueprint '.$product->name);
        $slug = $blueprintData['slug'] ?? Str::slug($name);

        return FactoryBlueprint::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'category' => $blueprintData['category'] ?? $product->category,
                'status' => $blueprintData['status'] ?? 'draft',
                'version' => $blueprintData['version'] ?? '0.1',
                'source_product_id' => $intake->origin === 'reference_project' ? $intake->product_id : null,
                'description' => $blueprintData['description'] ?? 'Blueprint generated from Factory AI intake.',
                'blueprint_dna' => array_merge($blueprintData['blueprint_dna'] ?? [], [
                    'generated_from_intake_id' => $intake->id,
                    'origin' => $intake->origin,
                    'profile_type' => data_get($intake->intake_dna, 'profile_type', 'generic'),
                ]),
            ]
        );
    }
}
