<?php
declare(strict_types=1);
define('FACTORY_GOVERNANCE_FIXTURES_ONLY', true);
require __DIR__.'/test-mission-handoff-repository.php';
if (getenv('FACTORY_GOVERNANCE_TEST_DRIVER') !== 'mariadb') {
    fwrite(STDERR, "MariaDB CI configuration required; this test must not skip\n");exit(2);
}

function concurrentAssessments(PDO $db, array $scope, array $commands): array
{
    $workers = [];
    $db->beginTransaction();
    $db->query('SELECT id FROM factory_missions WHERE id=1 FOR UPDATE')->fetchColumn();
    try {
        foreach ($commands as $command) {
            $process = proc_open([PHP_BINARY, __DIR__.'/fixtures/mission-mysql-worker.php'],
                [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
            if (!is_resource($process)) throw new RuntimeException('Unable to start worker');
            $workers[] = [$process,$pipes];
            fwrite($pipes[0], json_encode(['scope'=>$scope,'command'=>$command], JSON_THROW_ON_ERROR));
            fclose($pipes[0]);
        }
        foreach ($workers as [$process,$pipes]) {
            stream_set_timeout($pipes[1], 15);
            check(trim((string) fgets($pipes[1])) === 'READY', 'Worker failed before lock');
            check(proc_get_status($process)['running'], 'Worker bypassed parent row lock');
        }
        check(dna($db)['handoff_supervision']['revision']===0, 'Mutation before row lock release');
        $db->commit();
        $results = [];
        foreach ($workers as [$process,$pipes]) {
            $out = stream_get_contents($pipes[1]);$err = stream_get_contents($pipes[2]);
            fclose($pipes[1]);fclose($pipes[2]);proc_close($process);
            check($err === '', 'Unexpected worker stderr');
            $results[] = json_decode($out, true, 512, JSON_THROW_ON_ERROR);
        }
        $workers = [];
        return $results;
    } finally {
        if ($db->inTransaction()) $db->rollBack();
        foreach ($workers as [$process,$pipes]) {
            if (is_resource($process)) {
                if (proc_get_status($process)['running']) proc_terminate($process);
                foreach ($pipes as $pipe) if (is_resource($pipe)) fclose($pipe);
                proc_close($process);
            }
        }
    }
}

try {
    [$db,$repo,$scope,$command] = setup();
    $results = concurrentAssessments($db,$scope,[$command,$command]);
    check(count(array_filter($results,fn($r)=>$r['ok'] && $r['result']['applied']))===1);
    check(count(array_filter($results,fn($r)=>$r['ok'] && $r['result']['replayed']))===1);
    $g=dna($db)['handoff_supervision'];
    check(count($g['audit'])===1 && count($g['outbox'])===1 && count($g['receipts'])===1);
    check(!$repo->inspectNextAgent(1,$scope)['allowed']);
    echo "PASS InnoDB concurrent duplicate: one commit, one replay, one correction event\n";

    [$db,$repo,$scope,$command] = setup();
    $other=$command;$other['plan']['reasons']=['conflicting evaluation'];
    $results=concurrentAssessments($db,$scope,[$command,$other]);
    check(count(array_filter($results,fn($r)=>$r['ok']))===1);
    check(count(array_filter($results,fn($r)=>!$r['ok'] && str_contains($r['error'],'Conflicting assessment')))===1);
    $g=dna($db)['handoff_supervision'];check(count($g['audit'])===1 && count($g['outbox'])===1);
    echo "PASS InnoDB concurrent conflict: rejection without duplicate or overwrite\n";
} catch(Throwable $error) {
    fwrite(STDERR, "FAIL MariaDB concurrency: {$error->getMessage()}\n");exit(1);
}
