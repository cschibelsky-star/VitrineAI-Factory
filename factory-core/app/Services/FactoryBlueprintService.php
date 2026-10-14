<?php

namespace App\Services;

use App\Models\FactoryBlueprint;
use App\Models\FactoryBlueprintVersion;
use Illuminate\Support\Str;

class FactoryBlueprintService
{
    public function __construct(private readonly FactoryBlueprintImporter $importer) {}

    public function importSchema(FactoryBlueprint $blueprint, array $schema, string $version = '0.1.0'): FactoryBlueprintVersion
    {
        return $this->importer->import($blueprint, $schema, $version);
    }

    public function publishVersion(FactoryBlueprintVersion $version): FactoryBlueprintVersion
    {
        $version->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $version->refresh();
    }

    public function normalizePayload(array $payload): array
    {
        if (isset($payload['name']) || isset($payload['slug'])) {
            $payload['slug'] = Str::slug($payload['slug'] ?? $payload['name']);
        }

        return $payload;
    }
}
