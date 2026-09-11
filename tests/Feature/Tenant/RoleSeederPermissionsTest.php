<?php

namespace Tests\Feature\Tenant;

use App\Models\User;
use Tests\TenantTestCase;

class RoleSeederPermissionsTest extends TenantTestCase
{
    public function test_secretary_cannot_manage_roles_and_permissions(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');

        $this->assertFalse($secretary->can('role.view'));
        $this->assertFalse($secretary->can('role.create'));
        $this->assertFalse($secretary->can('role.update'));
        $this->assertFalse($secretary->can('role.delete'));
    }

    public function test_secretary_still_has_her_normal_operational_permissions(): void
    {

        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');

        $this->assertTrue($secretary->can('patient.view'));
        $this->assertTrue($secretary->can('patient.create'));
        $this->assertTrue($secretary->can('appointment.create'));
        $this->assertTrue($secretary->can('invoices.change-status'));
    }
}
