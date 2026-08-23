<?php

namespace App\Factory\Services;

use App\Models\FactoryOpportunity;
use App\Models\FactoryOpportunityAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FactoryOpportunityActionEngine
{
    public const CONTRACT = 'factory.opportunity.action.v1';

    public function buildActionRequest(FactoryOpportunity $opportunity): array
    {
        return [
            'contract' => self::CONTRACT,
            'objective' => 'Transform opportunity gaps, unmet requirements and the current action plan into concrete, trackable actions.',
            'opportunity' => [
                'id' => $opportunity->id,
                'title' => $opportunity->title,
                'profile_type' => $opportunity->profile_type,
                'opportunity_type' => $opportunity->opportunity_type,
                'deadline_at' => optional($opportunity->deadline_at)->toIso8601String(),
                'match_score' => $opportunity->match_score,
                'match_level' => data_get($opportunity->match_analysis, 'match_level'),
                'hard_blockers' => data_get($opportunity->match_analysis, 'hard_blockers', []),
                'requirements_unmet' => data_get($opportunity->match_analysis, 'requirements_unmet', []),
                'gaps' => $opportunity->gaps ?? [],
                'risks' => data_get($opportunity->match_analysis, 'risks', []),
                'action_plan' => $opportunity->action_plan ?? [],
            ],
            'required_output' => [
                'actions' => [
                    '<action>' => [
                        'title',
                        'description',
                        'type',
                        'priority',
                        'owner_type',
                        'owner',
                        'due_at',
                        'dependencies',
                        'required_evidence',
                        'source_gap',
                        'blocking',
                    ],
                ],
            ],
            'rules' => [
                'do_not_mark_actions_completed' => true,
                'completion_requires_evidence' => true,
                'deadline_must_respect_opportunity_deadline' => true,
                'hard_blockers_generate_critical_actions_when_resolvable' => true,
                'do_not_invent_certificates_documents_or_approvals' => true,
            ],
        ];
    }

    public function materialize(FactoryOpportunity $opportunity, array $plan): array
    {
        $actions = Arr::get($plan, 'actions', []);
        $created = [];

        foreach ($actions as $index => $data) {
            if (! is_array($data) || empty($data['title'])) {
                continue;
            }

            $fingerprint = sha1(implode('|', [
                $opportunity->id,
                Str::lower(trim($data['title'])),
                Str::lower(trim((string) ($data['source_gap'] ?? ''))),
            ]));

            $action = FactoryOpportunityAction::updateOrCreate(
                [
                    'opportunity_id' => $opportunity->id,
                    'action_dna->fingerprint' => $fingerprint,
                ],
                [
                    'type' => $data['type'] ?? 'task',
                    'status' => 'pending',
                    'priority' => $this->priority($data, $opportunity),
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'owner_type' => $data['owner_type'] ?? 'human',
                    'owner' => $data['owner'] ?? null,
                    'due_at' => $this->boundedDueAt($data['due_at'] ?? null, $opportunity),
                    'dependencies' => $data['dependencies'] ?? [],
                    'required_evidence' => $data['required_evidence'] ?? [],
                    'action_dna' => [
                        'fingerprint' => $fingerprint,
                        'source_gap' => $data['source_gap'] ?? null,
                        'blocking' => (bool) ($data['blocking'] ?? false),
                        'generated_from' => self::CONTRACT,
                        'order' => $index + 1,
                    ],
                ]
            );

            $created[] = $action;
        }

        if ($created !== [] && $opportunity->status === 'qualified') {
            $opportunity->forceFill(['status' => 'preparing'])->save();
        }

        return $created;
    }

    public function canComplete(FactoryOpportunityAction $action): array
    {
        $required = $action->required_evidence ?? [];
        $provided = $action->completion_evidence ?? [];

        if ($required === []) {
            return ['ready' => true, 'missing' => []];
        }

        $missing = [];
        foreach ($required as $item) {
            $key = is_array($item) ? ($item['key'] ?? $item['type'] ?? null) : (string) $item;
            if (! $key) {
                continue;
            }

            $found = collect($provided)->contains(function ($evidence) use ($key) {
                if (is_array($evidence)) {
                    return ($evidence['key'] ?? $evidence['type'] ?? null) === $key;
                }

                return (string) $evidence === $key;
            });

            if (! $found) {
                $missing[] = $key;
            }
        }

        return ['ready' => $missing === [], 'missing' => $missing];
    }

    public function complete(FactoryOpportunityAction $action, array $evidence): FactoryOpportunityAction
    {
        $action->forceFill(['completion_evidence' => $evidence])->save();
        $readiness = $this->canComplete($action->refresh());

        if (! $readiness['ready']) {
            return $action->refresh();
        }

        $action->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();

        return $action->refresh();
    }

    private function priority(array $data, FactoryOpportunity $opportunity): string
    {
        if (($data['blocking'] ?? false) === true) {
            return 'critical';
        }

        return $data['priority'] ?? (($opportunity->deadline_at && $opportunity->deadline_at->diffInDays(now()) <= 7) ? 'high' : 'normal');
    }

    private function boundedDueAt(?string $dueAt, FactoryOpportunity $opportunity): ?string
    {
        if (! $dueAt) {
            return null;
        }

        $candidate = \Carbon\Carbon::parse($dueAt);
        if ($opportunity->deadline_at && $candidate->greaterThan($opportunity->deadline_at)) {
            return $opportunity->deadline_at->toDateTimeString();
        }

        return $candidate->toDateTimeString();
    }
}
