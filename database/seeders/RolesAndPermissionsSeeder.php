<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'create item']);
        Permission::create(['name' => 'edit item']);
        Permission::create(['name' => 'approve deposit']);
        Permission::create(['name' => 'manage users']);

        $role = Role::create(['name' => 'petugas-gudang']);
        $role->givePermissionTo(['create item', 'edit item']);

        $role = Role::create(['name' => 'admin-perusahaan']);
        $role->givePermissionTo(['approve deposit', 'manage users']);

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}
