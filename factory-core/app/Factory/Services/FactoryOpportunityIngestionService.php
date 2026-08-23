<?php

namespace App\Factory\Services;

use App\Models\FactoryOpportunity;
use App\Models\FactoryOpportunitySource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FactoryOpportunityIngestionService
{
    /**
     * Normalizes one external opportunity payload using the source mapping contract.
     * The service is transport-agnostic: HTTP/API/RSS/scraping connectors feed this method.
     */
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
        return [
            'contract' => 'factory.opportunity.match.v1',
            'objective' => 'Evaluate factual compatibility between a persistent profile/DNA and one normalized opportunity.',
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
            'required_output' => [
                'match_score',
                'match_level',
                'reasons',
                'requirements_met',
                'requirements_unmet',
                'gaps',
                'risks',
                'action_plan',
                'recommendation',
            ],
            'rules' => [
                'score_range_0_100' => true,
                'do_not_invent_documents_or_eligibility' => true,
                'identify_missing_evidence_explicitly' => true,
                'source_facts_take_precedence_over_inference' => true,
            ],
        ];
    }

    public function applyMatching(FactoryOpportunity $opportunity, array $result): FactoryOpportunity
    {
        if (! isset($result['match_score']) || ! is_numeric($result['match_score'])) {
            throw new InvalidArgumentException('Matching result requires a numeric match_score.');
        }

        $score = max(0, min(100, (float) $result['match_score']));

        $opportunity->forceFill([
            'match_score' => $score,
            'match_analysis' => $result,
            'gaps' => $result['gaps'] ?? $result['requirements_unmet'] ?? [],
            'action_plan' => $result['action_plan'] ?? [],
            'status' => $score >= 70 ? 'qualified' : 'identified',
            'qualified_at' => $score >= 70 ? now() : null,
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
