<?php

namespace App\Services;

use App\Models\FactoryCapability;
use App\Models\FactoryCapabilityRelation;
use App\Models\FactoryCapabilityVersion;
use App\Models\FactoryBlueprint;
use App\Models\FactoryProduct;
use Illuminate\Support\Str;

class FactoryCapabilityService
{
    public function create(array $payload): FactoryCapability
    {
        $payload['slug'] = Str::slug($payload['slug'] ?? $payload['name']);
        $payload['status'] = $payload['status'] ?? 'active';
        $payload['version'] = $payload['version'] ?? '0.1.0';

        return FactoryCapability::create($payload);
    }

    public function publishVersion(FactoryCapability $capability, string $version = '0.1.0', array $schema = []): FactoryCapabilityVersion
    {
        $record = FactoryCapabilityVersion::updateOrCreate(
            ['capability_id' => $capability->id, 'version' => $version],
            ['status' => 'published', 'schema' => $schema, 'published_at' => now()]
        );

        $capability->update(['version' => $version, 'status' => 'active']);

        return $record;
    }

    public function addDependency(FactoryCapability $capability, FactoryCapability $dependency): FactoryCapabilityRelation
    {
        return FactoryCapabilityRelation::updateOrCreate(
            [
                'capability_id' => $capability->id,
                'related_capability_id' => $dependency->id,
                'link_type' => 'dependency',
            ],
            ['status' => 'active']
        );
    }

    public function attachBlueprint(FactoryCapability $capability, FactoryBlueprint $blueprint): FactoryCapabilityRelation
    {
        return FactoryCapabilityRelation::updateOrCreate(
            [
                'capability_id' => $capability->id,
                'blueprint_id' => $blueprint->id,
                'link_type' => 'blueprint',
            ],
            ['status' => 'active']
        );
    }

    public function attachProduct(FactoryCapability $capability, FactoryProduct $product): FactoryCapabilityRelation
    {
        return FactoryCapabilityRelation::updateOrCreate(
            [
                'capability_id' => $capability->id,
                'product_id' => $product->id,
                'link_type' => 'product',
            ],
            ['status' => 'active']
        );
    }
}
