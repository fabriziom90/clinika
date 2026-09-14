<?php

namespace Tests\Feature\Tenant;

use App\Models\AppointmentReminder;
use App\Models\User;
use App\Policies\AppointmentReminderPolicy;
use Mockery;
use Tests\TestCase;

class AppointmentReminderPolicyTest extends TestCase
{
    protected AppointmentReminderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new AppointmentReminderPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_appointment_reminders(): void
    {
        $this->assertTrue($this->policy->viewAny($this->userWithPermission('appointment-reminder.view')));
    }

    public function test_user_without_view_permission_cannot_view_any_appointment_reminders(): void
    {
        $this->assertFalse($this->policy->viewAny($this->userWithPermission('appointment-reminder.view', false)));
    }

    public function test_user_with_view_permission_can_view_appointment_reminder(): void
    {
        $this->assertTrue($this->policy->view($this->userWithPermission('appointment-reminder.view'), new AppointmentReminder));
    }

    public function test_user_without_view_permission_cannot_view_appointment_reminder(): void
    {
        $this->assertFalse($this->policy->view($this->userWithPermission('appointment-reminder.view', false), new AppointmentReminder));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
