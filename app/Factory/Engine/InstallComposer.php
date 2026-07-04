<?php

namespace App\Factory\Engine;

use Symfony\Component\Process\Process;

class InstallComposer
{
    public function execute(string $destination): bool
    {
        @mkdir($destination . '/storage/framework/cache', 0775, true);
        @mkdir($destination . '/storage/framework/sessions', 0775, true);
        @mkdir($destination . '/storage/framework/views', 0775, true);
        @mkdir($destination . '/bootstrap/cache', 0775, true);

        $process = Process::fromShellCommandline('composer install --no-dev --optimize-autoloader', $destination);
        $process->setTimeout(1800);
        $process->run();

        return $process->isSuccessful();
    }
}
