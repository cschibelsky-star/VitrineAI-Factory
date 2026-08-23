<?php

namespace App\Factory\Services;

use App\Models\FactoryOpportunity;
use InvalidArgumentException;

class FactoryOpportunityMatchingEngine
{
    public const CONTRACT = 'factory.opportunity.match.v2';

    public function defaultWeights(): array
    {
        return [
            'eligibility' => 25,
            'segment' => 15,
            'territory' => 15,
            'audience' => 10,
            'documentation' => 15,
            'capacity' => 10,
            'deadline' => 5,
            'risk' => 5,
        ];
    }

    public function buildAssessmentRequest(FactoryOpportunity $opportunity, array $profileDna): array
    {
        return [
            'contract' => self::CONTRACT,
            'objective' => 'Assess evidence for each deterministic matching criterion. Do not calculate the final weighted score.',
            'weights' => $this->defaultWeights(),
            'profile_dna' => $profileDna,
            'opportunity' => [
                'id' => $opportunity->id,
                'profile_type' => $opportunity->profile_type,
                'opportunity_type' => $opportunity->opportunity_type,
                'title' => $opportunity->title,
                'organization' => $opportunity->organization,
                'territory' => $opportunity->territory,
                'deadline_at' => optional($opportunity->deadline_at)->toIso8601String(),
                'requirements' => $opportunity->requirements ?? [],
                'opportunity_dna' => $opportunity->opportunity_dna ?? [],
                'source' => $opportunity->source,
                'source_url' => $opportunity->source_url,
            ],
            'criteria' => [
                'eligibility' => 'Legal/institutional eligibility and mandatory participation rules.',
                'segment' => 'Compatibility between activity/cause/need and the opportunity object.',
                'territory' => 'Geographic eligibility and practical area of operation.',
                'audience' => 'Compatibility of target public, beneficiaries or buyer profile.',
                'documentation' => 'Availability of required documents, registrations, certificates and evidence.',
                'capacity' => 'Technical, operational, financial and execution capacity.',
                'deadline' => 'Time feasibility considering the deadline and preparation effort.',
                'risk' => 'Disqualifying, legal, operational or evidentiary risks. Higher score means lower risk.',
            ],
            'required_output' => [
                'criteria' => [
                    '<criterion>' => [
                        'score_0_100',
                        'evidence',
                        'missing_evidence',
                        'reasoning_summary',
                        'hard_blocker',
                    ],
                ],
                'requirements_met',
                'requirements_unmet',
                'gaps',
                'risks',
                'action_plan',
                'recommendation',
            ],
            'rules' => [
                'do_not_calculate_final_score' => true,
                'do_not_invent_documents_or_eligibility' => true,
                'missing_evidence_must_reduce_confidence' => true,
                'hard_blockers_must_be_explicit' => true,
                'source_facts_take_precedence_over_inference' => true,
            ],
        ];
    }

    public function calculate(array $assessment, ?array $weights = null): array
    {
        $weights = $weights ?? $this->defaultWeights();
        $criteria = $assessment['criteria'] ?? null;

        if (! is_array($criteria)) {
            throw new InvalidArgumentException('Matching assessment requires a criteria map.');
        }

        $weighted = 0.0;
        $weightTotal = 0.0;
        $breakdown = [];
        $hardBlockers = [];

        foreach ($weights as $criterion => $weight) {
            $entry = $criteria[$criterion] ?? [];
            $score = $entry['score_0_100'] ?? null;

            if (! is_numeric($score)) {
                $score = 0;
            }

            $score = max(0, min(100, (float) $score));
            $weight = max(0, (float) $weight);
            $contribution = $weight > 0 ? ($score / 100) * $weight : 0;

            $weighted += $contribution;
            $weightTotal += $weight;

            $isHardBlocker = (bool) ($entry['hard_blocker'] ?? false);
            if ($isHardBlocker) {
                $hardBlockers[] = $criterion;
            }

            $breakdown[$criterion] = [
                'weight' => $weight,
                'score' => round($score, 2),
                'contribution' => round($contribution, 2),
                'evidence' => $entry['evidence'] ?? [],
                'missing_evidence' => $entry['missing_evidence'] ?? [],
                'reasoning_summary' => $entry['reasoning_summary'] ?? null,
                'hard_blocker' => $isHardBlocker,
            ];
        }

        $score = $weightTotal > 0 ? ($weighted / $weightTotal) * 100 : 0;
        $score = round(max(0, min(100, $score)), 2);

        if ($hardBlockers !== []) {
            $score = min($score, 49.99);
        }

        return [
            'contract' => self::CONTRACT,
            'match_score' => $score,
            'match_level' => $this->level($score, $hardBlockers),
            'hard_blockers' => $hardBlockers,
            'criteria_breakdown' => $breakdown,
            'weights' => $weights,
            'requirements_met' => $assessment['requirements_met'] ?? [],
            'requirements_unmet' => $assessment['requirements_unmet'] ?? [],
            'gaps' => $assessment['gaps'] ?? [],
            'risks' => $assessment['risks'] ?? [],
            'action_plan' => $assessment['action_plan'] ?? [],
            'recommendation' => $assessment['recommendation'] ?? null,
            'engine' => 'deterministic-weighted-v1',
        ];
    }

    private function level(float $score, array $hardBlockers): string
    {
        if ($hardBlockers !== []) {
            return 'blocked';
        }

        return match (true) {
            $score >= 85 => 'very_high',
            $score >= 70 => 'high',
            $score >= 50 => 'partial',
            default => 'low',
        };
    }
}
