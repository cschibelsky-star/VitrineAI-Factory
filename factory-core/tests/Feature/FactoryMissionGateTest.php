<?php

namespace Tests\Feature;

use App\Models\FactoryMission;
use App\Services\FactoryMissionRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryMissionGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_mission_stays_blocked_without_a_real_executor_and_independent_qa(): void
    {
        $mission = FactoryMission::create([
            'title' => 'Validar gate de execucao real',
            'objective' => 'Nao declarar entrega sem executor e QA.',
            'status' => 'planned',
        ]);

        $runner = app(FactoryMissionRunner::class);
        $runner->createDefaultSteps($mission);
        $result = $runner->run($mission);

        $this->assertSame('blocked', $result->status);
        $this->assertFalse($result->result['ok']);
        $this->assertSame('executor_and_independent_qa_not_integrated', $result->result['reason']);
        $this->assertNull($result->finished_at);
        $this->assertSame(0, $result->steps()->where('status', 'completed')->count());
        $this->assertSame(5, $result->steps()->where('status', 'pending')->count());
        $this->assertTrue($result->logs()->where('level', 'warning')->exists());
    }
}
