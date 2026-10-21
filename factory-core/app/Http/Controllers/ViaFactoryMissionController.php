<?php

namespace App\Http\Controllers;

use App\Models\FactoryMission;
use App\Services\FactoryMissionArtifactQa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Read-only, least-privilege integration for the VIA supervisor.
 *
 * No mission creation, execution, approvals or secret values are returned.
 * Independent human/agent QA must not be inferred from an internal hash check.
 */
class ViaFactoryMissionController extends Controller
{
    public function show(Request $request, int $mission): JsonResponse
    {
        $expected = config('factory_via.read_token');

        // Fail closed whenever a dedicated credential has not been provisioned.
        if (! is_string($expected) || strlen($expected) < 32) {
            return response()->json(['error' => 'integration_not_configured'], 503);
        }

        $provided = $request->bearerToken();
        if (! is_string($provided) || ! hash_equals($expected, $provided)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $record = FactoryMission::query()->with(['steps' => function ($query) {
            $query->orderBy('order_column');
        }])->find($mission);

        if (! $record) {
            return response()->json(['error' => 'mission_not_found'], 404);
        }

        $steps = $record->steps->map(function ($step) {
            $result = $step->result ?? [];
            $evidence = is_array($result['evidence'] ?? null) ? $result['evidence'] : [];
            $qa = is_array($result['qa'] ?? null) ? $result['qa'] : [];
            // Never trust a stored approval: files may have changed after the run.
            $verifiedNow = $step->status === 'completed'
                && ($result['ok'] ?? false) === true
                && ($qa['approved'] ?? false) === true
                && app(FactoryMissionArtifactQa::class)->verify($step, $evidence)['approved'] === true;

            return [
                'id' => $step->id,
                'name' => $step->name,
                'status' => $step->status,
                'agent_id' => $step->agent_id,
                'qa_artifact_gate_approved' => $verifiedNow,
                'evidence' => [
                    'scope' => $evidence['scope'] ?? null,
                    'files' => $evidence['files'] ?? [],
                    'sha256' => $evidence['sha256'] ?? [],
                ],
                'started_at' => $step->started_at?->toIso8601String(),
                'finished_at' => $step->finished_at?->toIso8601String(),
            ];
        });

        $gateApproved = $steps->isNotEmpty()
            && $steps->every(fn ($step) => $step['status'] === 'completed'
                && $step['qa_artifact_gate_approved'] === true);

        return response()->json([
            'schema' => 'factory.mission.read.v1',
            'mission' => [
                'id' => $record->id,
                'title' => $record->title,
                'status' => $record->status,
                'priority' => $record->priority,
                'started_at' => $record->started_at?->toIso8601String(),
                'finished_at' => $record->finished_at?->toIso8601String(),
                'artifact_gate_passed' => $record->status === 'completed'
                    && ($record->result['ok'] ?? false) === true
                    && $gateApproved,
                'independent_qa_verified' => false,
            ],
            'steps' => $steps,
            'read_only' => true,
        ]);
    }
}
