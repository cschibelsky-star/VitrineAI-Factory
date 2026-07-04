<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;
use App\Models\FactoryProvisioningLog;

class FactoryIntelligenceService
{
    public function validateProject(FactoryProject $project): array
    {
        $issues = [];

        if (blank($project->name)) {
            $issues[] = 'Nome do projeto não informado.';
        }

        if (blank($project->domain)) {
            $issues[] = 'Domínio não informado.';
        }

        if (blank($project->github_repository)) {
            $issues[] = 'Repositório GitHub não informado.';
        }

        if (blank($project->deploy_path)) {
            $issues[] = 'Caminho de deploy não informado.';
        }

        return [
            'valid' => count($issues) === 0,
            'issues' => $issues,
        ];
    }

    public function diagnose(FactoryProject $project): array
    {
        $lastError = FactoryProvisioningLog::where('factory_project_id', $project->id)
            ->where('status', 'error')
            ->latest()
            ->first();

        $recommendations = [];

        if ($project->health_status === 'offline') {
            $recommendations[] = 'Verificar DNS, Document Root e SSL no cPanel.';
        }

        if ($project->provisioning_status === 'failed') {
            $recommendations[] = 'Abrir logs do provisionamento e reexecutar após corrigir o erro.';
        }

        if ($lastError && str_contains(strtolower($lastError->message ?? ''), 'clone')) {
            $recommendations[] = 'Validar chave SSH do GitHub, branch e permissão do repositório.';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Nenhuma ação crítica detectada.';
        }

        return [
            'last_error' => $lastError?->message,
            'recommendations' => $recommendations,
        ];
    }
}
