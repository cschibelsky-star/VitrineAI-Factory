<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;

class FactoryBrainService
{
    public function recommendations(FactoryProject $project): array
    {
        $items = [];

        if (($project->provisioning_status ?? null) === 'failed') {
            $items[] = ['level' => 'critical', 'title' => 'Provisionamento com falha', 'message' => 'Revisar os logs do projeto e reexecutar apenas após corrigir domínio, path ou repositório.'];
        }

        if (($project->health_status ?? null) === 'offline') {
            $items[] = ['level' => 'warning', 'title' => 'Ambiente offline', 'message' => 'Validar DNS, SSL e Document Root no cPanel.'];
        }

        if (empty($project->document_root)) {
            $items[] = ['level' => 'info', 'title' => 'Document Root ausente', 'message' => 'Definir Document Root para facilitar a integração futura com cPanel.'];
        }

        if (empty($items)) {
            $items[] = ['level' => 'success', 'title' => 'Operação estável', 'message' => 'Nenhuma recomendação crítica encontrada para este projeto.'];
        }

        return $items;
    }
}
