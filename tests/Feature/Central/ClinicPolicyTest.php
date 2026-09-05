<?php

namespace Tests\Feature\Central;

use App\Models\CentralUser;
use App\Models\Clinic;
use App\Policies\ClinicPolicy;
use Tests\TestCase;

class ClinicPolicyTest extends TestCase
{
    protected ClinicPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ClinicPolicy;
    }

    public function test_superadmin_can_view_any_clinics(): void
    {
        $user = new CentralUser([
            'is_superadmin' => true,
        ]);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_non_superadmin_cannot_view_any_clinics(): void
    {
        $user = new CentralUser([
            'is_superadmin' => false,
        ]);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_superadmin_can_view_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => true,
        ]);

        $clinic = new Clinic;

        $this->assertTrue($this->policy->view($user, $clinic));
    }

    public function test_non_superadmin_cannot_view_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => false,
        ]);

        $clinic = new Clinic;

        $this->assertFalse($this->policy->view($user, $clinic));
    }

    public function test_superadmin_can_create_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => true,
        ]);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_non_superadmin_cannot_create_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => false,
        ]);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_superadmin_can_update_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => true,
        ]);

        $clinic = new Clinic;

        $this->assertTrue($this->policy->update($user, $clinic));
    }

    public function test_non_superadmin_cannot_update_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => false,
        ]);

        $clinic = new Clinic;

        $this->assertFalse($this->policy->update($user, $clinic));
    }

    public function test_superadmin_can_delete_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => true,
        ]);

        $clinic = new Clinic;

        $this->assertTrue($this->policy->delete($user, $clinic));
    }

    public function test_non_superadmin_cannot_delete_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => false,
        ]);

        $clinic = new Clinic;

        $this->assertFalse($this->policy->delete($user, $clinic));
    }

    public function test_superadmin_can_restore_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => true,
        ]);

        $clinic = new Clinic;

        $this->assertTrue($this->policy->restore($user, $clinic));
    }

    public function test_non_superadmin_cannot_restore_clinic(): void
    {
        $user = new CentralUser([
            'is_superadmin' => false,
        ]);

        $clinic = new Clinic;

        $this->assertFalse($this->policy->restore($user, $clinic));
    }
}
