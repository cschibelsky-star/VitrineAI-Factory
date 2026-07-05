<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;

class FactoryBrainService
{
    public function insights(): array
    {
        $failed = FactoryProject::where('provisioning_status', 'failed')->count();
        $offline = FactoryProject::where('health_status', 'offline')->count();
        $pendingCpanel = FactoryProject::where('cpanel_status', 'pending')->count();
        $running = FactoryProject::where('provisioning_status', 'running')->count();

        $items = [];

        if ($failed > 0) {
            $items[] = ['level' => 'danger', 'title' => 'Provisionamentos com falha', 'message' => $failed . ' projeto(s) precisam de correção técnica.'];
        }

        if ($offline > 0) {
            $items[] = ['level' => 'warning', 'title' => 'Ambientes offline', 'message' => $offline . ' ambiente(s) exigem health check e validação de DNS/SSL.'];
        }

        if ($pendingCpanel > 0) {
            $items[] = ['level' => 'info', 'title' => 'cPanel pendente', 'message' => $pendingCpanel . ' projeto(s) aguardam configuração de subdomínio/document root.'];
        }

        if ($running > 0) {
            $items[] = ['level' => 'info', 'title' => 'Provisionamentos em execução', 'message' => $running . ' projeto(s) estão em andamento.'];
        }

        if (empty($items)) {
            $items[] = ['level' => 'success', 'title' => 'Operação estável', 'message' => 'Nenhuma falha crítica detectada pelo Factory Brain.'];
        }

        return $items;
    }
}
