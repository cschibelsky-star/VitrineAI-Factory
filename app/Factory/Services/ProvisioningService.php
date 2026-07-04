<?php

namespace App\Factory\Services;

use App\Jobs\ProvisionProjectJob;
use App\Models\FactoryProject;

class ProvisioningService
{
    public function run(FactoryProject $project): void
    {
        $project->update([
            'provisioning_status' => 'running',
            'provisioning_log' => '[' . now() . '] Provisionamento iniciado.',
        ]);

        ProvisionProjectJob::dispatch($project);
    }
}
