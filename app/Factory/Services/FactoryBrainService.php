<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;

class FactoryBrainService
{
    public function diagnoseProject(FactoryProject $project): array
    {
        $alerts = [];

        if ($project->provisioning_status === 'failed') {
            $alerts[] = 'Provisionamento com falha. Verificar logs antes de nova tentativa.';
        }

        if (($project->health_status ?? 'unknown') === 'offline') {
            $alerts[] = 'Ambiente offline. Verificar DNS, SSL e Document Root.';
        }

        if (empty($project->domain)) {
            $alerts[] = 'Projeto sem domínio configurado.';
        }

        if (empty($project->deploy_path)) {
            $alerts[] = 'Projeto sem caminho de deploy configurado.';
        }

        return [
            'status' => empty($alerts) ? 'stable' : 'attention',
            'alerts' => $alerts,
        ];
    }
}
