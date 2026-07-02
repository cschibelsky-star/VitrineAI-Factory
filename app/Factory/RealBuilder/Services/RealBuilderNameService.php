<?php

declare(strict_types=1);

namespace App\Factory\RealBuilder\Services;

class RealBuilderNameService
{
    protected array $singularMap = [
        'clientes' => 'Cliente',
        'animais' => 'Animal',
        'agendamentos' => 'Agendamento',
        'prontuarios' => 'Prontuario',
        'vacinas' => 'Vacina',
        'financeiro' => 'Financeiro',
        'diagnosticos' => 'Diagnostico',
        'documentos' => 'Documento',
        'planos' => 'Plano',
        'relatorios' => 'Relatorio',
        'categorias' => 'Categoria',
        'bens' => 'Bem',
        'locais' => 'Local',
        'movimentacoes' => 'Movimentacao',
        'registros' => 'Registro',
        'empresas' => 'Empresa',
        'projetos' => 'Projeto',
        'leads' => 'Lead',
        'propostas' => 'Proposta',
        'followups' => 'Followup',
        'noticias' => 'Noticia',
        'banners' => 'Banner',
        'videos' => 'Video',
        'programas' => 'Programa',
        'cidades' => 'Cidade',
        'atrativos' => 'Atrativo',
        'eventos' => 'Evento',
        'processos' => 'Processo',
        'fornecedores' => 'Fornecedor',
    ];

    public function modelName(string $slug): string
    {
        return $this->singularMap[$slug] ?? $this->studly($this->singular($slug));
    }

    public function resourceName(string $slug): string
    {
        return $this->modelName($slug) . 'Resource';
    }

    public function pageListName(string $slug): string
    {
        return 'List' . $this->studly($slug);
    }

    public function migrationName(string $slug): string
    {
        return 'create_' . $slug . '_table';
    }

    public function relationName(string $field): string
    {
        return $this->camel(str_replace('_id', '', $field));
    }

    public function relatedTable(string $field): string
    {
        $base = str_replace('_id', '', $field);
        return $this->plural($base);
    }

    public function relatedModelFromField(string $field): string
    {
        return $this->modelName($this->plural(str_replace('_id', '', $field)));
    }

    public function studly(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = preg_replace('/[^A-Za-z0-9 ]+/', '', $value) ?: $value;
        return str_replace(' ', '', ucwords(strtolower($value)));
    }

    public function camel(string $value): string
    {
        return lcfirst($this->studly($value));
    }

    public function headline(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = preg_replace('/[^A-Za-z0-9À-ÿ ]+/', '', $value) ?: $value;
        return ucwords(strtolower(trim($value)));
    }

    public function slug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $value) ?: $value);
        return trim($value, '-') ?: 'projeto';
    }

    protected function singular(string $value): string
    {
        if (str_ends_with($value, 'oes')) return substr($value, 0, -3) . 'ao';
        if (str_ends_with($value, 'ais')) return substr($value, 0, -3) . 'al';
        if (str_ends_with($value, 'is')) return substr($value, 0, -2) . 'il';
        if (str_ends_with($value, 's')) return substr($value, 0, -1);
        return $value;
    }

    protected function plural(string $value): string
    {
        if (str_ends_with($value, 's')) return $value;
        if (str_ends_with($value, 'ao')) return substr($value, 0, -2) . 'oes';
        return $value . 's';
    }
}
