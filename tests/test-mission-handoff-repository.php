<?php
declare(strict_types=1);
require dirname(__DIR__).'/factory-core/app/Services/Governance/MissionHandoffRepository.php';
use App\Services\Governance\MissionHandoffRepository;

function check(bool $ok, string $why = 'assertion failed'): void {
    if (!$ok) throw new RuntimeException($why);
}
function rejects(callable $fn): void {
    try { $fn(); } catch (Throwable) { return; }
    throw new RuntimeException('expected rejection');
}
function setup(): array {
    $db = new PDO('sqlite::memory:');
    $db->exec('CREATE TABLE factory_missions (id INTEGER PRIMARY KEY, product_id INTEGER, status TEXT, mission_dna TEXT, updated_at TEXT)');
    $scope = ['productId'=>1, 'tenantId'=>'a', 'projectId'=>'marketing'];
    $task = ['id'=>'T-1', 'repository'=>'org/repo', 'resultSha'=>str_repeat('b',40), 'gate'=>'G3',
        'correctionOwner'=>'developer', 'attempts'=>0, 'maxAttempts'=>2, 'budgetExceeded'=>false];
    $g = ['schemaVersion'=>'1.0', 'scope'=>['tenantId'=>'a','projectId'=>'marketing'], 'revision'=>0,
        'status'=>'ready_for_via','task'=>$task, 'audit'=>[], 'outbox'=>[], 'receipts'=>[]];
    $q = $db->prepare('INSERT INTO factory_missions VALUES (1, 1, ?, ?, NULL)');
    $q->execute(['ready_for_via',json_encode(['legacy'=>'preserved', 'handoff_supervision'=>$g],JSON_THROW_ON_ERROR)]);
    $plan = ['state'=>'needs_correction', 'correctionOwner'=>'developer', 'taskId'=>'T-1',
        'resultSha'=>str_repeat('b',40), 'failedGate'=>'G3', 'reasons'=>['required test failed'],
        'canAdvance'=>false, 'dispatchAllowed'=>false, 'productionAllowed'=>false];
    return [$db, new MissionHandoffRepository($db), $scope, ['expectedRevision'=>0, 'plan'=>$plan]];
}
function dna(PDO $db): array {
    return json_decode($db->query('SELECT mission_dna FROM factory_missions')->fetchColumn(),true,512,JSON_THROW_ON_ERROR);
}
$cases = [
    'persists owner, state, audit and passive outbox atomically' => function($db,$repo,$scope,$cmd) {
        $r=$repo->recordAssessment(1,$scope,$cmd);$g=dna($db)['handoff_supervision'];
        check($r['status']==='needs_correction' && $r['revision']===1 && $r['applied']);
        check(count($g['audit'])===1 && count($g['outbox'])===1 && count($g['receipts'])===1);
        check($g['outbox'][0]['correctionOwner']==='developer' && !$g['outbox'][0]['dispatchAllowed']);
        check(dna($db)['legacy']==='preserved');
    },
    'identical retry does not duplicate correction' => function($db,$repo,$scope,$cmd) {
        $a=$repo->recordAssessment(1,$scope,$cmd);$b=$repo->recordAssessment(1,$scope,$cmd);
        check($a['applied'] && !$b['applied'] && $b['replayed'] && $a['eventId']===$b['eventId']);
        check(count(dna($db)['handoff_supervision']['outbox'])===1);
    },
    'conflicting verdict for same attempt is rejected' => function($db,$repo,$scope,$cmd) {
        $repo->recordAssessment(1,$scope,$cmd);$cmd['plan']['reasons']=['different verdict'];
        rejects(fn()=>$repo->recordAssessment(1,$scope,$cmd));
        check(count(dna($db)['handoff_supervision']['audit'])===1);
    },
    'stale revision cannot write' => function($db,$repo,$scope,$cmd) {
        $cmd['expectedRevision']=1;rejects(fn()=>$repo->recordAssessment(1,$scope,$cmd));
        check(dna($db)['handoff_supervision']['revision']===0);
    },
    'another tenant cannot read or write' => function($db,$repo,$scope,$cmd) {
        $scope['tenantId']='other';rejects(fn()=>$repo->load(1,$scope));rejects(fn()=>$repo->recordAssessment(1,$scope,$cmd));
    },
    'another project cannot read' => function($db,$repo,$scope,$cmd) {
        $scope['projectId']='other';rejects(fn()=>$repo->load(1,$scope));
    },
    'another product cannot read' => function($db,$repo,$scope,$cmd) {
        $scope['productId']=2;rejects(fn()=>$repo->load(1,$scope));
    },
    'permissive plan cannot write' => function($db,$repo,$scope,$cmd) {
        foreach(['canAdvance','dispatchAllowed','productionAllowed'] as $flag) {
            $bad=$cmd;$bad['plan'][$flag]=true;rejects(fn()=>$repo->recordAssessment(1,$scope,$bad));
        }
    },
    'wrong owner cannot receive correction' => function($db,$repo,$scope,$cmd) {
        $cmd['plan']['correctionOwner']='devops';rejects(fn()=>$repo->recordAssessment(1,$scope,$cmd));
    },
    'old SHA cannot write' => function($db,$repo,$scope,$cmd) {
        $cmd['plan']['resultSha']=str_repeat('a',40);rejects(fn()=>$repo->recordAssessment(1,$scope,$cmd));
    },
    'failed update rolls back audit and outbox' => function($db,$repo,$scope,$cmd) {
        $db->exec("CREATE TRIGGER deny_write BEFORE UPDATE ON factory_missions BEGIN SELECT RAISE(ABORT, 'write denied'); END");
        rejects(fn()=>$repo->recordAssessment(1,$scope,$cmd));$g=dna($db)['handoff_supervision'];
        check($g['revision']===0 && $g['audit']===[] && $g['outbox']===[]);
    },
    'submission invalidates old correction candidate' => function($db,$repo,$scope,$cmd) {
        $repo->recordAssessment(1,$scope,$cmd);check(count($repo->correctionCandidates(1,$scope))===1);
        $r=$repo->recordCorrection(1,$scope,['expectedRevision'=>1,'correctedBy'=>'developer','resultSha'=>str_repeat('c',40)]);
        check($r['revision']===2 && $r['status']==='awaiting_evaluation' && $r['task']['attempts']===1);
        check($repo->correctionCandidates(1,$scope)===[] && !$repo->inspectNextAgent(1,$scope)['allowed']);
        rejects(fn()=>$repo->recordAssessment(1,$scope,$cmd));
    },
    'submission does not accept wrong owner or same SHA' => function($db,$repo,$scope,$cmd) {
        $repo->recordAssessment(1,$scope,$cmd);
        rejects(fn()=>$repo->recordCorrection(1,$scope,['expectedRevision'=>1,'correctedBy'=>'other','resultSha'=>str_repeat('c',40)]));
        rejects(fn()=>$repo->recordCorrection(1,$scope,['expectedRevision'=>1,'correctedBy'=>'developer','resultSha'=>str_repeat('b',40)]));
    },
    'pending supervision cannot dispatch or produce correction candidate' => function($db,$repo,$scope,$cmd) {
        $cmd['plan']['state']='pending_supervision';$cmd['plan']['correctionOwner']=null;
        $repo->recordAssessment(1,$scope,$cmd);check($repo->correctionCandidates(1,$scope)===[]);
        check(!$repo->inspectNextAgent(1,$scope)['allowed']);
    },
    'corrupt canonical task fails closed' => function($db,$repo,$scope,$cmd) {
        $data=dna($db);unset($data['handoff_supervision']['task']['attempts']);
        $db->prepare('UPDATE factory_missions SET mission_dna=?')->execute([json_encode($data)]);
        rejects(fn()=>$repo->load(1,$scope));
    },
    'historical commit cannot be submitted again' => function($db,$repo,$scope,$cmd) {
        $repo->recordAssessment(1,$scope,$cmd);
        $repo->recordCorrection(1,$scope,['expectedRevision'=>1,'correctedBy'=>'developer','resultSha'=>str_repeat('c',40)]);
        $cmd['expectedRevision']=2;$cmd['plan']['resultSha']=str_repeat('c',40);
        $repo->recordAssessment(1,$scope,$cmd);
        rejects(fn()=>$repo->recordCorrection(1,$scope,['expectedRevision'=>3,'correctedBy'=>'developer','resultSha'=>str_repeat('b',40)]));
    },
];
$failures=0;
foreach($cases as $name=>$run) {
    try { $run(...setup()); echo "PASS {$name}\n"; }
    catch(Throwable $error) { $failures++; echo "FAIL {$name}: {$error->getMessage()}\n"; }
}
exit($failures ? 1 : 0);
