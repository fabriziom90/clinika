<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = Role::create(['name' => 'superadmin']);
        $doctor = Role::create(['name' => 'doctor']);
        $nurse = Role::create(['name' => 'nurse']);

        // Permessi base
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage patients']);
        Permission::create(['name' => 'manage appointments']);
        Permission::create(['name' => 'manage invoices']);

        // Assegno permessi ai ruoli
        $superadmin->givePermissionTo(Permission::all());
        $doctor->givePermissionTo(['manage patients', 'manage appointments']);
        $nurse->givePermissionTo(['manage patients']);
    }
}
