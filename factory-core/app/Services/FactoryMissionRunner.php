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
        $mission->update(['status' => 'running', 'started_at' => now()]);

        FactoryMissionLog::create([
            'mission_id' => $mission->id,
            'level' => 'info',
            'message' => 'Missão iniciada pela Vitrine IA Factory.',
        ]);

        foreach ($mission->steps()->orderBy('order_column')->get() as $step) {
            $step->update(['status' => 'running', 'started_at' => now()]);

            FactoryMissionLog::create([
                'mission_id' => $mission->id,
                'step_id' => $step->id,
                'agent_id' => $step->agent_id,
                'level' => 'info',
                'message' => 'Etapa executada: ' . $step->name,
            ]);

            $step->update([
                'status' => 'completed',
                'result' => ['ok' => true],
                'finished_at' => now(),
            ]);
        }

        $mission->update([
            'status' => 'completed',
            'finished_at' => now(),
            'result' => ['ok' => true, 'message' => 'Missão concluída'],
        ]);

        return $mission->refresh();
    }

    public function assignAgent(FactoryMissionStep $step, FactoryAgent $agent): FactoryMissionStep
    {
        $step->update(['agent_id' => $agent->id]);

        return $step->refresh();
    }
}
