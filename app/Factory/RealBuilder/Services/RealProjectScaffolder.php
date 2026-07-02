<?php

declare(strict_types=1);

namespace App\Factory\RealBuilder\Services;

use Illuminate\Support\Facades\File;

class RealProjectScaffolder
{
    public function scaffold(string $base, array $blueprint): array
    {
        File::ensureDirectoryExists($base);

        $name = $blueprint['name'] ?? $this->headline($blueprint['slug'] ?? 'factory-project');
        $slug = $blueprint['slug'] ?? $this->slug($name);
        $namespace = $this->namespaceFromName($name);

        $files = [];
        $files[] = $this->write($base . '/README.md', $this->readme($name, $slug));
        $files[] = $this->write($base . '/README_HOSTGATOR.md', $this->hostgatorReadme($name));
        $files[] = $this->write($base . '/CHANGELOG.md', "# Changelog\n\n## 0.2.0\n- Projeto gerado pela Factory Evolução Real 002.\n");
        $files[] = $this->write($base . '/.env.example', $this->envExample($name));
        $files[] = $this->write($base . '/.gitignore', $this->gitignore());
        $files[] = $this->write($base . '/composer.json', $this->composer($name, $namespace));
        $files[] = $this->write($base . '/artisan', $this->artisan());
        $files[] = $this->write($base . '/bootstrap/app.php', $this->bootstrapApp());
        $files[] = $this->write($base . '/routes/web.php', $this->webRoutes($name));
        $files[] = $this->write($base . '/routes/console.php', "<?php\n\nuse Illuminate\\Support\\Facades\\Artisan;\n\nArtisan::command('app:status', function () {\n    \$this->info('Aplicação gerada pela Vitrine AI Factory.');\n});\n");
        $files[] = $this->write($base . '/config/app.php', $this->configApp($name));
        $files[] = $this->write($base . '/database/seeders/DatabaseSeeder.php', $this->databaseSeeder($blueprint));
        $files[] = $this->write($base . '/app/Providers/AppServiceProvider.php', $this->appServiceProvider());
        $files[] = $this->write($base . '/resources/views/welcome.blade.php', $this->welcomeView($name));
        $files[] = $this->write($base . '/public/index.php', $this->publicIndex());

        foreach (['app/Models','app/Policies','app/Filament/Resources','database/migrations','database/seeders','database/factories','routes','resources/views','storage/app','storage/framework/cache','storage/framework/sessions','storage/framework/views','storage/logs','bootstrap/cache','public'] as $dir) {
            File::ensureDirectoryExists($base . '/' . $dir);
        }

        File::put($base . '/storage/app/.gitignore', "*\n!.gitignore\n");
        File::put($base . '/storage/logs/.gitignore', "*\n!.gitignore\n");
        File::put($base . '/bootstrap/cache/.gitignore', "*\n!.gitignore\n");

        return $files;
    }

    protected function write(string $path, string $content): string
    {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        return $path;
    }

    protected function namespaceFromName(string $name): string
    {
        return str_replace(' ', '', ucwords(strtolower((string) preg_replace('/[^A-Za-z0-9]+/', ' ', $name))));
    }

    protected function readme(string $name, string $slug): string
    {
        return <<<MD
# {$name}

Projeto Laravel + Filament gerado automaticamente pela **Vitrine AI Factory — Evolução Real 002**.

## Conteúdo gerado

- Estrutura base Laravel
- Models
- Migrations
- Policies
- Resources Filament
- Pages Filament
- Seeders
- README HostGator
- Relatório REAL_BUILD_REPORT.json

## Instalação rápida

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan optimize:clear
```

Slug do projeto: `{$slug}`.
MD;
    }

    protected function hostgatorReadme(string $name): string
    {
        return <<<MD
# Instalação HostGator — {$name}

1. Envie o ZIP gerado para a pasta do projeto no cPanel.
2. Extraia os arquivos.
3. Configure o `.env` com os dados do banco MySQL.
4. Pelo terminal SSH, execute:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

5. Aponte o domínio ou subdomínio para a pasta `public`.

Observação: se o Composer não estiver disponível no servidor, rode `composer install` localmente e envie também a pasta `vendor`.
MD;
    }

    protected function envExample(string $name): string
    {
        return <<<ENV
APP_NAME="{$name}"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://seudominio.com.br

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
ENV;
    }

    protected function gitignore(): string
    {
        return <<<TXT
/vendor
/node_modules
.env
.env.backup
.phpunit.result.cache
/storage/*.key
/storage/app/*
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
/storage/logs/*
/bootstrap/cache/*
TXT;
    }

    protected function composer(string $name, string $namespace): string
    {
        $package = $this->slug($name);
        return json_encode([
            'name' => 'vitrine-ai/' . $package,
            'type' => 'project',
            'description' => 'Aplicação gerada pela Vitrine AI Factory.',
            'require' => [
                'php' => '^8.2',
                'laravel/framework' => '^12.0',
                'filament/filament' => '^3.2|^4.0',
            ],
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/',
                    'Database\\Factories\\' => 'database/factories/',
                    'Database\\Seeders\\' => 'database/seeders/',
                ],
            ],
            'scripts' => [
                'post-autoload-dump' => [
                    'Illuminate\\Foundation\\ComposerScripts::postAutoloadDump',
                    '@php artisan package:discover --ansi',
                ],
            ],
            'config' => [
                'optimize-autoloader' => true,
                'preferred-install' => 'dist',
                'sort-packages' => true,
            ],
            'minimum-stability' => 'stable',
            'prefer-stable' => true,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function artisan(): string
    {
        return <<<'PHP'
#!/usr/bin/env php
<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$status = $app->handleCommand(new Symfony\Component\Console\Input\ArgvInput);

exit($status);
PHP;
    }

    protected function bootstrapApp(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
PHP;
    }

    protected function webRoutes(string $name): string
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', ['appName' => '{$name}']);
});
PHP;
    }

    protected function configApp(string $name): string
    {
        return <<<PHP
<?php

return [
    'name' => env('APP_NAME', '{$name}'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'America/Sao_Paulo',
    'locale' => 'pt_BR',
    'fallback_locale' => 'pt_BR',
    'faker_locale' => 'pt_BR',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
];
PHP;
    }

    protected function databaseSeeder(array $blueprint): string
    {
        $calls = [];
        foreach (($blueprint['modules'] ?? []) as $module) {
            $model = $this->studly($this->singular($module['slug'] ?? 'Registro'));
            $calls[] = '        $' . "this->call({$model}Seeder::class);";
        }
        $callCode = implode("\n", array_unique($calls));

        return <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
{$callCode}
    }
}
PHP;
    }

    protected function appServiceProvider(): string
    {
        return <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
PHP;
    }

    protected function welcomeView(string $name): string
    {
        return <<<BLADE
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$name}</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;background:#0f172a;color:#e5e7eb;margin:0;display:flex;min-height:100vh;align-items:center;justify-content:center}
        .card{max-width:760px;padding:40px;border-radius:24px;background:#111827;border:1px solid #334155;box-shadow:0 20px 60px rgba(0,0,0,.35)}
        h1{font-size:38px;margin:0 0 12px}.badge{display:inline-block;padding:8px 12px;border-radius:999px;background:#1f2937;color:#93c5fd;margin-bottom:18px}
        p{line-height:1.7;color:#cbd5e1}.ok{color:#86efac;font-weight:700}
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">Vitrine AI Factory</span>
        <h1>{{ \$appName ?? '{$name}' }}</h1>
        <p class="ok">Projeto gerado com sucesso.</p>
        <p>Esta aplicação foi criada automaticamente pela Factory. Próximos passos: configurar banco de dados, rodar migrations e acessar o painel Filament.</p>
    </main>
</body>
</html>
BLADE;
    }

    protected function publicIndex(): string
    {
        return <<<'PHP'
<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Illuminate\Http\Request::capture());
PHP;
    }

    protected function slug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $value) ?: $value);
        return trim($value, '-') ?: 'projeto';
    }

    protected function headline(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = preg_replace('/[^A-Za-z0-9À-ÿ ]+/', '', $value) ?: $value;
        return ucwords(strtolower(trim($value)));
    }

    protected function studly(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = preg_replace('/[^A-Za-z0-9 ]+/', '', $value) ?: $value;
        return str_replace(' ', '', ucwords(strtolower($value)));
    }

    protected function singular(string $value): string
    {
        if (str_ends_with($value, 'oes')) return substr($value, 0, -3) . 'ao';
        if (str_ends_with($value, 's')) return substr($value, 0, -1);
        return $value;
    }

}
