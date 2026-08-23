<?php

namespace App\Factory\Services;

use App\Models\FactoryIntake;
use App\Models\FactoryOpportunity;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class FactoryOpportunityService
{
    public function materializeApprovedAnalysis(FactoryIntake $intake): array
    {
        if ($intake->analysis_status !== 'approved') {
            throw new InvalidArgumentException('Only an approved Factory AI analysis can create opportunities.');
        }

        if ($intake->output_mode !== 'opportunity') {
            throw new InvalidArgumentException('Factory intake is not configured for opportunity output.');
        }

        $analysis = $intake->ai_analysis ?? [];
        $opportunities = Arr::get($analysis, 'opportunities', []);

        if (! is_array($opportunities)) {
            throw new InvalidArgumentException('Approved analysis has no valid opportunities collection.');
        }

        $created = [];

        foreach ($opportunities as $item) {
            if (! is_array($item) || empty($item['title']) || empty($item['opportunity_type'])) {
                continue;
            }

            $opportunity = FactoryOpportunity::updateOrCreate(
                [
                    'intake_id' => $intake->id,
                    'title' => $item['title'],
                    'source_url' => $item['source_url'] ?? null,
                ],
                [
                    'product_id' => $intake->product_id,
                    'profile_type' => Arr::get($analysis, 'profile_type'),
                    'opportunity_type' => $item['opportunity_type'],
                    'status' => $item['status'] ?? 'identified',
                    'organization' => $item['organization'] ?? null,
                    'territory' => $item['territory'] ?? null,
                    'source' => $item['source'] ?? null,
                    'deadline_at' => $item['deadline_at'] ?? null,
                    'match_score' => $item['match_score'] ?? null,
                    'match_analysis' => $item['match_analysis'] ?? null,
                    'requirements' => $item['requirements'] ?? null,
                    'gaps' => $item['gaps'] ?? null,
                    'action_plan' => $item['action_plan'] ?? null,
                    'evidence' => $item['evidence'] ?? null,
                    'opportunity_dna' => array_merge($item['opportunity_dna'] ?? [], [
                        'generated_from_intake_id' => $intake->id,
                        'factory_contract' => 'factory.intake.analysis.v3',
                        'profile_type' => Arr::get($analysis, 'profile_type'),
                    ]),
                    'qualified_at' => ($item['match_score'] ?? 0) > 0 ? now() : null,
                ]
            );

            $created[] = $opportunity;
        }

        $intake->forceFill([
            'status' => 'converted',
            'intake_dna' => array_merge($intake->intake_dna ?? [], [
                'opportunities_materialized' => true,
                'opportunities_count' => count($created),
                'materialized_at' => now()->toIso8601String(),
            ]),
        ])->save();

        return $created;
    }
}
