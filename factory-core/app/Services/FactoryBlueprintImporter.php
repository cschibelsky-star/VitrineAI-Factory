<?php

namespace App\Services;

use App\Models\FactoryBlueprint;
use App\Models\FactoryBlueprintColumn;
use App\Models\FactoryBlueprintEntity;
use App\Models\FactoryBlueprintRelation;
use App\Models\FactoryBlueprintVersion;
use Illuminate\Support\Arr;

class FactoryBlueprintImporter
{
    public function __construct(private readonly FactoryBlueprintParser $parser) {}

    public function import(FactoryBlueprint $blueprint, array $schema, string $version = '0.1.0'): FactoryBlueprintVersion
    {
        $parsed = $this->parser->parse($schema);

        $versionModel = FactoryBlueprintVersion::updateOrCreate(
            ['blueprint_id' => $blueprint->id, 'version' => $version],
            ['status' => 'draft', 'schema' => $schema, 'notes' => 'Imported by Vitrine IA Factory']
        );

        foreach ($parsed['entities'] as $entityData) {
            $entity = FactoryBlueprintEntity::updateOrCreate(
                ['blueprint_id' => $blueprint->id, 'slug' => $entityData['slug']],
                Arr::except($entityData, ['fields']) + ['blueprint_id' => $blueprint->id]
            );

            foreach ($entityData['fields'] as $fieldData) {
                FactoryBlueprintColumn::updateOrCreate(
                    ['blueprint_entity_id' => $entity->id, 'slug' => $fieldData['slug']],
                    $fieldData + ['blueprint_entity_id' => $entity->id]
                );
            }
        }

        foreach ($parsed['relations'] as $relation) {
            FactoryBlueprintRelation::create([
                'blueprint_id' => $blueprint->id,
                'name' => (string) Arr::get($relation, 'name', 'relation'),
                'type' => (string) Arr::get($relation, 'type', 'belongsTo'),
                'foreign_key' => Arr::get($relation, 'foreign_key'),
                'metadata' => $relation,
            ]);
        }

        return $versionModel;
    }
}
