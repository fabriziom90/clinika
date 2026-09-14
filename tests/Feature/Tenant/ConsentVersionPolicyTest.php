<?php

namespace Tests\Feature\Tenant;

use App\Models\ConsentVersion;
use App\Models\User;
use App\Policies\ConsentVersionPolicy;
use Mockery;
use Tests\TestCase;

class ConsentVersionPolicyTest extends TestCase
{
    protected ConsentVersionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ConsentVersionPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_consent_versions(): void
    {
        $this->assertTrue($this->policy->viewAny($this->userWithPermission('consent-version.view')));
    }

    public function test_user_without_view_permission_cannot_view_any_consent_versions(): void
    {
        $this->assertFalse($this->policy->viewAny($this->userWithPermission('consent-version.view', false)));
    }

    public function test_user_with_view_permission_can_view_consent_version(): void
    {
        $this->assertTrue($this->policy->view($this->userWithPermission('consent-version.view'), new ConsentVersion));
    }

    public function test_user_without_view_permission_cannot_view_consent_version(): void
    {
        $this->assertFalse($this->policy->view($this->userWithPermission('consent-version.view', false), new ConsentVersion));
    }

    public function test_user_with_create_permission_can_create_consent_version(): void
    {
        $this->assertTrue($this->policy->create($this->userWithPermission('consent-version.create')));
    }

    public function test_user_without_create_permission_cannot_create_consent_version(): void
    {
        $this->assertFalse($this->policy->create($this->userWithPermission('consent-version.create', false)));
    }

    public function test_user_with_update_permission_can_update_consent_version(): void
    {
        $this->assertTrue($this->policy->update($this->userWithPermission('consent-version.update'), new ConsentVersion));
    }

    public function test_user_without_update_permission_cannot_update_consent_version(): void
    {
        $this->assertFalse($this->policy->update($this->userWithPermission('consent-version.update', false), new ConsentVersion));
    }

    public function test_user_with_delete_permission_can_delete_consent_version(): void
    {
        $this->assertTrue($this->policy->delete($this->userWithPermission('consent-version.delete'), new ConsentVersion));
    }

    public function test_user_without_delete_permission_cannot_delete_consent_version(): void
    {
        $this->assertFalse($this->policy->delete($this->userWithPermission('consent-version.delete', false), new ConsentVersion));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
