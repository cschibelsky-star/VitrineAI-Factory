<?php

namespace Tests\Unit;

use App\Factory\Services\FactoryExistingProjectAdoptionService;
use App\Models\FactoryArtifact;
use App\Models\FactoryProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryExistingProjectAdoptionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adopts_existing_project_without_requesting_runtime_mutation(): void
    {
        $manifest = [
            'id' => 'gerocadastro',
            'name' => 'GEROCADASTRO',
            'workspace_root' => '/srv/projects/gerocadastro',
            'repository' => [
                'url' => 'git@github.com:cschibelsky-star/gerocadastro.git',
                'branch' => 'main',
                'directory' => 'repository',
            ],
            'docker' => [
                'compose_file' => 'docker-compose.yml',
                'project_name' => 'gerocadastro',
            ],
            'domains' => ['gerocadastro.example.test'],
        ];

        $product = app(FactoryExistingProjectAdoptionService::class)->adopt($manifest);

        $this->assertInstanceOf(FactoryProduct::class, $product);
        $this->assertSame('gerocadastro', $product->slug);
        $this->assertSame('adopted', $product->status);
        $this->assertTrue($product->product_dna['adoption_policy']['preserve_code']);
        $this->assertTrue($product->product_dna['adoption_policy']['preserve_database']);
        $this->assertTrue($product->product_dna['adoption_policy']['preserve_docker']);
        $this->assertTrue($product->product_dna['adoption_policy']['no_automatic_deploy']);

        $artifact = FactoryArtifact::query()
            ->where('product_id', $product->id)
            ->where('type', 'adoption_snapshot')
            ->firstOrFail();

        $this->assertSame('captured', $artifact->status);
        $this->assertTrue($artifact->metadata['adopted_without_mutation']);
        $this->assertSame('gerocadastro', $artifact->evidence['project_id']);
    }
}
