<?php

namespace App\Factory\Services;

use App\Jobs\ProvisionProjectJob;
use App\Models\FactoryProject;

class ProvisioningService
{
    public function run(FactoryProject $project): void
    {
        $project->forceFill([
            'provisioning_status' => 'running',
            'provisioning_log' => '[' . now() . '] Provisionamento iniciado pela Factory.',
        ])->save();

        ProvisionProjectJob::dispatch($project);
    }
}
