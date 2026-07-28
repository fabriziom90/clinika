<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return Inertia::render('Roles/IndexRole', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function togglePermission(Request $request)
    {
        $role = Role::findById($request->role_id);
        $permission = Permission::findById($request->permission_id);

        if ($role->hasPermissionTo($permission->name)) {
            $role->revokePermissionTo($permission->name);
        } else {
            $role->givePermissionTo($permission->name);
        }

        return back();
    }
}
