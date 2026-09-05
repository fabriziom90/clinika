<?php

namespace Tests\Feature\Tenant;

use App\Models\Drug;
use App\Models\User;
use App\Policies\DrugPolicy;
use Mockery;
use Tests\TestCase;

class DrugPolicyTest extends TestCase
{
    protected DrugPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new DrugPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_drugs(): void
    {
        $this->assertTrue($this->policy->viewAny($this->userWithPermission('drug.view')));
    }

    public function test_user_without_view_permission_cannot_view_any_drugs(): void
    {
        $this->assertFalse($this->policy->viewAny($this->userWithPermission('drug.view', false)));
    }

    public function test_user_with_view_permission_can_view_drug(): void
    {
        $this->assertTrue($this->policy->view($this->userWithPermission('drug.view'), new Drug));
    }

    public function test_user_without_view_permission_cannot_view_drug(): void
    {
        $this->assertFalse($this->policy->view($this->userWithPermission('drug.view', false), new Drug));
    }

    public function test_user_with_create_permission_can_create_drug(): void
    {
        $this->assertTrue($this->policy->create($this->userWithPermission('drug.create')));
    }

    public function test_user_without_create_permission_cannot_create_drug(): void
    {
        $this->assertFalse($this->policy->create($this->userWithPermission('drug.create', false)));
    }

    public function test_user_with_update_permission_can_update_drug(): void
    {
        $this->assertTrue($this->policy->update($this->userWithPermission('drug.update'), new Drug));
    }

    public function test_user_without_update_permission_cannot_update_drug(): void
    {
        $this->assertFalse($this->policy->update($this->userWithPermission('drug.update', false), new Drug));
    }

    public function test_user_with_delete_permission_can_delete_drug(): void
    {
        $this->assertTrue($this->policy->delete($this->userWithPermission('drug.delete'), new Drug));
    }

    public function test_user_without_delete_permission_cannot_delete_drug(): void
    {
        $this->assertFalse($this->policy->delete($this->userWithPermission('drug.delete', false), new Drug));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
