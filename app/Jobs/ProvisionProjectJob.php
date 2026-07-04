<?php

namespace App\Jobs;

use App\Models\FactoryProject;
use App\Models\FactoryProvisioningLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProvisionProjectJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public FactoryProject $project) {}

    public function handle(): void
    {
        $steps = [
            'Validando projeto',
            'Validando template',
            'Preparando ambiente',
            'Preparando repositório',
            'Preparando deploy',
            'Preparando configuração',
            'Provisionamento finalizado',
        ];

        foreach ($steps as $step) {
            FactoryProvisioningLog::create([
                'factory_project_id' => $this->project->id,
                'step' => $step,
                'status' => 'success',
                'message' => $step,
            ]);
        }

        $this->project->update([
            'status' => 'active',
            'provisioning_status' => 'completed',
            'provisioning_log' => '[' . now() . '] Provisionamento executado via Job.',
            'provisioned_at' => now(),
        ]);
    }
}
