<?php

namespace Tests\Feature\Tenant;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TenantTestCase;

class RoleControllerTest extends TenantTestCase
{
    private function host(): string
    {
        return $this->clinic->slug.'.clinika.test';
    }

    private function url(string $routeName, array $params = []): string
    {
        return 'http://'.$this->host().route($routeName, $params, false);
    }

    public function test_user_without_view_permission_cannot_access_roles_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get($this->url('admin.roles-permissions.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_roles_index_without_explicit_permission(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin'); // RolePolicy::before() dà accesso pieno all'admin

        $this->actingAs($admin)
            ->get($this->url('admin.roles-permissions.index'))
            ->assertSuccessful();
    }

    public function test_user_without_update_permission_cannot_toggle_a_role_permission(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('secretary', 'web');
        $permission = Permission::findOrCreate('appointment.view', 'web');

        $this->actingAs($user)
            ->post($this->url('admin.roles-permissions.toggle'), [
                'role_id' => $role->id,
                'permission_id' => $permission->id,
            ])
            ->assertForbidden();
    }

    public function test_secretary_cannot_grant_a_permission_to_her_own_role(): void
    {
        $secretaryUser = User::factory()->create();
        $secretaryUser->assignRole('secretary');

        $secretaryRole = Role::findByName('secretary', 'web');

        $this->assertFalse(
            $secretaryUser->can('role.update'),
            'Prerequisito del test: la segretaria non deve avere role.update di suo.'
        );

        $dangerousPermission = Permission::findOrCreate('role.update', 'web');

        $this->actingAs($secretaryUser)
            ->post($this->url('admin.roles-permissions.toggle'), [
                'role_id' => $secretaryRole->id,
                'permission_id' => $dangerousPermission->id,
            ])
            ->assertForbidden();

        $this->assertFalse(
            $secretaryRole->fresh()->hasPermissionTo('role.update'),
            'Il ruolo secretary non deve aver ottenuto role.update in seguito al tentativo.'
        );
    }

    public function test_user_with_update_permission_can_toggle_a_role_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('role.update');

        $roleName = 'test-role-'.Str::lower(Str::random(10));
        $permissionName = 'test.permission.'.Str::lower(Str::random(10));

        $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
        $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);

        $this->assertFalse($role->hasPermissionTo($permission));
        $this->actingAs($user)->post($this->url('admin.roles-permissions.toggle'), ['role_id' => $role->id, 'permission_id' => $permission->id])->assertRedirect();

        $this->assertTrue($role->fresh()->hasPermissionTo($permission));
        $this->actingAs($user)->post($this->url('admin.roles-permissions.toggle'), ['role_id' => $role->id, 'permission_id' => $permission->id])->assertRedirect();

        $this->assertFalse($role->fresh()->hasPermissionTo($permission));
    }

    public function test_admin_can_toggle_a_role_permission_without_explicit_permission(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $role = Role::findOrCreate('nurse', 'web');
        $permission = Permission::findOrCreate('product.view', 'web');

        $this->actingAs($admin)
            ->post($this->url('admin.roles-permissions.toggle'), [
                'role_id' => $role->id,
                'permission_id' => $permission->id,
            ])
            ->assertRedirect();

        $this->assertTrue($role->fresh()->hasPermissionTo('product.view'));
    }
}
