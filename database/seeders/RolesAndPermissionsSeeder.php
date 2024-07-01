<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создание разрешений
        Permission::create(['name' => 'manage stadiums']);
        Permission::create(['name' => 'manage courts']);
        Permission::create(['name' => 'manage sport types']);
        Permission::create(['name' => 'manage users']);

        // Создание ролей и присвоение разрешений
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'manage stadiums',
            'manage courts',
            'manage sport types',
            'manage users'
        ]);

        $coachRole = Role::create(['name' => 'coach']);
        $coachRole->givePermissionTo([
            'manage stadiums',
            'manage courts'
        ]);

        Role::create(['name' => 'user']);
        Role::create(['name' => 'trainer']);
        Role::create(['name' => 'stadium worker']);
    }
}
