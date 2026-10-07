<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FactoryBlueprintParser
{
    public function parse(array $schema): array
    {
        $entities = [];

        foreach (Arr::get($schema, 'entities', []) as $index => $entity) {
            $name = (string) Arr::get($entity, 'name', 'Entity');
            $fields = [];

            foreach (Arr::get($entity, 'fields', []) as $fieldIndex => $field) {
                $fieldName = (string) Arr::get($field, 'name', 'field_' . $fieldIndex);
                $fields[] = [
                    'name' => $fieldName,
                    'slug' => Str::slug((string) Arr::get($field, 'slug', $fieldName)),
                    'type' => Arr::get($field, 'type', 'string'),
                    'nullable' => (bool) Arr::get($field, 'nullable', true),
                    'required' => (bool) Arr::get($field, 'required', false),
                    'searchable' => (bool) Arr::get($field, 'searchable', false),
                    'sortable' => (bool) Arr::get($field, 'sortable', false),
                    'order_column' => (int) Arr::get($field, 'order_column', $fieldIndex),
                    'rules' => Arr::get($field, 'rules', []),
                    'metadata' => Arr::get($field, 'metadata', []),
                ];
            }

            $entities[] = [
                'name' => $name,
                'slug' => Str::slug((string) Arr::get($entity, 'slug', $name)),
                'table_name' => Arr::get($entity, 'table_name', Str::snake(Str::plural($name))),
                'model_name' => Arr::get($entity, 'model_name', Str::studly($name)),
                'description' => Arr::get($entity, 'description'),
                'fields' => $fields,
                'metadata' => Arr::get($entity, 'metadata', []),
            ];
        }

        return [
            'entities' => $entities,
            'relations' => Arr::get($schema, 'relations', []),
            'capabilities' => Arr::get($schema, 'capabilities', []),
        ];
    }
}
