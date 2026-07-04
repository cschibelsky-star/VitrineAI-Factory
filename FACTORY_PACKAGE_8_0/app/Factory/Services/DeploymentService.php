<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;
use App\Models\FactoryProvisioningLog;
use Symfony\Component\Process\Process;

class DeploymentService
{
    public function update(FactoryProject $project): bool
    {
        $path = rtrim((string) $project->deploy_path, '/');

        if (! is_dir($path . '/.git')) {
            $this->log($project, 'Deploy', 'error', 'Repositório Git não encontrado no caminho de deploy.');
            return false;
        }

        $commands = [
            'git pull',
            'composer install --no-dev --optimize-autoloader',
            'php artisan migrate --force',
            'php artisan optimize:clear',
        ];

        foreach ($commands as $command) {
            $process = Process::fromShellCommandline($command, $path);
            $process->setTimeout(1800);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->log($project, 'Deploy', 'error', $command . ': ' . ($process->getErrorOutput() ?: $process->getOutput()));
                $project->forceFill(['deployment_status' => 'failed'])->save();
                return false;
            }

            $this->log($project, 'Deploy', 'success', $command . ' concluído.');
        }

        $project->forceFill([
            'deployment_status' => 'completed',
            'last_deploy_at' => now(),
        ])->save();

        return true;
    }

    private function log(FactoryProject $project, string $step, string $status, string $message): void
    {
        FactoryProvisioningLog::create([
            'factory_project_id' => $project->id,
            'step' => $step,
            'status' => $status,
            'message' => $message,
        ]);
    }
}
