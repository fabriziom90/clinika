<?php

namespace Tests\Feature\Tenant;

use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use Mockery;
use Tests\TestCase;

class ProductPolicyTest extends TestCase
{
    protected ProductPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ProductPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_products(): void
    {
        $this->assertTrue($this->policy->viewAny($this->userWithPermission('product.view')));
    }

    public function test_user_without_view_permission_cannot_view_any_products(): void
    {
        $this->assertFalse($this->policy->viewAny($this->userWithPermission('product.view', false)));
    }

    public function test_user_with_view_permission_can_view_product(): void
    {
        $this->assertTrue($this->policy->view($this->userWithPermission('product.view'), new Product));
    }

    public function test_user_without_view_permission_cannot_view_product(): void
    {
        $this->assertFalse($this->policy->view($this->userWithPermission('product.view', false), new Product));
    }

    public function test_user_with_create_permission_can_create_product(): void
    {
        $this->assertTrue($this->policy->create($this->userWithPermission('product.create')));
    }

    public function test_user_without_create_permission_cannot_create_product(): void
    {
        $this->assertFalse($this->policy->create($this->userWithPermission('product.create', false)));
    }

    public function test_user_with_update_permission_can_update_product(): void
    {
        $this->assertTrue($this->policy->update($this->userWithPermission('product.update'), new Product));
    }

    public function test_user_without_update_permission_cannot_update_product(): void
    {
        $this->assertFalse($this->policy->update($this->userWithPermission('product.update', false), new Product));
    }

    public function test_user_with_delete_permission_can_delete_product(): void
    {
        $this->assertTrue($this->policy->delete($this->userWithPermission('product.delete'), new Product));
    }

    public function test_user_without_delete_permission_cannot_delete_product(): void
    {
        $this->assertFalse($this->policy->delete($this->userWithPermission('product.delete', false), new Product));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
