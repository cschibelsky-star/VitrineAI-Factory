<?php

namespace App\Factory\Engine;

use Symfony\Component\Process\Process;

class PublishAssets
{
    public function execute(string $destination): bool
    {
        $process = Process::fromShellCommandline('php artisan optimize:clear && php artisan filament:assets || true', $destination);
        $process->setTimeout(1800);
        $process->run();

        return true;
    }
}
