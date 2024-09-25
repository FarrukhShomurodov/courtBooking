<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Findz',
            'second_name' => 'Findz',
            'login' => 'findz',
            'password' => Hash::make('findz')
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);
    }
}
