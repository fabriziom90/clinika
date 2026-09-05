<?php

namespace Tests\Feature\Tenant;

use App\Models\Patient;
use App\Models\User;
use App\Policies\PatientPolicy;
use Mockery;
use Tests\TestCase;

class PatientPolicyTest extends TestCase
{
    protected PatientPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new PatientPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_any_patients(): void
    {
        $user = $this->userWithPermission('patient.view');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_view_permission_cannot_view_any_patients(): void
    {
        $user = $this->userWithPermission('patient.view', false);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_user_with_view_permission_can_view_patient(): void
    {
        $user = $this->userWithPermission('patient.view');
        $patient = new Patient;

        $this->assertTrue($this->policy->view($user, $patient));
    }

    public function test_user_without_view_permission_cannot_view_patient(): void
    {
        $user = $this->userWithPermission('patient.view', false);
        $patient = new Patient;

        $this->assertFalse($this->policy->view($user, $patient));
    }

    public function test_user_with_create_permission_can_create_patient(): void
    {
        $user = $this->userWithPermission('patient.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_patient(): void
    {
        $user = $this->userWithPermission('patient.create', false);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_update_permission_can_update_patient(): void
    {
        $user = $this->userWithPermission('patient.update');
        $patient = new Patient;

        $this->assertTrue($this->policy->update($user, $patient));
    }

    public function test_user_without_update_permission_cannot_update_patient(): void
    {
        $user = $this->userWithPermission('patient.update', false);
        $patient = new Patient;

        $this->assertFalse($this->policy->update($user, $patient));
    }

    public function test_user_with_delete_permission_can_delete_patient(): void
    {
        $user = $this->userWithPermission('patient.delete');
        $patient = new Patient;

        $this->assertTrue($this->policy->delete($user, $patient));
    }

    public function test_user_without_delete_permission_cannot_delete_patient(): void
    {
        $user = $this->userWithPermission('patient.delete', false);
        $patient = new Patient;

        $this->assertFalse($this->policy->delete($user, $patient));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
