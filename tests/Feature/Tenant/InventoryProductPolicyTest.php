<?php

namespace Tests\Feature\Tenant;

use App\Models\InventoryProduct;
use App\Models\User;
use App\Policies\InventoryProductPolicy;
use Mockery;
use Tests\TestCase;

class InventoryProductPolicyTest extends TestCase
{
    protected InventoryProductPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new InventoryProductPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_inventory_products(): void
    {
        $this->assertTrue($this->policy->viewAny($this->userWithPermission('inventory-product.view')));
    }

    public function test_user_without_view_permission_cannot_view_any_inventory_products(): void
    {
        $this->assertFalse($this->policy->viewAny($this->userWithPermission('inventory-product.view', false)));
    }

    public function test_user_with_view_permission_can_view_inventory_product(): void
    {
        $this->assertTrue($this->policy->view($this->userWithPermission('inventory-product.view'), new InventoryProduct));
    }

    public function test_user_without_view_permission_cannot_view_inventory_product(): void
    {
        $this->assertFalse($this->policy->view($this->userWithPermission('inventory-product.view', false), new InventoryProduct));
    }

    public function test_user_with_create_permission_can_create_inventory_product(): void
    {
        $this->assertTrue($this->policy->create($this->userWithPermission('inventory-product.create')));
    }

    public function test_user_without_create_permission_cannot_create_inventory_product(): void
    {
        $this->assertFalse($this->policy->create($this->userWithPermission('inventory-product.create', false)));
    }

    public function test_user_with_update_permission_can_update_inventory_product(): void
    {
        $this->assertTrue($this->policy->update($this->userWithPermission('inventory-product.update'), new InventoryProduct));
    }

    public function test_user_without_update_permission_cannot_update_inventory_product(): void
    {
        $this->assertFalse($this->policy->update($this->userWithPermission('inventory-product.update', false), new InventoryProduct));
    }

    public function test_user_with_delete_permission_can_delete_inventory_product(): void
    {
        $this->assertTrue($this->policy->delete($this->userWithPermission('inventory-product.delete'), new InventoryProduct));
    }

    public function test_user_without_delete_permission_cannot_delete_inventory_product(): void
    {
        $this->assertFalse($this->policy->delete($this->userWithPermission('inventory-product.delete', false), new InventoryProduct));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
