<?php

namespace Database\Seeders;

use App\Models\FactoryBlueprint;
use App\Services\FactoryBlueprintService;
use Illuminate\Database\Seeder;

class BlueprintEngineSeeder extends Seeder
{
    public function run(): void
    {
        $blueprint = FactoryBlueprint::updateOrCreate(
            ['slug' => 'portal-cms'],
            [
                'name' => 'Portal CMS',
                'category' => 'portal',
                'status' => 'foundation',
                'version' => '0.1.0',
                'description' => 'Blueprint base para portais da Vitrine IA Factory.',
                'blueprint_dna' => ['foundation' => true],
            ]
        );

        $schema = [
            'entities' => [
                [
                    'name' => 'Post',
                    'table_name' => 'posts',
                    'model_name' => 'Post',
                    'fields' => [
                        ['name' => 'title', 'type' => 'string', 'required' => true, 'searchable' => true],
                        ['name' => 'slug', 'type' => 'string', 'required' => true, 'searchable' => true],
                        ['name' => 'content', 'type' => 'text', 'required' => true],
                        ['name' => 'published_at', 'type' => 'datetime', 'nullable' => true, 'sortable' => true],
                    ],
                ],
                [
                    'name' => 'Category',
                    'table_name' => 'categories',
                    'model_name' => 'Category',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true, 'searchable' => true],
                        ['name' => 'slug', 'type' => 'string', 'required' => true],
                    ],
                ],
            ],
            'capabilities' => ['CMS', 'Notícias', 'Categorias'],
        ];

        app(FactoryBlueprintService::class)->importSchema($blueprint, $schema, '0.1.0');
    }
}
