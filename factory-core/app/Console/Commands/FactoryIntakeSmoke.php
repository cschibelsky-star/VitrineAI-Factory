<?php

namespace App\Console\Commands;

use App\Factory\Services\FactoryAIOrchestrator;
use App\Factory\Services\FactoryOpportunityService;
use App\Models\FactoryIntake;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class FactoryIntakeSmoke extends Command
{
    protected $signature = 'factory:intake-smoke';
    protected $description = 'Valida Intake -> analise -> aprovacao -> materializacao em transacao reversivel.';

    public function handle(FactoryAIOrchestrator $orchestrator, FactoryOpportunityService $opportunities): int
    {
        DB::beginTransaction();

        try {
            $productIntake = new FactoryIntake();
            $productIntake->forceFill([
                'title' => 'Smoke E2E Produto',
                'request' => 'Validacao deterministica do pipeline da Factory.',
                'output_mode' => 'product',
                'origin' => 'new_idea',
                'type' => 'new_project',
                'priority' => 'normal',
                'analysis_status' => 'pending',
                'status' => 'new',
                'references' => [],
                'intake_dna' => ['smoke_test' => true],
            ])->save();

            $productAnalysis = [
                'profile_type' => 'generic',
                'profile_dna' => ['subject' => 'Factory smoke test', 'context' => 'Validacao interna reversivel', 'desired_state' => 'Pipeline materializado'],
                'master_prompt' => 'Executar somente o smoke test deterministico da Factory.',
                'analysis' => ['summary' => 'fixture local'],
                'reference_assessment' => [],
                'assumptions' => [],
                'open_decisions' => [],
                'project' => ['name' => 'Factory Smoke Product', 'slug' => 'factory-smoke-product', 'category' => 'diagnostic', 'description' => 'Produto temporario do smoke test.'],
                'blueprint' => ['name' => 'Factory Smoke Blueprint', 'slug' => 'factory-smoke-blueprint', 'category' => 'diagnostic', 'description' => 'Blueprint temporario do smoke test.'],
                'capabilities' => [[ 'name' => 'Factory Smoke Capability', 'slug' => 'factory-smoke-capability', 'category' => 'diagnostic', 'type' => 'business', 'description' => 'Capability temporaria do smoke test.' ]],
                'missions' => [[ 'title' => 'Factory Smoke Mission', 'priority' => 'normal', 'objective' => 'Confirmar materializacao reversivel.' ]],
            ];

            $orchestrator->applyAnalysis($productIntake, $productAnalysis);
            $productIntake->refresh();
            if ($productIntake->analysis_status !== 'ready') {
                throw new RuntimeException('Produto: analise nao chegou a ready.');
            }

            $productIntake->forceFill(['analysis_status' => 'approved', 'status' => 'approved'])->save();
            $product = $orchestrator->materializeApprovedAnalysis($productIntake->fresh(['product']));
            $productIntake->refresh();
            if ($productIntake->status !== 'converted' || ! $product->exists) {
                throw new RuntimeException('Produto: materializacao nao converteu o Intake.');
            }

            $opportunityIntake = new FactoryIntake();
            $opportunityIntake->forceFill([
                'title' => 'Smoke E2E Oportunidade',
                'request' => 'Validacao deterministica do Radar da Factory.',
                'output_mode' => 'opportunity',
                'origin' => 'new_idea',
                'type' => 'opportunity_search',
                'priority' => 'normal',
                'analysis_status' => 'pending',
                'status' => 'new',
                'references' => [],
                'intake_dna' => ['smoke_test' => true],
            ])->save();

            $opportunityAnalysis = [
                'profile_type' => 'generic',
                'profile_dna' => ['subject' => 'Factory opportunity smoke test', 'context' => 'Validacao interna reversivel', 'desired_state' => 'Oportunidade materializada no Radar'],
                'master_prompt' => 'Executar somente o smoke test deterministico de oportunidade.',
                'analysis' => ['summary' => 'fixture local'],
                'reference_assessment' => [],
                'assumptions' => [],
                'open_decisions' => [],
                'opportunities' => [[
                    'title' => 'Factory Smoke Opportunity',
                    'opportunity_type' => 'diagnostic',
                    'status' => 'identified',
                    'organization' => 'Factory Internal QA',
                    'territory' => 'internal',
                    'source' => 'fixture',
                    'source_url' => null,
                    'match_score' => 100,
                    'requirements' => [],
                    'gaps' => [],
                    'action_plan' => [],
                    'evidence' => ['smoke_test' => true],
                ]],
            ];

            $orchestrator->applyAnalysis($opportunityIntake, $opportunityAnalysis);
            $opportunityIntake->refresh();
            if ($opportunityIntake->analysis_status !== 'ready') {
                throw new RuntimeException('Oportunidade: analise nao chegou a ready.');
            }

            $opportunityIntake->forceFill(['analysis_status' => 'approved', 'status' => 'approved'])->save();
            $created = $opportunities->materializeApprovedAnalysis($opportunityIntake->fresh(['product']));
            $opportunityIntake->refresh();
            if ($opportunityIntake->status !== 'converted' || count($created) !== 1) {
                throw new RuntimeException('Oportunidade: materializacao do Radar falhou.');
            }

            $this->info('FACTORY_INTAKE_SMOKE_OK');
            $this->line('product_intake=converted');
            $this->line('product_pipeline=product+blueprint+capability+mission');
            $this->line('opportunity_intake=converted');
            $this->line('radar_opportunities=1');
            $this->line('persistence=rolled_back');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('FACTORY_INTAKE_SMOKE_FAILED: '.$e->getMessage());
            return self::FAILURE;
        } finally {
            DB::rollBack();
        }
    }
}
