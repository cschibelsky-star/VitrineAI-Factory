<?php

namespace App\Http\Controllers;

use App\Models\FactoryMission;
use App\Services\FactoryMissionRunner;
use App\Services\FactoryMissionStepExecutor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ViaFactoryCorrectionController extends Controller
{
    public function store(Request $request, int $mission, int $step): JsonResponse
    {
        $token = config('factory_via.correction_token');
        $reader = config('factory_via.read_token');
        if (app()->environment('production')
            || ! config('factory_via.correction_enabled')
            || ! is_string($token) || strlen($token) < 32
            || (is_string($reader) && hash_equals($token, $reader))) {
            return response()->json(['error' => 'correction_not_configured'], 503);
        }
        $provided = $request->bearerToken();
        if (! is_string($provided) || ! hash_equals($token, $provided)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
            'expected_attempt' => ['required', 'integer', 'min:0', 'max:2'],
        ]);
        // Never accept new action, agent, approval or payload through this command.
        if (array_diff(array_keys($request->all()), ['reason', 'expected_attempt']) !== []) {
            return response()->json(['error' => 'unsupported_fields'], 422);
        }
        $record = FactoryMission::find($mission);
        $target = $record?->steps()->whereKey($step)->first();
        if (! $target) {
            return response()->json(['error' => 'step_not_found'], 404);
        }
        if ($record->steps()->where(function ($query) {
            $query->whereNull('payload')->orWhere('payload->action', '!=', FactoryMissionStepExecutor::INTERNAL_SCAFFOLD)
                ->orWhereNull('payload->action');
        })->exists()) {
            return response()->json(['error' => 'internal_scope_only'], 409);
        }
        try {
            $updated = app(FactoryMissionRunner::class)->requestCorrection(
                $target, $data['reason'], (int) $data['expected_attempt']
            );
        } catch (\RuntimeException $exception) {
            return response()->json(['error' => 'correction_conflict'], 409);
        }
        return response()->json([
            'schema' => 'factory.correction.v1',
            'mission_id' => $mission, 'step_id' => $updated->id,
            'status' => $updated->status, 'agent_id' => $updated->agent_id,
            'attempt' => (int) ($updated->result['attempt'] ?? 0),
            'execution_started' => false, 'approval_granted' => false,
        ], 202);
    }
}
