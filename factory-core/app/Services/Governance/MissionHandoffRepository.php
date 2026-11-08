<?php
declare(strict_types=1);

namespace App\Services\Governance;

use PDO;
use RuntimeException;
use Throwable;

/** Internal OBSERVER adapter over FactoryMission's existing table/mission_dna. */
final class MissionHandoffRepository
{
    public function __construct(private PDO $db)
    {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (!in_array($this->driver(), ['mysql', 'sqlite'], true)) {
            throw new RuntimeException('Unsupported mission repository driver');
        }
    }

    private function driver(): string
    {
        return $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    private function transaction(callable $action): array
    {
        if ($this->db->inTransaction()) throw new RuntimeException('Repository requires its own transaction');
        $sqlite = $this->driver() === 'sqlite';
        $sqlite ? $this->db->exec('BEGIN IMMEDIATE') : $this->db->beginTransaction();
        try {
            $result = $action();
            $sqlite ? $this->db->exec('COMMIT') : $this->db->commit();
            return $result;
        } catch (Throwable $error) {
            $sqlite ? $this->db->exec('ROLLBACK') : $this->db->rollBack();
            throw $error;
        }
    }

    private function row(int $id, array $scope, bool $lock = false): array
    {
        if ($id < 1 || !is_int($scope['productId'] ?? null) || $scope['productId'] < 1
            || !is_string($scope['tenantId'] ?? null) || trim($scope['tenantId']) === ''
            || !is_string($scope['projectId'] ?? null) || trim($scope['projectId']) === '') {
            throw new RuntimeException('Trusted mission scope required');
        }
        $sql = 'SELECT id, product_id, status, mission_dna FROM factory_missions WHERE id = ? AND product_id = ?';
        if ($lock && $this->driver() === 'mysql') $sql .= ' FOR UPDATE';
        $query = $this->db->prepare($sql);
        $query->execute([$id, $scope['productId']]);
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new RuntimeException('Mission unavailable in authorized scope');
        $dna = json_decode((string) $row['mission_dna'], true, 512, JSON_THROW_ON_ERROR);
        $g = $dna['handoff_supervision'] ?? null;
        if (!is_array($g) || ($g['schemaVersion'] ?? null) !== '1.0'
            || ($g['scope']['tenantId'] ?? null) !== $scope['tenantId']
            || ($g['scope']['projectId'] ?? null) !== $scope['projectId']
            || !is_int($g['revision'] ?? null) || $g['revision'] < 0
            || ($g['status'] ?? null) !== $row['status']
            || !is_array($g['task'] ?? null)
            || !is_array($g['audit'] ?? null) || !array_is_list($g['audit'])
            || !is_array($g['outbox'] ?? null) || !array_is_list($g['outbox'])
            || !is_array($g['receipts'] ?? null)) {
            throw new RuntimeException('Canonical supervision context unavailable or inconsistent');
        }
        $task = $g['task'];
        foreach (['id', 'repository', 'resultSha', 'gate'] as $field) {
            if (!is_string($task[$field] ?? null) || trim($task[$field]) === '') {
                throw new RuntimeException('Canonical task identity invalid');
            }
        }
        if (!preg_match('/^[a-f0-9]{40}$/', $task['resultSha']) || !preg_match('/^G[0-6]$/', $task['gate'])
            || !is_int($task['attempts'] ?? null) || $task['attempts'] < 0
            || !is_int($task['maxAttempts'] ?? null) || $task['maxAttempts'] < 1
            || !is_bool($task['budgetExceeded'] ?? null)) {
            throw new RuntimeException('Canonical task limits or commit invalid');
        }
        $row['dna'] = $dna;
        return $row;
    }

    private function view(array $row): array
    {
        $g = $row['dna']['handoff_supervision'];
        return ['missionId' => (int) $row['id'], 'revision' => $g['revision'],
            'status' => $g['status'], 'task' => $g['task'], 'scope' => $g['scope'],
            'canAdvance' => false, 'dispatchAllowed' => false, 'productionAllowed' => false];
    }

    public function load(int $id, array $scope): array
    {
        return $this->view($this->row($id, $scope));
    }

    private function save(array $row, array $g): array
    {
        if (count($g['audit']) > 1000 || count($g['receipts']) > 1000) {
            throw new RuntimeException('Supervision history capacity reached; escalate');
        }
        $row['dna']['handoff_supervision'] = $g;
        $query = $this->db->prepare('UPDATE factory_missions SET status = ?, mission_dna = ?, updated_at = ? WHERE id = ? AND product_id = ?');
        $query->execute([$g['status'], json_encode($row['dna'], JSON_THROW_ON_ERROR), gmdate('Y-m-d H:i:s'), $row['id'], $row['product_id']]);
        return $this->view($row);
    }

    /** plan must be computed by the trusted VIA service, never submitted by HTTP clients. */
    public function recordAssessment(int $id, array $scope, array $command): array
    {
        return $this->transaction(function () use ($id, $scope, $command): array {
            $row = $this->row($id, $scope, true);
            $g = $row['dna']['handoff_supervision'];
            $task = $g['task'];
            $plan = $command['plan'] ?? [];
            if (!in_array($plan['state'] ?? null, ['needs_correction', 'blocked', 'escalated', 'pending_supervision'], true)
                || ($plan['canAdvance'] ?? null) !== false || ($plan['dispatchAllowed'] ?? null) !== false
                || ($plan['productionAllowed'] ?? null) !== false
                || ($plan['taskId'] ?? null) !== ($task['id'] ?? null)
                || ($plan['resultSha'] ?? null) !== ($task['resultSha'] ?? null)
                || ($plan['failedGate'] ?? null) !== ($task['gate'] ?? null)
                || !is_array($plan['reasons'] ?? null) || !array_is_list($plan['reasons'])
                || $plan['reasons'] === [] || count($plan['reasons']) > 50) {
                throw new RuntimeException('Invalid OBSERVER correction plan');
            }
            foreach ($plan['reasons'] as $reason) {
                if (!is_string($reason) || trim($reason) === '' || strlen($reason) > 1000) throw new RuntimeException('Invalid correction reason');
            }
            $owner = $plan['state'] === 'needs_correction' ? ($task['correctionOwner'] ?? null) : null;
            if (($plan['correctionOwner'] ?? null) !== $owner
                || ($plan['state'] === 'needs_correction' && (!is_string($owner) || trim($owner) === ''))) {
                throw new RuntimeException('Correction owner differs from canonical task');
            }
            $key = hash('sha256', json_encode([$id, $scope['tenantId'], $scope['projectId'],
                $task['id'], $task['repository'], $task['resultSha'], $task['gate'], $task['attempts']], JSON_THROW_ON_ERROR));
            $digest = hash('sha256', json_encode([$plan['state'], $owner, array_values($plan['reasons'])], JSON_THROW_ON_ERROR));
            if (isset($g['receipts'][$key])) {
                if ($g['receipts'][$key]['digest'] !== $digest) throw new RuntimeException('Conflicting assessment for same attempt');
                return [...$this->view($row), 'applied' => false, 'replayed' => true, 'eventId' => $key];
            }
            if (($command['expectedRevision'] ?? null) !== $g['revision']) throw new RuntimeException('Stale mission revision');
            if (!in_array($g['status'], ['ready_for_via', 'awaiting_evaluation'], true)) throw new RuntimeException('Mission not awaiting evaluation');
            $g['revision']++;
            $g['status'] = $plan['state'];
            $event = ['id' => $key, 'revision' => $g['revision'], 'type' => 'handoff_assessed',
                'taskId' => $task['id'], 'resultSha' => $task['resultSha'], 'gate' => $task['gate'],
                'state' => $plan['state'], 'correctionOwner' => $owner, 'reasons' => array_values($plan['reasons']),
                'createdAt' => gmdate('c'), 'dispatchAllowed' => false];
            $g['audit'][] = $event;
            // Passive outbox only. There is no worker capable of executing this event.
            if ($plan['state'] === 'needs_correction') $g['outbox'][] = [...$event, 'type' => 'correction_required'];
            $g['receipts'][$key] = ['digest' => $digest];
            return [...$this->save($row, $g), 'applied' => true, 'replayed' => false, 'eventId' => $key];
        });
    }

    /** Records a submitted correction; it neither executes code nor approves the new SHA. */
    public function recordCorrection(int $id, array $scope, array $command): array
    {
        return $this->transaction(function () use ($id, $scope, $command): array {
            $row = $this->row($id, $scope, true);
            $g = $row['dna']['handoff_supervision'];
            $task = $g['task'];
            $sha = $command['resultSha'] ?? null;
            if (($command['expectedRevision'] ?? null) !== $g['revision'] || $g['status'] !== 'needs_correction'
                || ($command['correctedBy'] ?? null) !== ($task['correctionOwner'] ?? null)
                || !is_string($sha) || !preg_match('/^[a-f0-9]{40}$/', $sha) || $sha === $task['resultSha']
                || !is_int($task['attempts'] ?? null) || !is_int($task['maxAttempts'] ?? null)
                || $task['attempts'] >= $task['maxAttempts'] || ($task['budgetExceeded'] ?? null) !== false) {
                throw new RuntimeException('Correction rejected by scope, owner, SHA, revision or limits');
            }
            foreach ($g['audit'] as $event) {
                if (($event['resultSha'] ?? null) === $sha) throw new RuntimeException('Historical commit cannot be resubmitted');
            }
            $g['revision']++;
            $g['status'] = 'awaiting_evaluation';
            $g['task']['resultSha'] = $sha;
            $g['task']['attempts']++;
            $g['audit'][] = ['type' => 'correction_submitted', 'revision' => $g['revision'],
                'resultSha' => $sha, 'previousSha' => $task['resultSha'], 'correctedBy' => $command['correctedBy'],
                'createdAt' => gmdate('c'), 'canAdvance' => false];
            return $this->save($row, $g);
        });
    }

    public function correctionCandidates(int $id, array $scope): array
    {
        $row = $this->row($id, $scope);
        $g = $row['dna']['handoff_supervision'];
        if ($g['status'] !== 'needs_correction') return [];
        return array_values(array_filter($g['outbox'], fn (array $event): bool =>
            $event['revision'] === $g['revision'] && $event['resultSha'] === $g['task']['resultSha']
            && $event['correctionOwner'] === $g['task']['correctionOwner']));
    }

    public function inspectNextAgent(int $id, array $scope): array
    {
        $current = $this->load($id, $scope);
        return ['allowed' => false, 'missionId' => $id, 'revision' => $current['revision'],
            'state' => $current['status'], 'reason' => 'OBSERVER never dispatches the next agent'];
    }
}
