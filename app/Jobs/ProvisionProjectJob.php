<?php

namespace App\Jobs;

use App\Factory\Engine\CloneRepository;
use App\Factory\Engine\ConfigurePermissions;
use App\Factory\Engine\CreateEnvironment;
use App\Factory\Engine\FinalizeInstallation;
use App\Factory\Engine\InstallComposer;
use App\Factory\Engine\PublishAssets;
use App\Factory\Engine\RunMigrations;
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
        $destination = rtrim((string) $this->project->deploy_path, '/');

        $this->log('Validação', 'success', 'Projeto validado.');

        $clone = app(CloneRepository::class);

        $this->runStep('Clone GitHub', function () use ($clone, $destination) {
            $ok = $clone->execute(
                $this->project->github_repository,
                $this->project->branch,
                $destination
            );

            if (! $ok && $clone->error) {
                $this->log('Clone GitHub Detalhe', 'error', $clone->error);
            }

            return $ok;
        });

        $this->runStep('Criar .env', fn () =>
            app(CreateEnvironment::class)->execute($destination, [
                'APP_NAME' => $this->project->name,
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'APP_URL' => 'https://' . $this->project->domain,
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => $destination . '/database/database.sqlite',
                'CACHE_STORE' => 'file',
                'SESSION_DRIVER' => 'file',
                'QUEUE_CONNECTION' => 'sync',
            ])
        );

        $this->runStep('Composer Install', fn () => app(InstallComposer::class)->execute($destination));
        $this->runStep('Permissões', fn () => app(ConfigurePermissions::class)->execute($destination));
        $this->runStep('Finalização', fn () => app(FinalizeInstallation::class)->execute($destination));
        $this->runStep('Migrations', fn () => app(RunMigrations::class)->execute($destination));
        $this->runStep('Assets', fn () => app(PublishAssets::class)->execute($destination));

        $this->project->forceFill([
            'status' => 'active',
            'provisioning_status' => 'completed',
            'provisioning_log' => '[' . now() . '] Provisionamento concluído pela Factory Engine.',
            'provisioned_at' => now(),
        ])->save();

        $this->log('Concluído', 'success', 'Ambiente provisionado com sucesso.');
    }

    private function runStep(string $step, callable $callback): void
    {
        $ok = $callback();

        $this->log($step, $ok ? 'success' : 'error', $ok ? "{$step} concluído." : "{$step} falhou.");

        if (! $ok) {
            $this->project->forceFill(['provisioning_status' => 'failed'])->save();
            throw new \RuntimeException($step . ' falhou.');
        }
    }

    private function log(string $step, string $status, string $message): void
    {
        FactoryProvisioningLog::create([
            'factory_project_id' => $this->project->id,
            'step' => $step,
            'status' => $status,
            'message' => $message,
        ]);
    }
}
