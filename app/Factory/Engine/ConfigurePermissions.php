<?php

namespace App\Factory\Engine;

class ConfigurePermissions
{
    public function execute(string $destination): bool
    {
        @chmod($destination . '/storage', 0775);
        @chmod($destination . '/bootstrap/cache', 0775);

        return true;
    }
}
