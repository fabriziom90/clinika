<?php

namespace Tests\Feature\Tenant;

use App\Models\ReminderType;
use App\Models\User;
use App\Policies\ReminderTypePolicy;
use Mockery;
use Tests\TestCase;

class ReminderTypePolicyTest extends TestCase
{
    protected ReminderTypePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ReminderTypePolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_reminder_types(): void
    {
        $this->assertTrue($this->policy->viewAny($this->userWithPermission('reminder-type.view')));
    }

    public function test_user_without_view_permission_cannot_view_any_reminder_types(): void
    {
        $this->assertFalse($this->policy->viewAny($this->userWithPermission('reminder-type.view', false)));
    }

    public function test_user_with_view_permission_can_view_reminder_type(): void
    {
        $this->assertTrue($this->policy->view($this->userWithPermission('reminder-type.view'), new ReminderType));
    }

    public function test_user_without_view_permission_cannot_view_reminder_type(): void
    {
        $this->assertFalse($this->policy->view($this->userWithPermission('reminder-type.view', false), new ReminderType));
    }

    public function test_user_with_create_permission_can_create_reminder_type(): void
    {
        $this->assertTrue($this->policy->create($this->userWithPermission('reminder-type.create')));
    }

    public function test_user_without_create_permission_cannot_create_reminder_type(): void
    {
        $this->assertFalse($this->policy->create($this->userWithPermission('reminder-type.create', false)));
    }

    public function test_user_with_update_permission_can_update_reminder_type(): void
    {
        $this->assertTrue($this->policy->update($this->userWithPermission('reminder-type.update'), new ReminderType));
    }

    public function test_user_without_update_permission_cannot_update_reminder_type(): void
    {
        $this->assertFalse($this->policy->update($this->userWithPermission('reminder-type.update', false), new ReminderType));
    }

    public function test_user_with_delete_permission_can_delete_reminder_type(): void
    {
        $this->assertTrue($this->policy->delete($this->userWithPermission('reminder-type.delete'), new ReminderType));
    }

    public function test_user_without_delete_permission_cannot_delete_reminder_type(): void
    {
        $this->assertFalse($this->policy->delete($this->userWithPermission('reminder-type.delete', false), new ReminderType));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
