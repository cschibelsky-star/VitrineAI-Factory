<?php
declare(strict_types=1);

// CI transport only: SQLite temporary files, no production DSN or HTTP endpoint.
require dirname(__DIR__, 2).'/factory-core/app/Services/Governance/MissionHandoffRepository.php';
use App\Services\Governance\MissionHandoffRepository;

try {
    $path = $argv[1] ?? '';
    if (!str_starts_with($path, sys_get_temp_dir().'/via-handoff-') || !str_ends_with($path, '.sqlite')) {
        throw new RuntimeException('Temporary test database required');
    }
    $request = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    $db = new PDO('sqlite:'.$path);
    $db->exec('PRAGMA busy_timeout = 5000');
    $operation = $request['operation'];
    if ($operation === 'seed') {
        $db->exec('CREATE TABLE factory_missions (id INTEGER PRIMARY KEY, product_id INTEGER NOT NULL, status TEXT NOT NULL, mission_dna TEXT NOT NULL, updated_at TEXT)');
        $g = ['schemaVersion' => '1.0', 'revision' => 0, 'status' => 'ready_for_via',
            'scope' => ['tenantId' => 'tenant-a', 'projectId' => 'marketing'],
            'task' => $request['task'], 'receipts' => [], 'audit' => [], 'outbox' => []];
        $q = $db->prepare('INSERT INTO factory_missions VALUES (1, 1, ?, ?, NULL)');
        $q->execute(['ready_for_via', json_encode(['existing_dna' => 'preserve', 'handoff_supervision' => $g], JSON_THROW_ON_ERROR)]);
        $result = ['seeded' => true];
    } elseif ($operation === 'dump') {
        $result = json_decode($db->query('SELECT mission_dna FROM factory_missions WHERE id=1')->fetchColumn(), true, 512, JSON_THROW_ON_ERROR);
    } else {
        $repo = new MissionHandoffRepository($db);
        $id = $request['missionId'];
        $scope = $request['scope'];
        $result = match ($operation) {
            'load' => $repo->load($id, $scope),
            'recordAssessment' => $repo->recordAssessment($id, $scope, $request['command']),
            'recordCorrection' => $repo->recordCorrection($id, $scope, $request['command']),
            'correctionCandidates' => $repo->correctionCandidates($id, $scope),
            'inspectNextAgent' => $repo->inspectNextAgent($id, $scope),
            default => throw new RuntimeException('Unsupported test operation'),
        };
    }
    echo json_encode(['ok' => true, 'result' => $result], JSON_THROW_ON_ERROR)."\n";
} catch (Throwable $error) {
    fwrite(STDERR, json_encode(['ok' => false, 'error' => $error->getMessage()], JSON_THROW_ON_ERROR)."\n");
    exit(1);
}
