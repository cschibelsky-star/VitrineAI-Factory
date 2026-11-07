<?php

namespace Tests\Feature;

use App\Models\FactoryMission;
use App\Services\FactoryMissionStepExecutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryViaCorrectionApiTest extends TestCase
{
    use RefreshDatabase;

    private function setupStep(): array
    {
        config([
            'factory_via.correction_enabled' => true,
            'factory_via.correction_token' => str_repeat('C', 40),
            'factory_via.read_token' => str_repeat('R', 40),
        ]);
        $mission = FactoryMission::create(['title' => 'Internal correction', 'status' => 'blocked']);
        $step = $mission->steps()->create([
            'name' => 'Repair', 'status' => 'rework_required', 'order_column' => 1,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
            'result' => ['attempt' => 1, 'ok' => false, 'qa' => ['reason' => 'rejected']],
        ]);
        return [$mission, $step, '/api/via/factory/missions/'.$mission->id.'/steps/'.$step->id.'/correction'];
    }

    private function headers(): array { return ['Authorization' => 'Bearer '.str_repeat('C', 40)]; }
    private function body(): array { return ['reason' => 'Corrigir falha comprovada', 'expected_attempt' => 1]; }

    public function test_disabled_or_shared_credential_cannot_write(): void
    {
        [$mission, $step, $url] = $this->setupStep();
        config(['factory_via.correction_enabled' => false]);
        $this->postJson($url, $this->body(), $this->headers())->assertStatus(503);
        config(['factory_via.correction_enabled' => true, 'factory_via.correction_token' => str_repeat('R', 40)]);
        $this->postJson($url, $this->body(), $this->headers())->assertStatus(503);
        $this->assertSame('rework_required', $step->fresh()->status);
    }

    public function test_reader_and_anonymous_cannot_request_correction(): void
    {
        [$mission, $step, $url] = $this->setupStep();
        $this->postJson($url, $this->body())->assertUnauthorized();
        $this->postJson($url, $this->body(), ['Authorization' => 'Bearer '.str_repeat('R', 40)])->assertUnauthorized();
        $this->assertSame('rework_required', $step->fresh()->status);
    }

    public function test_authorized_correction_is_audited_but_never_approves_or_executes(): void
    {
        [$mission, $step, $url] = $this->setupStep();
        $this->postJson($url, $this->body(), $this->headers())->assertStatus(202)
            ->assertJsonPath('status', 'pending')->assertJsonPath('execution_started', false)
            ->assertJsonPath('approval_granted', false);
        $this->assertSame('blocked', $mission->fresh()->status);
        $this->assertSame(1, $step->logs()->count());
        $this->postJson($url, $this->body(), $this->headers())->assertStatus(409);
        $this->assertSame(1, $step->logs()->count());
    }

    public function test_stale_attempt_and_payload_changes_are_rejected(): void
    {
        [$mission, $step, $url] = $this->setupStep();
        $this->postJson($url, ['reason' => 'Revisar', 'expected_attempt' => 0], $this->headers())->assertStatus(409);
        $this->postJson($url, $this->body() + ['agent_id' => 77, 'approved' => true], $this->headers())->assertStatus(422);
        $this->assertSame('rework_required', $step->fresh()->status);
    }

    public function test_step_from_another_mission_is_not_accessible(): void
    {
        [$mission, $step, $url] = $this->setupStep();
        $other = FactoryMission::create(['title' => 'Other', 'status' => 'blocked']);
        $this->postJson('/api/via/factory/missions/'.$other->id.'/steps/'.$step->id.'/correction',
            $this->body(), $this->headers())->assertNotFound();
    }

    public function test_external_product_actions_are_denied(): void
    {
        [$mission, $step, $url] = $this->setupStep();
        $step->update(['payload' => ['action' => 'deploy.production']]);
        $this->postJson($url, $this->body(), $this->headers())->assertStatus(409)
            ->assertJsonPath('error', 'internal_scope_only');
    }

    public function test_production_disables_correction_even_with_valid_token(): void
    {
        [$mission, $step, $url] = $this->setupStep();
        $this->app->detectEnvironment(fn () => 'production');
        $this->postJson($url, $this->body(), $this->headers())->assertStatus(503);
    }
}
