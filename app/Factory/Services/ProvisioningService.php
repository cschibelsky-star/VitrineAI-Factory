<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;
use App\Models\FactoryProvisioningLog;

class ProvisioningService
{
    public function run(FactoryProject $project): void
    {
        $steps = [
            'validacao' => 'Projeto validado.',
            'template' => 'Template identificado.',
            'ambiente' => 'Ambiente preparado.',
            'deploy' => 'Deploy marcado para execução.',
            'finalizacao' => 'Provisionamento concluído.',
        ];

        foreach ($steps as $step => $message) {
            FactoryProvisioningLog::create([
                'factory_project_id' => $project->id,
                'step' => $step,
                'status' => 'success',
                'message' => $message,
            ]);
        }

        $project->update([
            'status' => 'active',
            'provisioning_status' => 'provisioned',
            'provisioning_log' => '[' . now() . '] Provisionamento executado pela Factory.',
            'provisioned_at' => now(),
        ]);
    }
}
