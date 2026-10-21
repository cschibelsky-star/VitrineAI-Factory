<?php

namespace Tests\Feature;

use App\Models\FactoryMission;
use App\Services\FactoryMissionStepExecutor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryViaMissionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_dedicated_credential_disables_endpoint(): void
    {
        config(['factory_via.read_token' => null]);
        $this->getJson('/api/via/factory/missions/1')
            ->assertStatus(503)->assertJsonPath('error', 'integration_not_configured');
    }

    public function test_invalid_bearer_is_denied_and_no_mission_data_is_returned(): void
    {
        config(['factory_via.read_token' => str_repeat('X', 40)]);
        $this->getJson('/api/via/factory/missions/1', ['Authorization' => 'Bearer wrong'])
            ->assertStatus(401)->assertJsonPath('error', 'unauthorized');
    }

    public function test_authorized_reader_reports_artifact_gate_without_claiming_independent_qa(): void
    {
        config(['factory_via.read_token' => str_repeat('X', 40)]);
        $mission = FactoryMission::create(['title' => 'Internal only', 'status' => 'blocked']);
        $this->getJson('/api/via/factory/missions/'.$mission->id, [
            'Authorization' => 'Bearer '.str_repeat('X', 40),
        ])->assertOk()
            ->assertJsonPath('schema', 'factory.mission.read.v1')
            ->assertJsonPath('mission.status', 'blocked')
            ->assertJsonPath('mission.artifact_gate_passed', false)
            ->assertJsonPath('mission.independent_qa_verified', false)
            ->assertJsonPath('read_only', true);
    }

    public function test_read_time_validation_refuses_modified_artifacts(): void
    {
        Storage::fake('local');
        config(['factory_via.read_token' => str_repeat('X', 40)]);
        $mission = FactoryMission::create([
            'title' => 'Internal integrity test',
            'objective' => 'Validate stored evidence again before responding.',
            'status' => 'planned',
        ]);
        $step = $mission->steps()->create([
            'name' => 'Internal scaffold',
            'status' => 'pending',
            'order_column' => 1,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        $evidence = app(FactoryMissionStepExecutor::class)->execute($step);
        $step->update([
            'status' => 'completed',
            'result' => ['ok' => true, 'evidence' => $evidence, 'qa' => ['approved' => true]],
        ]);
        $mission->update(['status' => 'completed', 'result' => ['ok' => true]]);
        $headers = ['Authorization' => 'Bearer '.str_repeat('X', 40)];

        $this->getJson('/api/via/factory/missions/'.$mission->id, $headers)
            ->assertOk()->assertJsonPath('mission.artifact_gate_passed', true)
            ->assertJsonPath('mission.independent_qa_verified', false);

        Storage::disk('local')->put($evidence['files'][0], 'content changed after QA');

        $this->getJson('/api/via/factory/missions/'.$mission->id, $headers)
            ->assertOk()->assertJsonPath('mission.artifact_gate_passed', false)
            ->assertJsonPath('steps.0.qa_artifact_gate_approved', false)
            ->assertJsonPath('mission.independent_qa_verified', false);
    }

}
