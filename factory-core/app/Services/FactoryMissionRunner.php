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
            FactoryMissionStep::updateOrCreate(
                ['mission_id' => $mission->id, 'name' => $step],
                ['status' => 'pending', 'order_column' => $index + 1]
            );
        }
    }

    public function run(FactoryMission $mission): FactoryMission
    {
        // Fail closed: assigning an agent or recording a log is not proof of execution.
        // The canonical mission executor and independent QA gate are not integrated yet.
        // Do not start, complete or overwrite existing successful steps until those
        // contracts are implemented and tested end to end.
        $mission->update([
            'status' => 'blocked',
            'result' => [
                'ok' => false,
                'reason' => 'executor_and_independent_qa_not_integrated',
                'message' => 'Missao bloqueada: execucao real e evidencia de QA independente obrigatorias.',
            ],
            'finished_at' => null,
        ]);

        FactoryMissionLog::create([
            'mission_id' => $mission->id,
            'level' => 'warning',
            'message' => 'Gate da Factory bloqueou a missao: nenhum executor real e QA independente foram confirmados.',
            'context' => ['gate' => 'G3', 'reason' => 'executor_and_independent_qa_not_integrated'],
        ]);

        return $mission->refresh();
    }

    public function assignAgent(FactoryMissionStep $step, FactoryAgent $agent): FactoryMissionStep
    {
        $step->update(['agent_id' => $agent->id]);

        return $step->refresh();
    }
}
