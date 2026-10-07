<?php

namespace App\Services;

use App\Models\FactoryProduct;
use Illuminate\Support\Str;

class FactoryGitHubEngine
{
    public function inspect(FactoryProduct $product): array
    {
        $repository = trim((string) $product->github_repository);
        $issues = [];

        if ($repository === '') {
            $issues[] = 'Repositório GitHub não informado.';
        }

        return [
            'ready' => $issues === [],
            'repository' => $repository ?: null,
            'repository_slug' => $repository ? $this->normalizeRepository($repository) : null,
            'issues' => $issues,
        ];
    }

    public function normalizeRepository(string $repository): string
    {
        $repository = trim($repository);
        $repository = preg_replace('#^git@github\.com:#', '', $repository) ?? $repository;
        $repository = preg_replace('#^https?://github\.com/#', '', $repository) ?? $repository;
        $repository = preg_replace('#\.git$#', '', $repository) ?? $repository;

        return trim($repository, '/');
    }

    public function suggestedBranch(FactoryProduct $product): string
    {
        $dna = $product->product_dna ?? [];

        return (string) ($dna['default_branch'] ?? 'main');
    }

    public function buildContext(FactoryProduct $product): array
    {
        $inspection = $this->inspect($product);

        return $inspection + [
            'product' => $product->name,
            'product_slug' => Str::slug($product->slug ?: $product->name),
            'version' => $product->version,
            'branch' => $this->suggestedBranch($product),
        ];
    }
}
