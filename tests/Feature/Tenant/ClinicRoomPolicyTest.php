<?php

namespace Tests\Feature\Tenant;

use App\Models\ClinicRoom;
use App\Models\User;
use App\Policies\ClinicRoomPolicy;
use Mockery;
use Tests\TestCase;

class ClinicRoomPolicyTest extends TestCase
{
    protected ClinicRoomPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ClinicRoomPolicy;
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

    public function test_user_with_view_permission_can_view_any_clinic_rooms(): void
    {
        $user = $this->userWithPermission('clinic-room.view');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_view_permission_cannot_view_any_clinic_rooms(): void
    {
        $user = $this->userWithPermission('clinic-room.view', false);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_user_with_view_permission_can_view_clinic_room(): void
    {
        $user = $this->userWithPermission('clinic-room.view');
        $clinicRoom = new ClinicRoom;

        $this->assertTrue($this->policy->view($user, $clinicRoom));
    }

    public function test_user_without_view_permission_cannot_view_clinic_room(): void
    {
        $user = $this->userWithPermission('clinic-room.view', false);
        $clinicRoom = new ClinicRoom;

        $this->assertFalse($this->policy->view($user, $clinicRoom));
    }

    public function test_user_with_create_permission_can_create_clinic_room(): void
    {
        $user = $this->userWithPermission('clinic-room.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_clinic_room(): void
    {
        $user = $this->userWithPermission('clinic-room.create', false);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_update_permission_can_update_clinic_room(): void
    {
        $user = $this->userWithPermission('clinic-room.update');
        $clinicRoom = new ClinicRoom;

        $this->assertTrue($this->policy->update($user, $clinicRoom));
    }

    public function test_user_without_update_permission_cannot_update_clinic_room(): void
    {
        $user = $this->userWithPermission('clinic-room.update', false);
        $clinicRoom = new ClinicRoom;

        $this->assertFalse($this->policy->update($user, $clinicRoom));
    }

    public function test_user_with_delete_permission_can_delete_clinic_room(): void
    {
        $user = $this->userWithPermission('clinic-room.delete');
        $clinicRoom = new ClinicRoom;

        $this->assertTrue($this->policy->delete($user, $clinicRoom));
    }

    public function test_user_without_delete_permission_cannot_delete_clinic_room(): void
    {
        $user = $this->userWithPermission('clinic-room.delete', false);
        $clinicRoom = new ClinicRoom;

        $this->assertFalse($this->policy->delete($user, $clinicRoom));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
