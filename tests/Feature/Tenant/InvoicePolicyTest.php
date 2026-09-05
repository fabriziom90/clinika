<?php

namespace Tests\Feature\Tenant;

use App\Models\Invoice;
use App\Models\User;
use App\Policies\InvoicePolicy;
use Mockery;
use Tests\TestCase;

class InvoicePolicyTest extends TestCase
{
    protected InvoicePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new InvoicePolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_invoices(): void
    {
        $this->assertTrue($this->policy->viewAny($this->userWithPermission('invoices.view')));
    }

    public function test_user_without_view_permission_cannot_view_any_invoices(): void
    {
        $this->assertFalse($this->policy->viewAny($this->userWithPermission('invoices.view', false)));
    }

    public function test_user_with_view_permission_can_view_invoice(): void
    {
        $this->assertTrue($this->policy->view($this->userWithPermission('invoices.view'), new Invoice));
    }

    public function test_user_without_view_permission_cannot_view_invoice(): void
    {
        $this->assertFalse($this->policy->view($this->userWithPermission('invoices.view', false), new Invoice));
    }

    public function test_user_with_create_permission_can_create_invoice(): void
    {
        $this->assertTrue($this->policy->create($this->userWithPermission('invoices.create')));
    }

    public function test_user_without_create_permission_cannot_create_invoice(): void
    {
        $this->assertFalse($this->policy->create($this->userWithPermission('invoices.create', false)));
    }

    public function test_user_with_update_permission_can_update_invoice(): void
    {
        $this->assertTrue($this->policy->update($this->userWithPermission('invoices.update'), new Invoice));
    }

    public function test_user_without_update_permission_cannot_update_invoice(): void
    {
        $this->assertFalse($this->policy->update($this->userWithPermission('invoices.update', false), new Invoice));
    }

    public function test_user_with_delete_permission_can_delete_invoice(): void
    {
        $this->assertTrue($this->policy->delete($this->userWithPermission('invoices.delete'), new Invoice));
    }

    public function test_user_without_delete_permission_cannot_delete_invoice(): void
    {
        $this->assertFalse($this->policy->delete($this->userWithPermission('invoices.delete', false), new Invoice));
    }

    public function test_user_with_change_status_permission_can_change_invoice_status(): void
    {
        $this->assertTrue($this->policy->changeStatus($this->userWithPermission('invoices.change-status'), new Invoice));
    }

    public function test_user_without_change_status_permission_cannot_change_invoice_status(): void
    {
        $this->assertFalse($this->policy->changeStatus($this->userWithPermission('invoices.change-status', false), new Invoice));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
