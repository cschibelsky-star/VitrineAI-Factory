<?php

declare(strict_types=1);

namespace App\Factory\Console\Commands;

use App\Factory\RealBuilder\Services\BlueprintAutoFactory;
use App\Factory\RealBuilder\Services\RealBuildExporter;
use App\Factory\RealBuilder\Services\RealCodeGenerator;
use Illuminate\Console\Command;
use Throwable;

class FactoryMakeRealProjectCommand extends Command
{
    protected $signature = 'factory:make-real-project
        {name : Nome do projeto}
        {--type=saas : Tipo: saas, crm, portal, tv_digital, guia_digital, compras_ia}
        {--slug= : Slug opcional}
        {--zip : Exportar ZIP ao final}';

    protected $description = 'Cria blueprint automático, gera aplicação Laravel/Filament real e opcionalmente exporta ZIP.';

    public function handle(
        BlueprintAutoFactory $blueprints,
        RealCodeGenerator $generator,
        RealBuildExporter $exporter,
    ): int {
        try {
            $name = (string) $this->argument('name');
            $type = (string) $this->option('type');
            $slug = $this->option('slug') ? (string) $this->option('slug') : $this->slug($name);

            $blueprint = $blueprints->create($name, $type, $slug);
            $report = $generator->generate($blueprint['slug']);

            $this->info('Projeto real gerado pela Factory.');
            $this->line('Nome: ' . $name);
            $this->line('Tipo: ' . $type);
            $this->line('Slug: ' . $blueprint['slug']);
            $this->line('Blueprint: ' . $blueprint['path']);
            $this->line('Arquivos: ' . $report['files_count']);
            $this->line('Build: ' . $report['build_path']);

            if ((bool) $this->option('zip')) {
                $export = $exporter->export($blueprint['slug']);
                $this->info('ZIP exportado.');
                $this->line('ZIP: ' . $export['zip']);
                $this->line('Tamanho: ' . $export['size_bytes'] . ' bytes');
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
    }

    protected function slug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $value) ?: $value);
        return trim($value, '-') ?: 'projeto';
    }
}