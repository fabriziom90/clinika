<?php

namespace Tests\Feature\Tenant;

use App\Models\Doctor;
use App\Models\User;
use App\Policies\DoctorPolicy;
use Mockery;
use Tests\TestCase;

class DoctorPolicyTest extends TestCase
{
    protected DoctorPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new DoctorPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_doctors(): void
    {
        $user = $this->userWithPermission('doctor.view');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_view_permission_cannot_view_any_doctors(): void
    {
        $user = $this->userWithPermission('doctor.view', false);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_user_with_view_permission_can_view_doctor(): void
    {
        $user = $this->userWithPermission('doctor.view');
        $doctor = new Doctor;

        $this->assertTrue($this->policy->view($user, $doctor));
    }

    public function test_user_without_view_permission_cannot_view_doctor(): void
    {
        $user = $this->userWithPermission('doctor.view', false);
        $doctor = new Doctor;

        $this->assertFalse($this->policy->view($user, $doctor));
    }

    public function test_user_with_create_permission_can_create_doctor(): void
    {
        $user = $this->userWithPermission('doctor.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_doctor(): void
    {
        $user = $this->userWithPermission('doctor.create', false);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_update_permission_can_update_doctor(): void
    {
        $user = $this->userWithPermission('doctor.update');
        $doctor = new Doctor;

        $this->assertTrue($this->policy->update($user, $doctor));
    }

    public function test_user_without_update_permission_cannot_update_doctor(): void
    {
        $user = $this->userWithPermission('doctor.update', false);
        $doctor = new Doctor;

        $this->assertFalse($this->policy->update($user, $doctor));
    }

    public function test_user_with_delete_permission_can_delete_doctor(): void
    {
        $user = $this->userWithPermission('doctor.delete');
        $doctor = new Doctor;

        $this->assertTrue($this->policy->delete($user, $doctor));
    }

    public function test_user_without_delete_permission_cannot_delete_doctor(): void
    {
        $user = $this->userWithPermission('doctor.delete', false);
        $doctor = new Doctor;

        $this->assertFalse($this->policy->delete($user, $doctor));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
