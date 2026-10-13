<?php

namespace App\Services;

use App\Models\FactoryBlueprint;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FactoryMiniBuilder
{
    public function generate(string $projectName, ?FactoryBlueprint $blueprint = null, array $capabilities = []): array
    {
        $slug = Str::slug($projectName);
        $basePath = 'factory-builds/' . $slug;

        $files = [
            $basePath . '/README.md' => $this->readme($projectName, $blueprint, $capabilities),
            $basePath . '/composer.json' => $this->composer($slug),
            $basePath . '/.env.example' => $this->env($projectName),
            $basePath . '/routes/web.php' => "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::get('/', fn () => view('welcome'));\n",
        ];

        foreach ($files as $path => $content) {
            Storage::disk('local')->put($path, $content);
        }

        return [
            'project' => $projectName,
            'slug' => $slug,
            'path' => $basePath,
            'files' => array_keys($files),
        ];
    }

    private function readme(string $projectName, ?FactoryBlueprint $blueprint, array $capabilities): string
    {
        return '# ' . $projectName . "\n\n" .
            'Gerado pela Vitrine IA Factory.' . "\n\n" .
            'Blueprint: ' . ($blueprint?->name ?? 'Nenhum') . "\n\n" .
            'Capabilities: ' . implode(', ', $capabilities) . "\n";
    }

    private function composer(string $slug): string
    {
        return json_encode([
            'name' => 'vitrine-ia/' . $slug,
            'type' => 'project',
            'require' => [
                'php' => '^8.3',
                'laravel/framework' => '^12.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function env(string $projectName): string
    {
        return "APP_NAME=\"{$projectName}\"\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n";
    }
}
