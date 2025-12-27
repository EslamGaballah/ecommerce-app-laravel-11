<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissionsConfig = config('permissions');

        if (!is_array($permissionsConfig)) {
            throw new \Exception('permissions config must be an array');
}

        $permissions = collect(config('permissions'))->flatten();

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // create roles
       
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user   = Role::firstOrCreate(['name' => 'user']);
        $editor = Role::firstOrCreate(['name' => 'editor']);

        // permissions roles
        $admin->permissions()->sync(Permission::pluck('id'));

        $editorPerms = Permission::whereIn('name', [
            'create-product', 'edit-product', 'view-product'
        ])->pluck('id');
        $editor->permissions()->sync($editorPerms);

        $userPerms = Permission::whereIn('name', [
            'create-product', 'view-product'
        ])->pluck('id');
        $user->permissions()->sync($userPerms);

        // ربط أول مستخدم كـ Admin

        if ($adminUser = User::first()) {
            $adminUser->roles()->sync([$admin->id]);
        }
    }
}
