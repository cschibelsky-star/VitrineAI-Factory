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
            $this->log($project, 'Git Pull', 'error', 'Projeto não possui repositório Git válido.');
            return false;
        }

        return $this->run($project, 'Git Pull', 'git pull', $path)
            && $this->run($project, 'Composer', 'composer install --no-dev --optimize-autoloader', $path)
            && $this->run($project, 'Migrations', 'php artisan migrate --force', $path)
            && $this->run($project, 'Assets', 'php artisan optimize:clear && php artisan filament:assets || true', $path);
    }

    private function run(FactoryProject $project, string $step, string $command, string $path): bool
    {
        $process = Process::fromShellCommandline($command, $path);
        $process->setTimeout(1800);
        $process->run();

        $ok = $process->isSuccessful();

        $this->log(
            $project,
            $step,
            $ok ? 'success' : 'error',
            $ok ? $step . ' concluído.' : ($process->getErrorOutput() ?: $process->getOutput())
        );

        return $ok;
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
