<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        // Membuat role
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $pimpinanRole = Role::firstOrCreate([
            'name' => 'pimpinan',
            'guard_name' => 'web',
        ]);

        // Membuat akun Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@sikabar.test'],
            [
                'name' => 'Administrator',
                'password' => 'Admin12345!',
            ]
        );

        $admin->syncRoles([$adminRole]);

        // Membuat akun Pimpinan
        $pimpinan = User::firstOrCreate(
            ['email' => 'pimpinan@sikabar.test'],
            [
                'name' => 'Pimpinan Puskesmas',
                'password' => 'Pimpinan12345!',
            ]
        );

        $pimpinan->syncRoles([$pimpinanRole]);
    }
}
