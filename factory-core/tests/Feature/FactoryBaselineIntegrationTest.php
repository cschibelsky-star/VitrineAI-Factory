<?php

namespace Tests\Feature;

use App\Filament\Pages\FactoryBuilderPage;
use App\Filament\Pages\FactoryDashboard;
use App\Models\Engine;
use App\Models\EngineType;
use App\Models\FactoryAgent;
use App\Models\FactoryBlueprint;
use App\Models\FactoryBlueprintColumn;
use App\Models\FactoryBlueprintEntity;
use App\Models\FactoryBlueprintVersion;
use App\Models\FactoryCapability;
use App\Models\FactoryCapabilityRelation;
use App\Models\FactoryCapabilityVersion;
use App\Models\FactoryMission;
use App\Models\FactoryProduct;
use App\Models\User;
use App\Services\EngineService;
use App\Services\FactoryAIAnalysisEngine;
use App\Services\FactoryBlueprintService;
use App\Services\FactoryCapabilityService;
use App\Services\FactoryDeploymentEngine;
use App\Services\FactoryGitHubEngine;
use App\Services\FactoryMiniBuilder;
use App\Services\FactoryMissionRunner;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FactoryBaselineIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ci_database_is_ephemeral_and_enforces_foreign_keys(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->assertDatabaseCount('engines', 0);
    }

    public function test_engine_service_resolves_and_persists_state_transitions(): void
    {
        $type = EngineType::factory()->create();
        $service = app(EngineService::class);
        $engine = $service->create([
            'engine_type_id' => $type->id,
            'name' => 'CI Engine',
            'code' => 'ci-engine',
        ]);

        $this->assertSame('CI-ENGINE', $engine->code);
        $this->assertSame('ci-engine', $engine->slug);
        $this->assertSame($type->id, $engine->engineType->id);
        $this->assertSame(Engine::STATUS_ACTIVE, $service->activate($engine)->status);
        $this->assertFalse($service->pause($engine)->is_active);
        $this->assertSame($engine->id, $service->paginate()->first()->id);
    }

    public function test_blueprint_capability_product_and_builder_integration(): void
    {
        Storage::fake('local');
        $product = FactoryProduct::create(['name' => 'CI Product', 'slug' => 'ci-product']);
        $blueprint = FactoryBlueprint::create([
            'name' => 'CI Blueprint', 'slug' => 'ci-blueprint', 'source_product_id' => $product->id,
        ]);
        $schema = ['entities' => [
            ['name' => 'Post', 'fields' => [['name' => 'title', 'required' => true]]],
        ]];
        $blueprints = app(FactoryBlueprintService::class);
        $version = $blueprints->importSchema($blueprint, $schema);
        $blueprints->importSchema($blueprint, $schema);
        $this->assertSame('published', $blueprints->publishVersion($version)->status);
        $this->assertDatabaseCount('factory_blueprint_versions', 1);
        $this->assertDatabaseCount('factory_blueprint_entities', 1);
        $this->assertDatabaseCount('factory_blueprint_fields', 1);
        $this->assertSame($product->id, $blueprint->sourceProduct->id);

        $capabilities = app(FactoryCapabilityService::class);
        $capability = $capabilities->create(['name' => 'CI CMS']);
        $dependency = $capabilities->create(['name' => 'CI Login']);
        $capabilities->publishVersion($capability, '1.0.0', ['cms' => true]);
        $capabilities->publishVersion($capability, '1.0.0', ['cms' => true]);
        $capabilities->addDependency($capability, $dependency);
        $capabilities->addDependency($capability, $dependency);
        $capabilities->attachBlueprint($capability, $blueprint);
        $capabilities->attachProduct($capability, $product);
        $this->assertDatabaseCount('factory_capability_versions', 1);
        $this->assertDatabaseCount('factory_capability_links', 3);

        $build = app(FactoryMiniBuilder::class)->generate($product->name, $blueprint, [$capability->name]);
        Storage::disk('local')->assertExists($build['files']);
        $readme = Storage::disk('local')->get($build['path'].'/README.md');
        $this->assertStringContainsString($blueprint->name, $readme);
        $this->assertStringContainsString($capability->name, $readme);
        $composer = json_decode(Storage::disk('local')->get($build['path'].'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('vitrine-ia/ci-product', $composer['name']);
    }

    public function test_mission_agent_steps_and_logs_remain_traceable(): void
    {
        $product = FactoryProduct::create(['name' => 'Mission Product', 'slug' => 'mission-product']);
        $agent = FactoryAgent::create(['name' => 'CI Agent', 'slug' => 'ci-agent', 'role' => 'qa']);
        $mission = FactoryMission::create([
            'title' => 'CI Mission', 'objective' => 'Validate canonical records',
            'product_id' => $product->id, 'agent_id' => $agent->id,
        ]);
        $runner = app(FactoryMissionRunner::class);
        $runner->createDefaultSteps($mission);
        $runner->createDefaultSteps($mission);
        $this->assertSame(5, $mission->steps()->count());
        $step = $runner->assignAgent($mission->steps()->first(), $agent);
        $this->assertSame($agent->id, $step->agent->id);
        $result = $runner->run($mission);
        $this->assertSame('completed', $result->status);
        $this->assertTrue($result->result['ok']);
        $this->assertNotNull($result->started_at);
        $this->assertNotNull($result->finished_at);
        $this->assertSame(5, $result->steps()->where('status', 'completed')->count());
        $this->assertSame(6, $result->logs()->count());
        $this->assertSame(1, $agent->logs()->count());
        $this->assertSame('ready', app(FactoryAIAnalysisEngine::class)->analyzeMission($result)['status']);
    }

    public function test_github_deployment_and_analysis_plans_do_not_execute_deploys(): void
    {
        $product = FactoryProduct::create(['name' => 'Plan Product', 'slug' => 'plan-product']);
        $this->assertFalse(app(FactoryDeploymentEngine::class)->plan($product)['ready']);
        $this->assertSame('attention', app(FactoryAIAnalysisEngine::class)->analyzeProduct($product)['status']);
        $product->update([
            'github_repository' => 'git@github.com:example/project.git',
            'description' => 'CI plan only',
            'product_dna' => ['default_branch' => 'hml', 'deploy_path' => '/isolated/project'],
        ]);
        $context = app(FactoryGitHubEngine::class)->buildContext($product->refresh());
        $this->assertSame('example/project', $context['repository_slug']);
        $this->assertSame('hml', $context['branch']);
        $this->assertTrue(app(FactoryDeploymentEngine::class)->plan($product)['ready']);
        $this->assertSame('ready', app(FactoryAIAnalysisEngine::class)->analyzeProduct($product)['status']);
    }

    public function test_seeders_are_idempotent_and_filament_pages_render_with_database(): void
    {
        Storage::fake('local');
        $this->seed();
        $models = [Engine::class, EngineType::class, FactoryProduct::class, FactoryBlueprint::class,
            FactoryBlueprintEntity::class, FactoryBlueprintColumn::class, FactoryBlueprintVersion::class,
            FactoryCapability::class, FactoryCapabilityVersion::class, FactoryCapabilityRelation::class,
            FactoryAgent::class, FactoryMission::class];
        $counts = [];
        foreach ($models as $model) {
            $counts[$model] = $model::count();
            if ($model !== FactoryCapabilityRelation::class) {
                $this->assertGreaterThan(0, $counts[$model], $model);
            }
        }
        $this->seed();
        foreach ($models as $model) {
            $this->assertSame($counts[$model], $model::count(), $model);
        }
        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        Filament::bootCurrentPanel();
        $this->actingAs(User::factory()->create());
        Livewire::test(FactoryDashboard::class)->assertSuccessful();
        Livewire::test(FactoryBuilderPage::class)
            ->fillForm(['project_name' => 'CI UI Build', 'blueprint_id' => FactoryBlueprint::first()->id, 'capabilities' => ['CMS']])
            ->call('generate')
            ->assertHasNoFormErrors();
        Storage::disk('local')->assertExists('factory-builds/ci-ui-build/README.md');
    }
}
