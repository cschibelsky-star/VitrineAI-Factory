<?php

namespace App\Services;

use App\Models\FactoryAgent;
use App\Models\FactoryMission;
use App\Models\FactoryMissionLog;
use App\Models\FactoryMissionStep;

class FactoryMissionRunner
{
    public function createDefaultSteps(FactoryMission $mission): void
    {
        $steps = [
            'Analisar missão',
            'Preparar blueprint',
            'Gerar estrutura',
            'Validar arquivos',
            'Registrar resultado',
        ];

        foreach ($steps as $index => $step) {
            FactoryMissionStep::firstOrCreate(
                ['mission_id' => $mission->id, 'name' => $step],
                ['status' => 'pending', 'order_column' => $index + 1]
            );
        }
    }

    public function run(FactoryMission $mission): FactoryMission
    {
        $steps = $mission->steps()->orderBy('order_column')->get();

        // First execution capability is deliberately internal-only.
        // All other mission actions fail closed until an authorized executor exists.
        if ($steps->isEmpty() || app()->environment('production')) {
            return $this->block($mission, null, 'executor_not_available');
        }

        $mission->update(['status' => 'running', 'started_at' => now(), 'finished_at' => null]);

        foreach ($steps as $step) {
            if ($step->status === 'completed') {
                return $this->block($mission, $step, 'previously_completed_step_requires_review');
            }
            if (($step->payload['action'] ?? null) !== FactoryMissionStepExecutor::INTERNAL_SCAFFOLD
                || $step->status !== 'pending') {
                return $this->block($mission, $step, 'unsupported_or_unready_step');
            }

            $step->update(['status' => 'running', 'started_at' => now()]);
            try {
                $evidence = app(FactoryMissionStepExecutor::class)->execute($step);
                $qa = app(FactoryMissionArtifactQa::class)->verify($step, $evidence);
                if (($qa['approved'] ?? false) !== true) {
                    throw new \RuntimeException('QA reprovou: ' . ($qa['reason'] ?? 'unknown'));
                }

                $step->update([
                    'status' => 'completed',
                    'result' => ['ok' => true, 'evidence' => $evidence, 'qa' => $qa],
                    'finished_at' => now(),
                ]);
                FactoryMissionLog::create([
                    'mission_id' => $mission->id,
                    'step_id' => $step->id,
                    'agent_id' => $step->agent_id,
                    'level' => 'info',
                    'message' => 'Etapa interna executada e validada por QA independente.',
                    'context' => ['qa' => $qa, 'evidence' => $evidence],
                ]);
            } catch (\Throwable $exception) {
                return $this->block($mission, $step, 'execution_or_qa_failed', $exception->getMessage());
            }
        }

        $mission->update([
            'status' => 'completed',
            'result' => ['ok' => true, 'scope' => 'factory-internal-test-only', 'verified_steps' => $steps->count()],
            'finished_at' => now(),
        ]);

        return $mission->refresh();
    }

    private function block(
        FactoryMission $mission,
        ?FactoryMissionStep $step,
        string $reason,
        ?string $detail = null
    ): FactoryMission {
        if ($step && $step->status !== 'completed') {
            $step->update([
                'status' => 'blocked',
                'result' => ['ok' => false, 'reason' => $reason],
                'finished_at' => now(),
            ]);
        }

        $mission->update([
            'status' => 'blocked',
            'result' => ['ok' => false, 'reason' => $reason],
            'finished_at' => null,
        ]);

        FactoryMissionLog::create([
            'mission_id' => $mission->id,
            'step_id' => $step?->id,
            'agent_id' => $step?->agent_id,
            'level' => 'warning',
            'message' => 'Gate de execucao ou QA bloqueou a missao.',
            'context' => ['reason' => $reason, 'detail' => $detail],
        ]);

        return $mission->refresh();
    }

    public function assignAgent(FactoryMissionStep $step, FactoryAgent $agent): FactoryMissionStep
    {
        $step->update(['agent_id' => $agent->id]);

        return $step->refresh();
    }
}
