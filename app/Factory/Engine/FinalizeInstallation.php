<?php

namespace App\Factory\Engine;

use Symfony\Component\Process\Process;

class FinalizeInstallation
{
    public function execute(string $destination): bool
    {
        $process = Process::fromShellCommandline('php artisan key:generate --force && php artisan optimize:clear', $destination);
        $process->setTimeout(1800);
        $process->run();

        return $process->isSuccessful();
    }
}
