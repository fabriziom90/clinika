<?php

namespace Tests\Feature\Tenant;

use App\Models\Secretary;
use App\Models\User;
use App\Policies\SecretaryPolicy;
use Mockery;
use Tests\TestCase;

class SecretaryPolicyTest extends TestCase
{
    protected SecretaryPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new SecretaryPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_secretaries(): void
    {
        $user = $this->userWithPermission('secretary.view');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_view_permission_cannot_view_any_secretaries(): void
    {
        $user = $this->userWithPermission('secretary.view', false);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_user_with_view_permission_can_view_secretary(): void
    {
        $user = $this->userWithPermission('secretary.view');
        $secretary = new Secretary;

        $this->assertTrue($this->policy->view($user, $secretary));
    }

    public function test_user_without_view_permission_cannot_view_secretary(): void
    {
        $user = $this->userWithPermission('secretary.view', false);
        $secretary = new Secretary;

        $this->assertFalse($this->policy->view($user, $secretary));
    }

    public function test_user_with_create_permission_can_create_secretary(): void
    {
        $user = $this->userWithPermission('secretary.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_secretary(): void
    {
        $user = $this->userWithPermission('secretary.create', false);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_update_permission_can_update_secretary(): void
    {
        $user = $this->userWithPermission('secretary.update');
        $secretary = new Secretary;

        $this->assertTrue($this->policy->update($user, $secretary));
    }

    public function test_user_without_update_permission_cannot_update_secretary(): void
    {
        $user = $this->userWithPermission('secretary.update', false);
        $secretary = new Secretary;

        $this->assertFalse($this->policy->update($user, $secretary));
    }

    public function test_user_with_delete_permission_can_delete_secretary(): void
    {
        $user = $this->userWithPermission('secretary.delete');
        $secretary = new Secretary;

        $this->assertTrue($this->policy->delete($user, $secretary));
    }

    public function test_user_without_delete_permission_cannot_delete_secretary(): void
    {
        $user = $this->userWithPermission('secretary.delete', false);
        $secretary = new Secretary;

        $this->assertFalse($this->policy->delete($user, $secretary));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
