<?php

namespace Tests\Feature\Tenant;

use App\Models\Audit;
use App\Models\CentralUser;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuditConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (app()->bound('currentClinic')) {
            app()->forgetInstance('currentClinic');
        }
    }

    public function test_audit_uses_central_connection_for_superadmin(): void
    {
        $user = new CentralUser([
            'name' => 'Test Superadmin',
            'email' => 'superadmin@test.it',
            'password' => bcrypt('password'),
        ]);

        $user->save();

        Auth::guard('superadmin')->setUser($user);

        $audit = new Audit;

        $this->assertSame('central', $audit->getConnectionName());
    }

    public function test_audit_uses_tenant_connection_for_tenant_user(): void
    {
        $clinic = Clinic::factory()->create([
            'active' => true,
        ]);

        app()->instance('currentClinic', $clinic);

        $user = User::factory()->make();

        Auth::guard('web')->setUser($user);

        $audit = new Audit;

        $this->assertSame('tenant', $audit->getConnectionName());
    }

    public function test_audit_falls_back_to_parent_connection_without_authentication(): void
    {
        $audit = new Audit;

        $this->assertSame(
            config('audit.drivers.database.connection'),
            $audit->getConnectionName()
        );
    }
}
