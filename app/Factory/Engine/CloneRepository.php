<?php

namespace App\Factory\Engine;

use Symfony\Component\Process\Process;

class CloneRepository
{
    public string $error = '';

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
            "git@github.com:$repository.git",
            $destination,
        ]);

        $process->setTimeout(1800);
        $process->run();

        $this->error = $process->getErrorOutput() ?: $process->getOutput();

        return $process->isSuccessful();
    }
}
