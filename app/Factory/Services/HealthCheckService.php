<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;

class HealthCheckService
{
    public function check(FactoryProject $project): string
    {
        if (empty($project->domain)) {
            return 'unknown';
        }

        $url = 'https://' . $project->domain;

        $headers = @get_headers($url);

        $status = ($headers && str_contains($headers[0] ?? '', '200')) ? 'online' : 'offline';

        $project->forceFill([
            'health_status' => $status,
            'last_health_check_at' => now(),
        ])->save();

        return $status;
    }
}
