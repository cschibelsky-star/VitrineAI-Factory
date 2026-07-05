<?php

namespace App\Factory\Services;

use App\Models\FactoryProject;
use App\Models\FactoryProvisioningLog;
use Symfony\Component\Process\Process;

class BackupService
{
    public function create(FactoryProject $project): bool
    {
        $path = rtrim((string) $project->deploy_path, '/');

        if (! is_dir($path)) {
            $this->log($project, 'Backup', 'error', 'Diretório do projeto não encontrado.');
            return false;
        }

        $backupDir = storage_path('app/factory-backups');
        @mkdir($backupDir, 0775, true);

        $filename = $backupDir . '/' . str_replace(['/', '.', ' '], '-', $project->name) . '-' . date('Ymd-His') . '.tar.gz';

        $process = Process::fromShellCommandline(
            'tar -czf ' . escapeshellarg($filename) . ' --exclude=vendor --exclude=node_modules --exclude=storage/logs ' . escapeshellarg($path)
        );

        $process->setTimeout(1800);
        $process->run();

        $ok = $process->isSuccessful();

        $this->log(
            $project,
            'Backup',
            $ok ? 'success' : 'error',
            $ok ? 'Backup criado: ' . $filename : ($process->getErrorOutput() ?: $process->getOutput())
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
