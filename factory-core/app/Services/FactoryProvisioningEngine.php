<?php

namespace App\Services;

use App\Models\FactoryHomologation;
use App\Models\FactoryProduct;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FactoryProvisioningEngine
{
    public function plan(FactoryProduct $product): array
    {
        $dna = $product->product_dna ?? [];
        $deployment = app(FactoryDeploymentEngine::class)->plan($product);
        $health = app(FactoryHealthEngine::class)->plan($product);

        $issues = array_values(array_unique(array_merge(
            $deployment['issues'] ?? [],
            $health['issues'] ?? [],
        )));

        return [
            'ready' => $issues === [],
            'product' => $product->name,
            'environment' => $dna['environment'] ?? 'hml',
            'strategy' => $dna['provisioning_strategy'] ?? 'controlled-compose',
            'executor' => 'centro-operacional',
            'confirmation_required' => true,
            'steps' => [
                'workspace_validation',
                'repository_validation',
                'configuration_validation',
                'compose_validation',
                'build',
                'start_services',
                'health_checks',
                'provision_hml_route',
                'collect_evidence',
                'mark_homologation_ready',
            ],
            'deployment' => $deployment,
            'health' => $health,
            'issues' => $issues,
        ];
    }

    public function provisionHml(FactoryProduct $product): array
    {
        $dna = $product->product_dna ?? [];
        $baseUrl = rtrim((string) env('FACTORY_OPS_BASE_URL', 'http://vitrine_ops_api_hml:8080'), '/');
        $token = (string) env('FACTORY_OPS_TOKEN', '');

        if ($token === '') {
            throw new RuntimeException('FACTORY_OPS_TOKEN não configurado.');
        }

        $projectId = (string) ($dna['project_id'] ?? $product->slug);
        $hostname = (string) ($dna['hml_domain'] ?? $dna['domain'] ?? ($product->slug . '.hml.vitrineiapro.com.br'));
        $upstream = (string) ($dna['hml_upstream'] ?? $dna['upstream'] ?? '');
        $healthPath = (string) ($dna['health_path'] ?? '/health');

        if ($upstream === '') {
            throw new RuntimeException('hml_upstream/upstream não definido no product_dna.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(930)
            ->post($baseUrl . '/routing/hml/provision', [
                'project_id' => $projectId,
                'hostname' => $hostname,
                'upstream' => $upstream,
                'health_path' => $healthPath,
                'route_id' => $dna['hml_route_id'] ?? null,
            ]);

        $payload = $response->json();
        if (! $response->successful() || ! is_array($payload) || ! ($payload['ok'] ?? false)) {
            $detail = is_array($payload) ? ($payload['detail'] ?? $payload) : $response->body();
            throw new RuntimeException('Falha ao provisionar HML: ' . json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $homologation = FactoryHomologation::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'url' => $payload['url'] ?? ('https://' . $hostname),
            ],
            [
                'status' => 'testing',
                'health_status' => 'healthy',
                'checks' => [
                    'route_id' => $payload['route_id'] ?? null,
                    'health_url' => $payload['health_url'] ?? null,
                    'routing_status' => $payload['status'] ?? null,
                ],
                'evidence' => [
                    'executor' => 'vitrine-ops-api',
                    'exit_code' => $payload['exit_code'] ?? null,
                    'provisioned_at' => now()->toISOString(),
                ],
            ]
        );

        return [
            'ok' => true,
            'homologation_id' => $homologation->id,
            'url' => $homologation->url,
            'health_status' => $homologation->health_status,
            'routing' => $payload,
        ];
    }
}
