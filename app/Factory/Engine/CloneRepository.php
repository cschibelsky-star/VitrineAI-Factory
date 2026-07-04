<?php

namespace App\Factory\Engine;

use Symfony\Component\Process\Process;

class CloneRepository
{
    public string $error = '';

    public function execute(string $repository, string $branch, string $destination): bool
    {
        $destination = rtrim($destination, '/');

        if (empty($repository) || empty($branch) || empty($destination)) {
            $this->error = 'Repositório, branch ou destino não informado.';
            return false;
        }

        if (is_dir($destination . '/.git')) {
            return true;
        }

        if (is_dir($destination) && count(scandir($destination)) > 2) {
            $this->error = "Destino já existe e não está vazio: {$destination}";
            return false;
        }

        if (! is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
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
