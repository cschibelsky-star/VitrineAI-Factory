<?php
declare(strict_types=1);
$validator = dirname(__DIR__) . '/tools/verify-agent-handoff.php';
$valid = [
 'schema_version'=>'1.0','task_id'=>'TASK-1','repository'=>'org/repo','branch'=>'feature/gate',
 'agent_id'=>'builder','specialty'=>'developer','objective'=>'Test gate','next_owner'=>'via',
 'authorization'=>['scope'=>'feature branch','approved_for_branch_write'=>true,'production_approved'=>false],
 'base_sha'=>str_repeat('a',40),'result_sha'=>str_repeat('b',40),'changed_files'=>['src/test.php'],
 'checks'=>[['id'=>'php-lint','required'=>true,'status'=>'passed','command'=>'php -l src/test.php','evidence'=>'CI/job/1']],
 'review'=>['reviewer_agent_id'=>'qa','status'=>'approved','evidence'=>'PR/review/1'],
 'risks'=>[],'state'=>'ready_for_via'
];
$cases=[
 'valid metadata remains pending'=>[$valid,3],
 'missing evidence'=>[array_replace_recursive($valid,['checks'=>[['id'=>'lint','required'=>true,'status'=>'passed','evidence'=>'']]]),1],
 'failed required check'=>[array_replace_recursive($valid,['checks'=>[['id'=>'lint','required'=>true,'status'=>'failed']]]),1],
 'not executed check'=>[array_replace_recursive($valid,['checks'=>[['id'=>'lint','required'=>true,'status'=>'not_run']]]),1],
 'self review'=>[array_replace_recursive($valid,['review'=>['reviewer_agent_id'=>'builder']]),1],
 'production approval not allowed in gate'=>[array_replace_recursive($valid,['authorization'=>['production_approved'=>true]]),1],
 'same git sha'=>[array_replace($valid,['result_sha'=>str_repeat('a',40)]),1],
 'empty changes'=>[array_replace($valid,['changed_files'=>[]]),1],
 'review pending'=>[array_replace_recursive($valid,['review'=>['status'=>'pending']]),1],
 'no mandatory checks'=>[array_replace($valid,['checks'=>[['id'=>'info','required'=>false,'status'=>'passed','command'=>'x','evidence'=>'y']]]),1],
];
foreach (['main', 'master', 'production', 'prod'] as $branch) {
 $cases['protected branch '.$branch] = [array_replace($valid,['branch'=>$branch]),1];
}
$cases['needs correction cannot advance'] = [array_replace($valid,['state'=>'needs_correction']),1];
$cases['rejected review'] = [array_replace_recursive($valid,['review'=>['status'=>'rejected']]),1];
$failures=0;
foreach($cases as $label=>[$payload,$expected]) {
 $file=tempnam(sys_get_temp_dir(),'via-gate-');
 file_put_contents($file,json_encode($payload,JSON_THROW_ON_ERROR));
 $process=proc_open([PHP_BINARY,$validator,$file],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
 if (!is_resource($process)) {fwrite(STDERR,"FAIL: unable to start PHP\n");exit(2);}
 $out=stream_get_contents($pipes[1]); $err=stream_get_contents($pipes[2]);
 fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($process);unlink($file);
 $decision=json_decode($out,true);
 $safe=is_array($decision) && ($decision['can_advance'] ?? null)===false
   && ($decision['evidence_verified'] ?? null)===false
   && ($decision['decision'] ?? null)===($expected===3?'pending_external_verification':'blocked');
 if($code!==$expected || !$safe){$failures++;echo "FAIL {$label}: exit={$code}, expected={$expected}; {$out}{$err}\n";}
 else echo "PASS {$label}\n";
}
exit($failures?1:0);
