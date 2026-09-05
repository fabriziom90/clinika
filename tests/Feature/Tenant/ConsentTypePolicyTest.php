<?php

namespace Tests\Feature\Tenant;

use App\Models\ConsentType;
use App\Models\User;
use App\Policies\ConsentTypePolicy;
use Mockery;
use Tests\TestCase;

class ConsentTypePolicyTest extends TestCase
{
    protected ConsentTypePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ConsentTypePolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')
            ->once()
            ->with($permission)
            ->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_consent_types(): void
    {
        $user = $this->userWithPermission('consent-type.view');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_view_permission_cannot_view_any_consent_types(): void
    {
        $user = $this->userWithPermission('consent-type.view', false);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_user_with_view_permission_can_view_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.view');
        $consentType = new ConsentType;

        $this->assertTrue($this->policy->view($user, $consentType));
    }

    public function test_user_without_view_permission_cannot_view_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.view', false);
        $consentType = new ConsentType;

        $this->assertFalse($this->policy->view($user, $consentType));
    }

    public function test_user_with_create_permission_can_create_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.create', false);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_update_permission_can_update_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.update');
        $consentType = new ConsentType;

        $this->assertTrue($this->policy->update($user, $consentType));
    }

    public function test_user_without_update_permission_cannot_update_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.update', false);
        $consentType = new ConsentType;

        $this->assertFalse($this->policy->update($user, $consentType));
    }

    public function test_user_with_delete_permission_can_delete_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.delete');
        $consentType = new ConsentType;

        $this->assertTrue($this->policy->delete($user, $consentType));
    }

    public function test_user_without_delete_permission_cannot_delete_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.delete', false);
        $consentType = new ConsentType;

        $this->assertFalse($this->policy->delete($user, $consentType));
    }

    public function test_user_with_restore_permission_can_restore_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.restore');
        $consentType = new ConsentType;

        $this->assertTrue($this->policy->restore($user, $consentType));
    }

    public function test_user_without_restore_permission_cannot_restore_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.restore', false);
        $consentType = new ConsentType;

        $this->assertFalse($this->policy->restore($user, $consentType));
    }

    public function test_user_with_force_delete_permission_can_force_delete_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.forceDelete');
        $consentType = new ConsentType;

        $this->assertTrue($this->policy->forceDelete($user, $consentType));
    }

    public function test_user_without_force_delete_permission_cannot_force_delete_consent_type(): void
    {
        $user = $this->userWithPermission('consent-type.forceDelete', false);
        $consentType = new ConsentType;

        $this->assertFalse($this->policy->forceDelete($user, $consentType));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
