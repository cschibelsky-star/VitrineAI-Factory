<?php

namespace App\Factory\Engine;

use Symfony\Component\Process\Process;

class InstallComposer
{
    public function execute(string $destination): bool
    {
        $process = Process::fromShellCommandline('composer install --no-dev --optimize-autoloader', $destination);
        $process->setTimeout(1800);
        $process->run();

        return $process->isSuccessful();
    }
}
