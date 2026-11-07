<?php

namespace App\Services;

use App\Models\FactoryAgent;
use App\Models\FactoryMission;
use App\Models\FactoryMissionLog;
use App\Models\FactoryMissionStep;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($mission): FactoryMission {
            $locked = FactoryMission::query()->lockForUpdate()->findOrFail($mission->id);
            return $this->runLocked($locked);
        });
    }

    private function runLocked(FactoryMission $mission): FactoryMission
    {
        $steps = $mission->steps()->orderBy('order_column')->orderBy('id')->get();

        // First execution capability is deliberately internal-only.
        // All other mission actions fail closed until an authorized executor exists.
        if ($steps->isEmpty() || app()->environment('production')) {
            return $this->block($mission, null, 'executor_not_available');
        }

        $mission->update(['status' => 'running', 'started_at' => now(), 'finished_at' => null]);

        foreach ($steps as $step) {
            if ($step->status === 'completed') {
                $stored = $step->result ?? [];
                try {
                    $qa = app(FactoryMissionArtifactQa::class)->verify($step, $stored['evidence'] ?? []);
                } catch (\Throwable $exception) {
                    return $this->block($mission, $step, 'approved_artifact_unverifiable');
                }
                if (($stored['ok'] ?? false) !== true || ($qa['approved'] ?? false) !== true) {
                    return $this->block($mission, $step, 'approved_artifact_changed');
                }
                continue;
            }
            if (($step->payload['action'] ?? null) !== FactoryMissionStepExecutor::INTERNAL_SCAFFOLD
                || $step->status !== 'pending') {
                return $this->block($mission, $step, 'unsupported_or_unready_step');
            }

            $attempt = (int) ($step->result['attempt'] ?? 0) + 1;
            if ($attempt > 3) {
                return $this->block($mission, $step, 'attempt_limit_requires_supervisor_review');
            }
            $step->update([
                'status' => 'running', 'started_at' => now(), 'finished_at' => null,
                'result' => ['ok' => false, 'attempt' => $attempt],
            ]);
            try {
                $evidence = app(FactoryMissionStepExecutor::class)->execute($step);
                $step->update(['result' => ['ok' => false, 'attempt' => $attempt, 'evidence' => $evidence]]);
                $qa = app(FactoryMissionArtifactQa::class)->verify($step, $evidence);
                $step->update(['result' => array_merge($step->result, ['qa' => $qa])]);
                if (($qa['approved'] ?? false) !== true) {
                    throw new \RuntimeException('QA reprovou: ' . ($qa['reason'] ?? 'unknown'));
                }

                $step->update([
                    'status' => 'completed',
                    'result' => ['ok' => true, 'attempt' => $attempt, 'evidence' => $evidence, 'qa' => $qa,
                        'validated_at' => now()->toIso8601String(), 'supervisor' => 'VIA'],
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
                return $this->block($mission, $step, 'execution_or_qa_failed');
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
        if ($step) {
            $step->update([
                'status' => $reason === 'execution_or_qa_failed' ? 'rework_required' : 'blocked',
                'result' => array_merge($step->result ?? [], ['ok' => false, 'reason' => $reason,
                    'return_to_agent_id' => $step->agent_id, 'supervisor' => 'VIA']),
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

    /**
     * Internal supervision command. Caller must enforce owner permissions.
     * No public write route is introduced by this service.
     */
    public function requestCorrection(FactoryMissionStep $step, string $reason): FactoryMissionStep
    {
        if (trim($reason) === '') {
            throw new \InvalidArgumentException('Motivo da correcao obrigatorio.');
        }
        return DB::transaction(function () use ($step, $reason): FactoryMissionStep {
            FactoryMission::query()->lockForUpdate()->findOrFail($step->mission_id);
            $current = FactoryMissionStep::query()->lockForUpdate()->findOrFail($step->id);
            if (! in_array($current->status, ['rework_required', 'blocked'], true)
                || (int) ($current->result['attempt'] ?? 0) >= 3) {
                throw new \RuntimeException('Etapa indisponivel para nova tentativa.');
            }
            // A correction never changes the assigned agent or earlier evidence.
            FactoryMissionLog::create([
                'mission_id' => $current->mission_id, 'step_id' => $current->id,
                'agent_id' => $current->agent_id, 'level' => 'info',
                'message' => 'VIA: etapa devolvida ao responsavel para correcao.',
                'context' => ['reason' => $reason, 'previous_result' => $current->result],
            ]);
            $current->update(['status' => 'pending', 'finished_at' => null]);
            return $current->refresh();
        });
    }

    public function assignAgent(FactoryMissionStep $step, FactoryAgent $agent): FactoryMissionStep
    {
        $step->update(['agent_id' => $agent->id]);

        return $step->refresh();
    }
}
