<?php

namespace Tests\Feature;

use App\Models\FactoryMission;
use App\Services\FactoryMissionRunner;
use App\Services\FactoryMissionStepExecutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactoryMissionGateTest extends TestCase
{
    use RefreshDatabase;

    private function createMission(): FactoryMission
    {
        return FactoryMission::create([
            'title' => 'Prova interna de gates',
            'objective' => 'Comprovar execucao real e QA independente.',
            'status' => 'planned',
        ]);
    }

    public function test_default_steps_never_complete_without_executor(): void
    {
        $mission = $this->createMission();
        $runner = app(FactoryMissionRunner::class);
        $runner->createDefaultSteps($mission);

        $result = $runner->run($mission);
        $this->assertSame('blocked', $result->status);
        $this->assertFalse($result->result['ok']);
        $this->assertSame(0, $result->steps()->where('status', 'completed')->count());
        $this->assertSame(4, $result->steps()->where('status', 'pending')->count());
        $this->assertSame(1, $result->steps()->where('status', 'blocked')->count());
        $this->assertTrue($result->logs()->where('level', 'warning')->exists());
    }

    public function test_internal_scaffold_creates_real_files_and_passes_independent_qa(): void
    {
        Storage::fake('local');
        $mission = $this->createMission();
        $step = $mission->steps()->create([
            'name' => 'Prova isolada',
            'status' => 'pending',
            'order_column' => 1,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        $result = app(FactoryMissionRunner::class)->run($mission);
        $this->assertSame('completed', $result->status);
        $this->assertSame('completed', $step->fresh()->status);
        $evidence = $step->fresh()->result['evidence'];
        $this->assertCount(4, $evidence['files']);
        $this->assertTrue($step->fresh()->result['qa']['approved']);
        foreach ($evidence['files'] as $file) {
            Storage::disk('local')->assertExists($file);
            $this->assertSame(hash('sha256', Storage::disk('local')->get($file)), $evidence['sha256'][$file]);
        }
    }

    public function test_unsupported_action_blocks_mission_and_no_artifacts(): void
    {
        Storage::fake('local');
        $mission = $this->createMission();
        $mission->steps()->create([
            'name' => 'Acao nao autorizada',
            'status' => 'pending',
            'order_column' => 1,
            'payload' => ['action' => 'deploy.production'],
        ]);
        $result = app(FactoryMissionRunner::class)->run($mission);
        $this->assertSame('blocked', $result->status);
        $this->assertSame(0, $result->steps()->where('status', 'completed')->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_second_step_cannot_run_after_first_step_fails(): void
    {
        Storage::fake('local');
        $mission = $this->createMission();
        $mission->steps()->create([
            'name' => 'Falha',
            'status' => 'pending',
            'order_column' => 1,
            'payload' => ['action' => 'unsupported'],
        ]);
        $second = $mission->steps()->create([
            'name' => 'Etapa seguinte',
            'status' => 'pending',
            'order_column' => 2,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        $this->assertSame('blocked', app(FactoryMissionRunner::class)->run($mission)->status);
        $this->assertSame('pending', $second->fresh()->status);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }
    public function test_preparing_steps_again_does_not_erase_existing_approval_or_block(): void
    {
        $mission = $this->createMission();
        $runner = app(FactoryMissionRunner::class);
        $runner->createDefaultSteps($mission);
        $first = $mission->steps()->orderBy('order_column')->firstOrFail();
        $first->update(['status' => 'blocked', 'result' => ['ok' => false, 'reason' => 'qa_failed']]);

        $runner->createDefaultSteps($mission);

        $this->assertSame('blocked', $first->fresh()->status);
        $this->assertSame('qa_failed', $first->fresh()->result['reason']);
        $this->assertSame(5, $mission->steps()->count());
    }

    public function test_qa_rejects_artifact_tampering(): void
    {
        Storage::fake('local');
        $mission = $this->createMission();
        $step = $mission->steps()->create([
            'name' => 'Prova de integridade',
            'status' => 'pending',
            'order_column' => 1,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        $evidence = app(FactoryMissionStepExecutor::class)->execute($step);
        Storage::disk('local')->put($evidence['files'][0], 'arquivo adulterado');

        $qa = app(\App\Services\FactoryMissionArtifactQa::class)->verify($step, $evidence);
        $this->assertFalse($qa['approved']);
        $this->assertSame('missing_or_modified_artifact', $qa['reason']);
    }

    public function test_rejection_returns_for_correction_without_releasing_next_agent(): void
    {
        Storage::fake('local');
        $mission = $this->createMission();
        $first = $mission->steps()->create([
            'name' => 'Primeira', 'status' => 'pending', 'order_column' => 1,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        $second = $mission->steps()->create([
            'name' => 'Segunda', 'status' => 'pending', 'order_column' => 2,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        $this->mock(\App\Services\FactoryMissionArtifactQa::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn(['approved' => false, 'reason' => 'test_rejection']);
        });
        $runner = app(FactoryMissionRunner::class);
        $this->assertSame('blocked', $runner->run($mission)->status);
        $this->assertSame('rework_required', $first->fresh()->status);
        $this->assertSame('pending', $second->fresh()->status);
        $oldEvidence = $first->fresh()->result['evidence'];
        $this->app->forgetInstance(\App\Services\FactoryMissionArtifactQa::class);
        $runner->requestCorrection($first, 'Corrigir entrega reprovada');
        $this->assertSame('completed', $runner->run($mission)->status);
        $this->assertSame(2, $first->fresh()->result['attempt']);
        $this->assertNotSame($oldEvidence['artifact_path'], $first->fresh()->result['evidence']['artifact_path']);
        foreach ($oldEvidence['files'] as $file) {
            Storage::disk('local')->assertExists($file);
        }
        $this->assertTrue($mission->logs()->where('message', 'VIA: etapa devolvida ao responsavel para correcao.')->exists());
    }

    public function test_modified_approved_artifact_blocks_new_downstream_step(): void
    {
        Storage::fake('local');
        $mission = $this->createMission();
        $first = $mission->steps()->create([
            'name' => 'Primeira', 'status' => 'pending', 'order_column' => 1,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        $runner = app(FactoryMissionRunner::class);
        $runner->run($mission);
        $second = $mission->steps()->create([
            'name' => 'Segunda', 'status' => 'pending', 'order_column' => 2,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        Storage::disk('local')->put($first->fresh()->result['evidence']['files'][0], 'adulterado');
        $this->assertSame('blocked', $runner->run($mission)->status);
        $this->assertSame('blocked', $first->fresh()->status);
        $this->assertFalse($first->fresh()->result['ok']);
        $this->assertSame('pending', $second->fresh()->status);
    }

    public function test_three_failed_attempts_require_supervisor_review(): void
    {
        Storage::fake('local');
        $mission = $this->createMission();
        $step = $mission->steps()->create([
            'name' => 'Limite', 'status' => 'pending', 'order_column' => 1,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        $this->mock(\App\Services\FactoryMissionArtifactQa::class, function ($mock) {
            $mock->shouldReceive('verify')->times(3)->andReturn(['approved' => false, 'reason' => 'test_rejection']);
        });
        $runner = app(FactoryMissionRunner::class);
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $runner->run($mission);
            $this->assertSame($attempt, $step->fresh()->result['attempt']);
            if ($attempt < 3) {
                $runner->requestCorrection($step, 'Revisar');
            }
        }
        $this->expectException(\RuntimeException::class);
        $runner->requestCorrection($step, 'Nao permitir loop infinito');
    }

    public function test_completed_step_cannot_be_reopened_as_correction(): void
    {
        Storage::fake('local');
        $mission = $this->createMission();
        $step = $mission->steps()->create([
            'name' => 'Aprovada', 'status' => 'pending', 'order_column' => 1,
            'payload' => ['action' => FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
        ]);
        $runner = app(FactoryMissionRunner::class);
        $runner->run($mission);
        $this->expectException(\RuntimeException::class);
        $runner->requestCorrection($step, 'Reabrir indevidamente');
    }
}
