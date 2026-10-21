<?php

namespace Tests\Feature;

use App\Models\Engine;
use App\Models\User;
use App\Policies\FactoryEnginePolicy;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class FactoryEngineAuthorizationTest extends TestCase
{
    public function test_engine_permissions_fail_closed(): void
    {
        config()->set('factory_access.admin_email', null);
        $policy = new FactoryEnginePolicy();
        $user = new User(['email' => 'visitor@example.test']);
        $engine = new Engine();

        $this->assertFalse($policy->viewAny($user));
        $this->assertFalse($policy->create($user));
        $this->assertFalse($policy->view($user, $engine));
        $this->assertFalse($policy->update($user, $engine));
        $this->assertFalse($policy->delete($user, $engine));
    }

    public function test_only_configured_administrator_is_authorized(): void
    {
        config()->set('factory_access.admin_email', 'owner@example.test');
        $admin = new User(['email' => 'OWNER@example.test']);
        $other = new User(['email' => 'other@example.test']);
        $engine = new Engine();

        $this->assertTrue((new FactoryEnginePolicy())->create($admin));
        $this->assertFalse((new FactoryEnginePolicy())->create($other));

        $this->be($admin);
        $this->assertTrue(Gate::allows('create', Engine::class));
        $this->be($other);
        $this->assertFalse(Gate::allows('create', Engine::class));
    }
}
