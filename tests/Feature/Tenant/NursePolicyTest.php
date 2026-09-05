<?php

namespace Tests\Feature\Tenant;

use App\Models\Nurse;
use App\Models\User;
use App\Policies\NursePolicy;
use Mockery;
use Tests\TestCase;

class NursePolicyTest extends TestCase
{
    protected NursePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new NursePolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_nurses(): void
    {
        $user = $this->userWithPermission('nurse.view');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_view_permission_cannot_view_any_nurses(): void
    {
        $user = $this->userWithPermission('nurse.view', false);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_user_with_view_permission_can_view_nurse(): void
    {
        $user = $this->userWithPermission('nurse.view');
        $nurse = new Nurse;

        $this->assertTrue($this->policy->view($user, $nurse));
    }

    public function test_user_without_view_permission_cannot_view_nurse(): void
    {
        $user = $this->userWithPermission('nurse.view', false);
        $nurse = new Nurse;

        $this->assertFalse($this->policy->view($user, $nurse));
    }

    public function test_user_with_create_permission_can_create_nurse(): void
    {
        $user = $this->userWithPermission('nurse.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_nurse(): void
    {
        $user = $this->userWithPermission('nurse.create', false);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_update_permission_can_update_nurse(): void
    {
        $user = $this->userWithPermission('nurse.update');
        $nurse = new Nurse;

        $this->assertTrue($this->policy->update($user, $nurse));
    }

    public function test_user_without_update_permission_cannot_update_nurse(): void
    {
        $user = $this->userWithPermission('nurse.update', false);
        $nurse = new Nurse;

        $this->assertFalse($this->policy->update($user, $nurse));
    }

    public function test_user_with_delete_permission_can_delete_nurse(): void
    {
        $user = $this->userWithPermission('nurse.delete');
        $nurse = new Nurse;

        $this->assertTrue($this->policy->delete($user, $nurse));
    }

    public function test_user_without_delete_permission_cannot_delete_nurse(): void
    {
        $user = $this->userWithPermission('nurse.delete', false);
        $nurse = new Nurse;

        $this->assertFalse($this->policy->delete($user, $nurse));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
