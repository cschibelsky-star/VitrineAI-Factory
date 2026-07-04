<?php

namespace App\Factory\Engine;

use Symfony\Component\Process\Process;

class RunMigrations
{
    public function execute(string $destination): bool
    {
        $process = Process::fromShellCommandline('php artisan migrate --force', $destination);
        $process->setTimeout(1800);
        $process->run();

        return $process->isSuccessful();
    }
}
