<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use Mockery;
use Tests\TestCase;

class AppointmentPolicyTest extends TestCase
{
    protected AppointmentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new AppointmentPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_appointments(): void
    {
        $user = $this->userWithPermission('appointment.view');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_view_permission_cannot_view_any_appointments(): void
    {
        $user = $this->userWithPermission('appointment.view', false);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_user_with_view_permission_can_view_appointment(): void
    {
        $user = $this->userWithPermission('appointment.view');
        $appointment = new Appointment;

        $this->assertTrue($this->policy->view($user, $appointment));
    }

    public function test_user_without_view_permission_cannot_view_appointment(): void
    {
        $user = $this->userWithPermission('appointment.view', false);
        $appointment = new Appointment;

        $this->assertFalse($this->policy->view($user, $appointment));
    }

    public function test_user_with_create_permission_can_create_appointment(): void
    {
        $user = $this->userWithPermission('appointment.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_appointment(): void
    {
        $user = $this->userWithPermission('appointment.create', false);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_update_permission_can_update_appointment(): void
    {
        $user = $this->userWithPermission('appointment.update');
        $appointment = new Appointment;

        $this->assertTrue($this->policy->update($user, $appointment));
    }

    public function test_user_without_update_permission_cannot_update_appointment(): void
    {
        $user = $this->userWithPermission('appointment.update', false);
        $appointment = new Appointment;

        $this->assertFalse($this->policy->update($user, $appointment));
    }

    public function test_user_with_delete_permission_can_delete_appointment(): void
    {
        $user = $this->userWithPermission('appointment.delete');
        $appointment = new Appointment;

        $this->assertTrue($this->policy->delete($user, $appointment));
    }

    public function test_user_without_delete_permission_cannot_delete_appointment(): void
    {
        $user = $this->userWithPermission('appointment.delete', false);
        $appointment = new Appointment;

        $this->assertFalse($this->policy->delete($user, $appointment));
    }

    public function test_user_with_change_status_permission_can_change_appointment_status(): void
    {
        $user = $this->userWithPermission('appointment.change-status');
        $appointment = new Appointment;

        $this->assertTrue($this->policy->changeStatus($user, $appointment));
    }

    public function test_user_without_change_status_permission_cannot_change_appointment_status(): void
    {
        $user = $this->userWithPermission('appointment.change-status', false);
        $appointment = new Appointment;

        $this->assertFalse($this->policy->changeStatus($user, $appointment));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
