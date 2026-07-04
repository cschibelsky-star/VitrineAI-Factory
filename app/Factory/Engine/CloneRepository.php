<?php

namespace App\Factory\Engine;

use Symfony\Component\Process\Process;

class CloneRepository
{
    public function execute(string $repository, string $branch, string $destination): bool
    {
        if (is_dir($destination)) {
            return true;
        }

        $process = new Process([
            'git',
            'clone',
            '--branch',
            $branch,
            "https://github.com/$repository.git",
            $destination,
        ]);

        $process->setTimeout(1800);
        $process->run();

        return $process->isSuccessful();
    }
}
