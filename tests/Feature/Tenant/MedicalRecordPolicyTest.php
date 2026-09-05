<?php

namespace Tests\Feature\Tenant;

use App\Models\MedicalRecord;
use App\Models\User;
use App\Policies\MedicalRecordPolicy;
use Mockery;
use Tests\TestCase;

class MedicalRecordPolicyTest extends TestCase
{
    protected MedicalRecordPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new MedicalRecordPolicy;
    }

    protected function userWithPermission(string $permission, bool $result = true): User
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('can')->once()->with($permission)->andReturn($result);

        return $user;
    }

    public function test_user_with_view_permission_can_view_medical_record(): void
    {
        $user = $this->userWithPermission('medical-record.view');
        $medicalRecord = new MedicalRecord;

        $this->assertTrue($this->policy->view($user, $medicalRecord));
    }

    public function test_user_without_view_permission_cannot_view_medical_record(): void
    {
        $user = $this->userWithPermission('medical-record.view', false);
        $medicalRecord = new MedicalRecord;

        $this->assertFalse($this->policy->view($user, $medicalRecord));
    }

    public function test_user_cannot_create_medical_record(): void
    {
        $user = Mockery::mock(User::class);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_cannot_update_medical_record(): void
    {
        $user = Mockery::mock(User::class);
        $medicalRecord = new MedicalRecord;

        $this->assertFalse($this->policy->update($user, $medicalRecord));
    }

    public function test_admin_can_delete_medical_record(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')->once()->with('admin')->andReturn(true);

        $medicalRecord = new MedicalRecord;

        $this->assertTrue($this->policy->delete($user, $medicalRecord));
    }

    public function test_non_admin_cannot_delete_medical_record(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')->once()->with('admin')->andReturn(false);

        $medicalRecord = new MedicalRecord;

        $this->assertFalse($this->policy->delete($user, $medicalRecord));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
