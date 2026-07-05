<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;
use App\Models\FactoryTemplate;

class FactoryBrainService
{
    public function recommendations(FactoryProject $project): array
    {
        $items = [];

        if (($project->provisioning_status ?? null) === 'failed') {
            $items[] = [
                'level' => 'critical',
                'title' => 'Provisionamento com falha',
                'message' => 'Revisar logs, caminho no servidor, repositório, branch, domínio e Document Root antes de reexecutar.',
            ];
        }

        if (($project->health_status ?? null) === 'offline') {
            $items[] = [
                'level' => 'warning',
                'title' => 'Ambiente offline',
                'message' => 'Validar DNS, SSL e Document Root no cPanel. Depois executar Verificar Saúde.',
            ];
        }

        if (($project->provisioning_status ?? null) === 'completed' && ($project->health_status ?? null) !== 'online') {
            $items[] = [
                'level' => 'info',
                'title' => 'Provisionado, mas não online',
                'message' => 'O deploy foi concluído, mas o domínio ainda não confirmou health online. Verificar subdomínio e SSL.',
            ];
        }

        if (empty($project->document_root)) {
            $items[] = [
                'level' => 'info',
                'title' => 'Document Root ausente',
                'message' => 'Definir Document Root para padronizar a entrega em HostGator/cPanel.',
            ];
        }

        if (empty($project->admin_email)) {
            $items[] = [
                'level' => 'info',
                'title' => 'Administrador não definido',
                'message' => 'Cadastrar e-mail do administrador para automatizar onboarding e entrega de acesso.',
            ];
        }

        if (empty($items)) {
            $items[] = [
                'level' => 'success',
                'title' => 'Operação estável',
                'message' => 'Nenhuma recomendação crítica encontrada para este projeto.',
            ];
        }

        return $items;
    }

    public function platformInsights(): array
    {
        $projects = FactoryProject::query();

        return [
            'total_projects' => (clone $projects)->count(),
            'completed' => FactoryProject::where('provisioning_status', 'completed')->count(),
            'failed' => FactoryProject::where('provisioning_status', 'failed')->count(),
            'running' => FactoryProject::where('provisioning_status', 'running')->count(),
            'online' => FactoryProject::where('health_status', 'online')->count(),
            'offline' => FactoryProject::where('health_status', 'offline')->count(),
            'templates' => FactoryTemplate::count(),
            'actions' => $this->globalActions(),
        ];
    }

    public function globalActions(): array
    {
        $actions = [];

        $failed = FactoryProject::where('provisioning_status', 'failed')->count();
        if ($failed > 0) {
            $actions[] = [
                'level' => 'critical',
                'title' => $failed . ' projeto(s) com falha',
                'message' => 'Prioridade operacional: revisar logs e corrigir antes de novos provisionamentos.',
            ];
        }

        $offline = FactoryProject::where('health_status', 'offline')->count();
        if ($offline > 0) {
            $actions[] = [
                'level' => 'warning',
                'title' => $offline . ' ambiente(s) offline',
                'message' => 'Validar DNS, SSL e Document Root nos ambientes offline.',
            ];
        }

        $pendingCpanel = FactoryProject::where('cpanel_status', 'pending')->count();
        if ($pendingCpanel > 0) {
            $actions[] = [
                'level' => 'info',
                'title' => $pendingCpanel . ' configuração(ões) cPanel pendente(s)',
                'message' => 'Usar cPanel Assistido para concluir subdomínio, Document Root e SSL.',
            ];
        }

        if (empty($actions)) {
            $actions[] = [
                'level' => 'success',
                'title' => 'Factory sem alertas críticos',
                'message' => 'Todos os indicadores operacionais estão dentro do esperado.',
            ];
        }

        return $actions;
    }
}
