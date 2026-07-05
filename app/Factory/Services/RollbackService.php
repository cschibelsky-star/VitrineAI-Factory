<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;
use App\Models\FactoryProvisioningLog;
use Symfony\Component\Process\Process;

class RollbackService
{
    public function rollbackGit(FactoryProject $project): bool
    {
        $path = rtrim((string) $project->deploy_path, '/');

        if (! is_dir($path . '/.git')) {
            $this->log($project, 'Rollback', 'error', 'Projeto não possui Git válido.');
            return false;
        }

        $check = Process::fromShellCommandline('git rev-parse --verify HEAD~1', $path);
        $check->run();

        if (! $check->isSuccessful()) {
            $this->log($project, 'Rollback', 'error', 'Rollback indisponível: não há commit anterior.');
            return false;
        }

        $process = Process::fromShellCommandline('git reset --hard HEAD~1 && php artisan optimize:clear', $path);
        $process->setTimeout(1800);
        $process->run();

        $ok = $process->isSuccessful();

        $this->log(
            $project,
            'Rollback',
            $ok ? 'success' : 'error',
            $ok ? 'Rollback Git executado.' : ($process->getErrorOutput() ?: $process->getOutput())
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
