<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'ADMIN',
            'second_name' => 'ADMIN',
            'login' => 'admin',
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);
    }
}
