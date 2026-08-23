<?php

namespace App\Factory\Services;

use App\Models\FactoryOpportunitySource;

class FactoryOpportunitySourceReadinessService
{
    public function evaluate(FactoryOpportunitySource $source): array
    {
        $checks = [
            'connector_defined' => filled($source->connector_type),
            'mapping_contract_defined' => ! empty($source->mapping_contract),
            'supported_profiles_defined' => ! empty($source->supported_profile_types),
            'supported_opportunity_types_defined' => ! empty($source->supported_opportunity_types),
            'sync_status_success' => in_array($source->last_sync_status, ['success', 'healthy'], true),
            'sync_evidence_present' => ! empty($source->last_sync_evidence),
            'last_sync_present' => $source->last_sync_at !== null,
        ];

        return [
            'ready' => ! in_array(false, $checks, true),
            'checks' => $checks,
            'blockers' => collect($checks)
                ->filter(fn (bool $passed) => ! $passed)
                ->keys()
                ->values()
                ->all(),
        ];
    }
}
