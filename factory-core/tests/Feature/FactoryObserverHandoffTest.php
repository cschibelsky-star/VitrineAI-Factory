<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FactoryMission;
use App\Models\FactoryProduct;
use App\Services\FactoryMissionRunner;
use App\Services\FactoryMissionStepExecutor;
use App\Services\Governance\MissionHandoffRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

// Real migrations/Eloquent/PDO, without an outer RefreshDatabase transaction.
// Isolated processes preserve the legacy suite's in-memory DB lifecycle.
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class FactoryObserverHandoffTest extends TestCase
{
    use DatabaseMigrations;

    private function record(string $state = 'ready_for_via'): array
    {
        $product = FactoryProduct::create(['name'=>'Observer test', 'slug'=>'observer-test-'.FactoryProduct::count()]);
        $scope = ['productId'=>$product->id, 'tenantId'=>'tenant-a', 'projectId'=>'marketing'];
        $g = ['schemaVersion'=>'1.0', 'scope'=>['tenantId'=>'tenant-a','projectId'=>'marketing'],
            'revision'=>0, 'status'=>$state, 'task'=>['id'=>'MK-1','repository'=>'org/marketing',
                'resultSha'=>str_repeat('b',40),'gate'=>'G3','correctionOwner'=>'engineering-owner',
                'attempts'=>0,'maxAttempts'=>3,'budgetExceeded'=>false],
            'audit'=>[], 'outbox'=>[], 'receipts'=>[]];
        $mission = FactoryMission::create(['title'=>'Observer engineering task','status'=>$state,
            'product_id'=>$product->id,'mission_dna'=>['existing_dna'=>'preserved','handoff_supervision'=>$g]]);
        return [$mission, $scope, new MissionHandoffRepository(DB::connection()->getPdo())];
    }

    private function rejection(): array
    {
        return ['expectedRevision'=>0,'plan'=>['state'=>'needs_correction','correctionOwner'=>'engineering-owner',
            'taskId'=>'MK-1','resultSha'=>str_repeat('b',40),'failedGate'=>'G3','reasons'=>['required test failed'],
            'canAdvance'=>false,'dispatchAllowed'=>false,'productionAllowed'=>false]];
    }

    public function test_pdo_adapter_updates_the_actual_eloquent_mission_and_preserves_dna(): void
    {
        [$mission,$scope,$repo]=$this->record();
        $first=$repo->recordAssessment($mission->id,$scope,$this->rejection());
        $retry=$repo->recordAssessment($mission->id,$scope,$this->rejection());
        $fresh=$mission->fresh();$g=$fresh->mission_dna['handoff_supervision'];
        $this->assertSame('needs_correction',$fresh->status);
        $this->assertSame('preserved',$fresh->mission_dna['existing_dna']);
        $this->assertSame($scope['productId'],$fresh->product->id);
        $this->assertSame(1,$g['revision']);
        $this->assertCount(1,$g['audit']);$this->assertCount(1,$g['outbox']);
        $this->assertTrue($first['applied']);$this->assertTrue($retry['replayed']);
        $this->assertFalse($repo->inspectNextAgent($mission->id,$scope)['allowed']);
    }

    public function test_legacy_runner_cannot_bypass_any_observer_state(): void
    {
        Storage::fake('local');
        foreach(['ready_for_via','needs_correction','pending_supervision','escalated','awaiting_evaluation'] as $state) {
            [$mission,$scope,$repo]=$this->record($state);
            $step=$mission->steps()->create(['name'=>'Must not execute','status'=>'pending','order_column'=>1,
                'payload'=>['action'=>FactoryMissionStepExecutor::INTERNAL_SCAFFOLD]]);
            try { app(FactoryMissionRunner::class)->run($mission);$this->fail('Runner bypassed OBSERVER'); }
            catch(\RuntimeException $error) { $this->assertSame('observer_handoff_requires_independent_authorization',$error->getMessage()); }
            $this->assertSame($state,$mission->fresh()->status);
            $this->assertSame('pending',$step->fresh()->status);
            $this->assertSame(0,$mission->logs()->count());
            $this->assertSame([],Storage::disk('local')->allFiles());
            $this->assertFalse($repo->inspectNextAgent($mission->id,$scope)['allowed']);
        }
    }

    public function test_legacy_correction_route_cannot_reopen_an_observer_mission(): void
    {
        [$mission,$scope,$repo]=$this->record('needs_correction');
        $step=$mission->steps()->create(['name'=>'Rejected','status'=>'rework_required','order_column'=>1,
            'result'=>['ok'=>false,'attempt'=>1],'payload'=>['action'=>FactoryMissionStepExecutor::INTERNAL_SCAFFOLD]]);
        try { app(FactoryMissionRunner::class)->requestCorrection($step,'Cannot bypass governance',1);$this->fail('Correction bypassed OBSERVER'); }
        catch(\RuntimeException $error) { $this->assertSame('observer_handoff_requires_independent_authorization',$error->getMessage()); }
        $this->assertSame('rework_required',$step->fresh()->status);
        $this->assertSame('needs_correction',$mission->fresh()->status);
        $this->assertSame(0,$mission->logs()->count());
    }

    public function test_new_sha_invalidates_the_candidate_and_does_not_release_the_runner(): void
    {
        [$mission,$scope,$repo]=$this->record();
        $repo->recordAssessment($mission->id,$scope,$this->rejection());
        $repo->recordCorrection($mission->id,$scope,['expectedRevision'=>1,'correctedBy'=>'engineering-owner','resultSha'=>str_repeat('c',40)]);
        $this->assertSame('awaiting_evaluation',$mission->fresh()->status);
        $this->assertSame([],$repo->correctionCandidates($mission->id,$scope));
        $this->assertFalse($repo->inspectNextAgent($mission->id,$scope)['allowed']);
        $this->expectExceptionMessage('observer_handoff_requires_independent_authorization');
        app(FactoryMissionRunner::class)->run($mission);
    }

    public function test_malformed_supervision_marker_does_not_enable_legacy_execution(): void
    {
        [$mission]=$this->record();$mission->update(['mission_dna'=>['handoff_supervision'=>null]]);
        $this->expectExceptionMessage('observer_handoff_requires_independent_authorization');
        app(FactoryMissionRunner::class)->run($mission);
    }
}
