<?php

namespace Tests\Feature\Tenant;

use App\Models\InventoryDrug;
use App\Models\User;
use App\Policies\InventoryDrugPolicy;
use Mockery;
use Tests\TestCase;

class InventoryDrugPolicyTest extends TestCase
{
    protected InventoryDrugPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new InventoryDrugPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_inventory_drugs(): void
    {
        $this->assertTrue($this->policy->viewAny($this->userWithPermission('inventory-drug.view')));
    }

    public function test_user_without_view_permission_cannot_view_any_inventory_drugs(): void
    {
        $this->assertFalse($this->policy->viewAny($this->userWithPermission('inventory-drug.view', false)));
    }

    public function test_user_with_view_permission_can_view_inventory_drug(): void
    {
        $this->assertTrue($this->policy->view($this->userWithPermission('inventory-drug.view'), new InventoryDrug));
    }

    public function test_user_without_view_permission_cannot_view_inventory_drug(): void
    {
        $this->assertFalse($this->policy->view($this->userWithPermission('inventory-drug.view', false), new InventoryDrug));
    }

    public function test_user_with_create_permission_can_create_inventory_drug(): void
    {
        $this->assertTrue($this->policy->create($this->userWithPermission('inventory-drug.create')));
    }

    public function test_user_without_create_permission_cannot_create_inventory_drug(): void
    {
        $this->assertFalse($this->policy->create($this->userWithPermission('inventory-drug.create', false)));
    }

    public function test_user_with_update_permission_can_update_inventory_drug(): void
    {
        $this->assertTrue($this->policy->update($this->userWithPermission('inventory-drug.update'), new InventoryDrug));
    }

    public function test_user_without_update_permission_cannot_update_inventory_drug(): void
    {
        $this->assertFalse($this->policy->update($this->userWithPermission('inventory-drug.update', false), new InventoryDrug));
    }

    public function test_user_with_delete_permission_can_delete_inventory_drug(): void
    {
        $this->assertTrue($this->policy->delete($this->userWithPermission('inventory-drug.delete'), new InventoryDrug));
    }

    public function test_user_without_delete_permission_cannot_delete_inventory_drug(): void
    {
        $this->assertFalse($this->policy->delete($this->userWithPermission('inventory-drug.delete', false), new InventoryDrug));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
