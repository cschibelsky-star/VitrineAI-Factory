<?php

namespace Tests\Feature;

use App\Models\Engine;
use App\Models\EngineType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngineCoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_engine_belongs_to_engine_type(): void
    {
        $type = EngineType::factory()->create([
            'name' => 'Core',
            'slug' => 'core-test',
        ]);

        $engine = Engine::factory()->create([
            'engine_type_id' => $type->id,
            'name' => 'Blueprint Engine',
            'slug' => 'blueprint-engine-test',
            'code' => 'ENG-BLUEPRINT-TEST',
            'status' => Engine::STATUS_ACTIVE,
        ]);

        $this->assertEquals($type->id, $engine->engineType->id);
        $this->assertEquals(Engine::STATUS_ACTIVE, $engine->status);
    }
}
