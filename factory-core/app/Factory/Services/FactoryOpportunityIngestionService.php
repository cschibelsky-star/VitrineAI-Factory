<?php

namespace App\Factory\Services;

use App\Models\FactoryOpportunity;
use App\Models\FactoryOpportunitySource;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class FactoryOpportunityIngestionService
{
    public function __construct(
        private readonly FactoryOpportunityMatchingEngine $matchingEngine,
    ) {
    }

    public function ingest(FactoryOpportunitySource $source, array $payload): FactoryOpportunity
    {
        if (! in_array($source->status, ['active', 'manual'], true)) {
            throw new InvalidArgumentException('Opportunity source is not enabled for ingestion.');
        }

        $map = $source->mapping_contract ?? [];
        $externalId = $this->value($payload, $map['external_id'] ?? 'id');
        $title = $this->value($payload, $map['title'] ?? 'title');

        if (! $title) {
            throw new InvalidArgumentException('Normalized opportunity requires a title.');
        }

        $attributes = [
            'profile_type' => $this->value($payload, $map['profile_type'] ?? null),
            'opportunity_type' => $this->value($payload, $map['opportunity_type'] ?? null) ?: ($source->supported_opportunity_types[0] ?? 'other'),
            'status' => 'identified',
            'title' => $title,
            'organization' => $this->value($payload, $map['organization'] ?? 'organization'),
            'territory' => $this->value($payload, $map['territory'] ?? 'territory'),
            'source' => $source->name,
            'source_url' => $this->value($payload, $map['source_url'] ?? 'url') ?: $source->base_url,
            'external_id' => $externalId ? (string) $externalId : null,
            'ingestion_status' => 'normalized',
            'deadline_at' => $this->value($payload, $map['deadline_at'] ?? 'deadline_at'),
            'requirements' => $this->arrayValue($payload, $map['requirements'] ?? 'requirements'),
            'evidence' => [
                'source_slug' => $source->slug,
                'ingested_at' => now()->toIso8601String(),
            ],
            'opportunity_dna' => [
                'source_category' => $source->category,
                'source_scope' => $source->scope,
                'connector_type' => $source->connector_type,
            ],
            'raw_payload' => $payload,
        ];

        if ($externalId) {
            return FactoryOpportunity::updateOrCreate(
                ['source_id' => $source->id, 'external_id' => (string) $externalId],
                $attributes + ['source_id' => $source->id],
            );
        }

        return FactoryOpportunity::create($attributes + ['source_id' => $source->id]);
    }

    public function buildMatchingRequest(FactoryOpportunity $opportunity, array $profileDna): array
    {
        return $this->matchingEngine->buildAssessmentRequest($opportunity, $profileDna);
    }

    public function applyMatching(FactoryOpportunity $opportunity, array $assessment): FactoryOpportunity
    {
        $result = $this->matchingEngine->calculate(
            $assessment,
            null,
            $opportunity->profile_type,
        );

        $score = (float) $result['match_score'];
        $blocked = ($result['match_level'] ?? null) === 'blocked';

        $opportunity->forceFill([
            'match_score' => $score,
            'match_analysis' => $result,
            'gaps' => $result['gaps'] ?? $result['requirements_unmet'] ?? [],
            'action_plan' => $result['action_plan'] ?? [],
            'status' => (! $blocked && $score >= 70) ? 'qualified' : 'identified',
            'qualified_at' => (! $blocked && $score >= 70) ? now() : null,
            'evidence' => array_merge($opportunity->evidence ?? [], [
                'matching_contract' => FactoryOpportunityMatchingEngine::CONTRACT,
                'matching_engine' => $result['engine'] ?? 'deterministic-weighted-profile-v1',
                'matching_profile_type' => $opportunity->profile_type ?: 'generic',
                'matching_weights' => $result['weights'] ?? [],
                'matched_at' => now()->toIso8601String(),
            ]),
        ])->save();

        return $opportunity->refresh();
    }

    private function value(array $payload, ?string $path): mixed
    {
        return $path ? Arr::get($payload, $path) : null;
    }

    private function arrayValue(array $payload, ?string $path): array
    {
        $value = $this->value($payload, $path);

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            return [trim($value)];
        }

        return [];
    }
}
