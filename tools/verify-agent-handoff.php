<?php
/**
 * Offline validation of developer-to-VIA handoff metadata.
 * Does not authenticate evidence, inspect Git or authorize release.
 */
declare(strict_types=1);

if ($argc !== 2 || !is_file($argv[1])) {
    fwrite(STDERR, "Usage: php tools/verify-agent-handoff.php <handoff.json>\n");
    exit(2);
}
$data = json_decode((string) file_get_contents($argv[1]), true);
if (!is_array($data) || array_is_list($data) || json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "FAIL: invalid JSON object\n");
    exit(2);
}
$errors = [];
$requiredText = static function (array $item, string $field, string $where = '') use (&$errors): void {
    if (!isset($item[$field]) || !is_string($item[$field]) || trim($item[$field]) === '') {
        $errors[] = "missing or empty {$where}{$field}";
    }
};
foreach (['task_id', 'repository', 'branch', 'agent_id', 'specialty', 'objective', 'next_owner'] as $field) {
    $requiredText($data, $field);
}
if (($data['schema_version'] ?? null) !== '1.0') $errors[] = 'unsupported schema_version';
if (in_array($data['branch'] ?? '', ['main', 'master', 'production', 'prod'], true)) $errors[] = 'target branch must be isolated';
if (($data['state'] ?? null) !== 'ready_for_via') $errors[] = 'state must be ready_for_via';
if (!is_array($data['authorization'] ?? null) ||
    ($data['authorization']['approved_for_branch_write'] ?? null) !== true ||
    ($data['authorization']['production_approved'] ?? null) !== false) {
    $errors[] = 'authorization must allow branch write but not production';
} else {
    $requiredText($data['authorization'], 'scope', 'authorization.');
}
foreach (['base_sha', 'result_sha'] as $field) {
    if (!is_string($data[$field] ?? null) || !preg_match('/^[a-f0-9]{40}$/i', $data[$field])) {
        $errors[] = "invalid {$field}";
    }
}
if (isset($data['base_sha'], $data['result_sha']) && $data['base_sha'] === $data['result_sha']) {
    $errors[] = 'result_sha matches base_sha';
}
if (!is_array($data['changed_files'] ?? null) || count($data['changed_files']) === 0) {
    $errors[] = 'changed_files must be nonempty';
} else {
    foreach ($data['changed_files'] as $f) {
        if (!is_string($f) || trim($f) === '') $errors[] = 'invalid changed_files entry';
    }
}
if (!is_array($data['checks'] ?? null) || count($data['checks']) === 0) {
    $errors[] = 'checks must be nonempty';
} else {
    $requiredCount = 0;
    foreach ($data['checks'] as $i => $check) {
        if (!is_array($check)) { $errors[] = "check {$i} invalid"; continue; }
        $requiredText($check, 'id', "check {$i}.");
        if (!is_bool($check['required'] ?? null)) $errors[] = "check {$i}.required must be boolean";
        if (!in_array($check['status'] ?? null, ['passed', 'failed', 'not_run'], true)) {
            $errors[] = "check {$i}.status invalid";
        }
        if (($check['required'] ?? false) === true) {
            $requiredCount++;
            if (($check['status'] ?? null) !== 'passed') $errors[] = "required check {$i} not passed";
            $requiredText($check, 'command', "check {$i}.");
            $requiredText($check, 'evidence', "check {$i}.");
        }
    }
    if ($requiredCount === 0) $errors[] = 'at least one required check needed';
}
if (!is_array($data['review'] ?? null) || ($data['review']['status'] ?? null) !== 'approved') {
    $errors[] = 'independent review must be approved';
} else {
    $requiredText($data['review'], 'reviewer_agent_id', 'review.');
    $requiredText($data['review'], 'evidence', 'review.');
    if (($data['review']['reviewer_agent_id'] ?? null) === ($data['agent_id'] ?? null)) {
        $errors[] = 'reviewer must differ from developer agent';
    }
}
if (!isset($data['risks']) || !is_array($data['risks'])) $errors[] = 'risks must be array';
$result = [
    'decision' => $errors === [] ? 'pending_external_verification' : 'blocked',
    'can_advance' => false,
    'evidence_verified' => false,
    'failures' => $errors,
];
echo json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
// Deliberately nonzero: valid metadata is not permission to execute another agent.
exit($errors === [] ? 3 : 1);
