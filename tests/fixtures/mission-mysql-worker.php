<?php
declare(strict_types=1);
require __DIR__.'/mission-test-database.php';
require dirname(__DIR__, 2).'/factory-core/app/Services/Governance/MissionHandoffRepository.php';
use App\Services\Governance\MissionHandoffRepository;
try {
    if (getenv('FACTORY_GOVERNANCE_TEST_DRIVER') !== 'mariadb') throw new RuntimeException('MariaDB CI only');
    $request = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    $db = missionTestConnection();
    echo "READY\n"; fflush(STDOUT);
    $repo = new MissionHandoffRepository($db);
    $result = $repo->recordAssessment(1, $request['scope'], $request['command']);
    echo json_encode(['ok'=>true, 'result'=>$result], JSON_THROW_ON_ERROR)."\n";
} catch (Throwable $error) {
    echo json_encode(['ok'=>false, 'error'=>$error->getMessage()], JSON_THROW_ON_ERROR)."\n";
    exit(1);
}
