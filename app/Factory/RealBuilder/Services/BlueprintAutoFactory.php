<?php

declare(strict_types=1);

namespace App\Factory\RealBuilder\Services;

use Illuminate\Support\Facades\File;

class BlueprintAutoFactory
{
    public function create(string $name, string $type = 'saas', ?string $slug = null): array
    {
        $slug = $slug ?: $this->slug($name);
        $blueprint = [
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'framework' => 'Laravel 12',
            'panel' => 'Filament',
            'database' => 'MySQL',
            'generated_by' => 'Vitrine AI Factory Evolução Real 002',
            'modules' => $this->modulesForType($type),
        ];

        $path = storage_path('app/factory/blueprints/' . $slug . '.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($blueprint, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return [
            'slug' => $slug,
            'path' => $path,
            'blueprint' => $blueprint,
        ];
    }

    protected function modulesForType(string $type): array
    {
        return match ($type) {
            'crm' => $this->crmModules(),
            'portal', 'portal_noticias' => $this->portalModules(),
            'tv', 'tv_digital' => $this->tvModules(),
            'guia', 'guia_digital' => $this->guiaModules(),
            'compras', 'compras_ia' => $this->comprasModules(),
            default => $this->saasModules(),
        };
    }

    protected function saasModules(): array
    {
        return [
            $this->module('empresas', 'Empresas', [
                ['name' => 'nome', 'type' => 'string', 'nullable' => false],
                ['name' => 'documento', 'type' => 'string'],
                ['name' => 'email', 'type' => 'string'],
                ['name' => 'telefone', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string', 'nullable' => false],
            ]),
            $this->module('clientes', 'Clientes', [
                ['name' => 'empresa_id', 'type' => 'foreignId', 'related_model' => 'Empresa'],
                ['name' => 'nome', 'type' => 'string', 'nullable' => false],
                ['name' => 'email', 'type' => 'string'],
                ['name' => 'telefone', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string'],
            ]),
            $this->module('projetos', 'Projetos', [
                ['name' => 'empresa_id', 'type' => 'foreignId', 'related_model' => 'Empresa'],
                ['name' => 'nome', 'type' => 'string', 'nullable' => false],
                ['name' => 'tipo', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string'],
                ['name' => 'descricao', 'type' => 'text'],
            ]),
        ];
    }

    protected function crmModules(): array
    {
        return [
            $this->module('leads', 'Leads', [
                ['name' => 'nome', 'type' => 'string', 'nullable' => false],
                ['name' => 'email', 'type' => 'string'],
                ['name' => 'telefone', 'type' => 'string'],
                ['name' => 'origem', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string', 'nullable' => false],
                ['name' => 'valor_estimado', 'type' => 'decimal'],
            ]),
            $this->module('propostas', 'Propostas', [
                ['name' => 'lead_id', 'type' => 'foreignId', 'related_model' => 'Lead'],
                ['name' => 'titulo', 'type' => 'string', 'nullable' => false],
                ['name' => 'valor', 'type' => 'decimal'],
                ['name' => 'status', 'type' => 'string'],
                ['name' => 'validade', 'type' => 'date'],
            ]),
            $this->module('followups', 'Follow-ups', [
                ['name' => 'lead_id', 'type' => 'foreignId', 'related_model' => 'Lead'],
                ['name' => 'data', 'type' => 'date'],
                ['name' => 'canal', 'type' => 'string'],
                ['name' => 'observacao', 'type' => 'text'],
                ['name' => 'status', 'type' => 'string'],
            ]),
        ];
    }

    protected function portalModules(): array
    {
        return [
            $this->module('categorias', 'Categorias', [
                ['name' => 'nome', 'type' => 'string', 'nullable' => false],
                ['name' => 'slug', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string'],
            ]),
            $this->module('noticias', 'Notícias', [
                ['name' => 'categoria_id', 'type' => 'foreignId', 'related_model' => 'Categoria'],
                ['name' => 'titulo', 'type' => 'string', 'nullable' => false],
                ['name' => 'slug', 'type' => 'string'],
                ['name' => 'linha_fina', 'type' => 'string'],
                ['name' => 'conteudo', 'type' => 'text'],
                ['name' => 'status', 'type' => 'string'],
                ['name' => 'publicado_em', 'type' => 'datetime'],
            ]),
            $this->module('banners', 'Banners', [
                ['name' => 'titulo', 'type' => 'string'],
                ['name' => 'imagem', 'type' => 'string'],
                ['name' => 'link', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string'],
            ]),
        ];
    }

    protected function tvModules(): array
    {
        return array_merge($this->portalModules(), [
            $this->module('videos', 'Vídeos', [
                ['name' => 'titulo', 'type' => 'string', 'nullable' => false],
                ['name' => 'url', 'type' => 'string'],
                ['name' => 'categoria', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string'],
                ['name' => 'publicado_em', 'type' => 'datetime'],
            ]),
            $this->module('programas', 'Programas', [
                ['name' => 'nome', 'type' => 'string', 'nullable' => false],
                ['name' => 'apresentador', 'type' => 'string'],
                ['name' => 'descricao', 'type' => 'text'],
                ['name' => 'status', 'type' => 'string'],
            ]),
        ]);
    }

    protected function guiaModules(): array
    {
        return [
            $this->module('cidades', 'Cidades', [
                ['name' => 'nome', 'type' => 'string', 'nullable' => false],
                ['name' => 'uf', 'type' => 'string'],
                ['name' => 'descricao', 'type' => 'text'],
                ['name' => 'status', 'type' => 'string'],
            ]),
            $this->module('atrativos', 'Atrativos', [
                ['name' => 'cidade_id', 'type' => 'foreignId', 'related_model' => 'Cidade'],
                ['name' => 'nome', 'type' => 'string', 'nullable' => false],
                ['name' => 'categoria', 'type' => 'string'],
                ['name' => 'imagem', 'type' => 'string'],
                ['name' => 'descricao', 'type' => 'text'],
                ['name' => 'status', 'type' => 'string'],
            ]),
            $this->module('eventos', 'Eventos', [
                ['name' => 'cidade_id', 'type' => 'foreignId', 'related_model' => 'Cidade'],
                ['name' => 'titulo', 'type' => 'string', 'nullable' => false],
                ['name' => 'data', 'type' => 'date'],
                ['name' => 'local', 'type' => 'string'],
                ['name' => 'descricao', 'type' => 'text'],
                ['name' => 'status', 'type' => 'string'],
            ]),
        ];
    }

    protected function comprasModules(): array
    {
        return [
            $this->module('processos', 'Processos', [
                ['name' => 'numero', 'type' => 'string', 'nullable' => false],
                ['name' => 'objeto', 'type' => 'text', 'nullable' => false],
                ['name' => 'modalidade', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string'],
            ]),
            $this->module('documentos', 'Documentos', [
                ['name' => 'processo_id', 'type' => 'foreignId', 'related_model' => 'Processo'],
                ['name' => 'tipo', 'type' => 'string'],
                ['name' => 'titulo', 'type' => 'string'],
                ['name' => 'conteudo', 'type' => 'text'],
                ['name' => 'status', 'type' => 'string'],
            ]),
            $this->module('fornecedores', 'Fornecedores', [
                ['name' => 'nome', 'type' => 'string', 'nullable' => false],
                ['name' => 'documento', 'type' => 'string'],
                ['name' => 'email', 'type' => 'string'],
                ['name' => 'telefone', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string'],
            ]),
        ];
    }

    protected function module(string $slug, string $label, array $fields): array
    {
        return ['slug' => $slug, 'label' => $label, 'fields' => $fields];
    }

    protected function slug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $value) ?: $value);
        return trim($value, '-') ?: 'projeto';
    }
}