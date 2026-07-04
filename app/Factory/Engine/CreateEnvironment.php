<?php

namespace App\Factory\Engine;

class CreateEnvironment
{
    public function execute(string $destination, array $env): bool
    {
        $content = '';

        foreach ($env as $key => $value) {
            $content .= $key . '=' . '"' . str_replace('"', '\"', (string) $value) . '"' . PHP_EOL;
        }

        file_put_contents($destination . '/.env', $content);

        return file_exists($destination . '/.env');
    }
}
