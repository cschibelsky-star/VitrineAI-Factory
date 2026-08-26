<?php

namespace App\Factory\Services;

use App\Models\FactoryArtifact;
use App\Models\FactoryProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FactoryExistingProjectAdoptionService
{
    /**
     * Adota um projeto já existente no ecossistema sem recriar código, banco,
     * Docker, branch ou infraestrutura. O manifesto recebido deve vir de uma
     * fonte operacional confiável (ex.: Centro Operacional V5).
     */
    public function adopt(array $manifest): FactoryProduct
    {
        $normalized = $this->normalizeManifest($manifest);

        $product = FactoryProduct::query()->updateOrCreate(
            ['slug' => $normalized['slug']],
            [
                'name' => $normalized['name'],
                'category' => $normalized['category'],
                'status' => 'adopted',
                'version' => $normalized['version'],
                'github_repository' => $normalized['repository_url'],
                'description' => $normalized['description'],
                'product_dna' => [
                    'origin' => 'existing_project_adoption',
                    'governance' => 'factory',
                    'operational_source' => 'v5_manifest',
                    'project_id' => $normalized['project_id'],
                    'workspace_root' => $normalized['workspace_root'],
                    'repository' => [
                        'url' => $normalized['repository_url'],
                        'branch' => $normalized['branch'],
                        'directory' => $normalized['repository_directory'],
                    ],
                    'docker' => $normalized['docker'],
                    'domains' => $normalized['domains'],
                    'deployment' => $normalized['deployment'],
                    'adoption_policy' => [
                        'preserve_code' => true,
                        'preserve_database' => true,
                        'preserve_docker' => true,
                        'preserve_branch' => true,
                        'no_automatic_rebuild' => true,
                        'no_automatic_deploy' => true,
                    ],
                ],
            ],
        );

        FactoryArtifact::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'mission_id' => null,
                'type' => 'adoption_snapshot',
                'version' => 'v1',
            ],
            [
                'stage' => 'adoption',
                'status' => 'captured',
                'title' => 'Snapshot de adoção do projeto existente',
                'location' => 'v5-manifest://'.$normalized['project_id'],
                'evidence' => [
                    'project_id' => $normalized['project_id'],
                    'repository_url' => $normalized['repository_url'],
                    'branch' => $normalized['branch'],
                    'compose_file' => Arr::get($normalized, 'docker.compose_file'),
                    'docker_project' => Arr::get($normalized, 'docker.project_name'),
                ],
                'metadata' => [
                    'manifest' => $manifest,
                    'adopted_without_mutation' => true,
                ],
            ],
        );

        return $product->fresh();
    }

    private function normalizeManifest(array $manifest): array
    {
        $projectId = trim((string) Arr::get($manifest, 'id', ''));
        $name = trim((string) Arr::get($manifest, 'name', ''));
        $workspaceRoot = trim((string) Arr::get($manifest, 'workspace_root', ''));
        $repositoryUrl = trim((string) Arr::get($manifest, 'repository.url', ''));
        $branch = trim((string) Arr::get($manifest, 'repository.branch', 'main'));
        $repositoryDirectory = trim((string) Arr::get($manifest, 'repository.directory', 'repository'));

        if ($projectId === '' || $name === '' || $workspaceRoot === '' || $repositoryUrl === '') {
            throw ValidationException::withMessages([
                'manifest' => 'Manifesto operacional incompleto para adoção.',
            ]);
        }

        if (! Str::startsWith($repositoryUrl, ['https://github.com/', 'git@github.com:'])) {
            throw ValidationException::withMessages([
                'repository.url' => 'A adoção aceita somente repositórios GitHub autorizados.',
            ]);
        }

        return [
            'project_id' => $projectId,
            'slug' => Str::slug($projectId),
            'name' => $name,
            'category' => (string) Arr::get($manifest, 'factory.category', 'existing-product'),
            'version' => (string) Arr::get($manifest, 'factory.version', 'adopted'),
            'description' => (string) Arr::get(
                $manifest,
                'factory.description',
                'Projeto existente adotado pela governança da Vitrine IA Pro Factory.'
            ),
            'workspace_root' => $workspaceRoot,
            'repository_url' => $repositoryUrl,
            'branch' => $branch !== '' ? $branch : 'main',
            'repository_directory' => $repositoryDirectory !== '' ? $repositoryDirectory : 'repository',
            'docker' => (array) Arr::get($manifest, 'docker', []),
            'domains' => (array) Arr::get($manifest, 'domains', []),
            'deployment' => (array) Arr::get($manifest, 'deployment', []),
        ];
    }
}
