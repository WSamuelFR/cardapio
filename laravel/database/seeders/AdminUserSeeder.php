<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'wesleysamuelfr@outlook.com'],
            [
                'name' => 'Wesley Samuel Ferreira Rodrigues',
                'password' => Hash::make('Samuel9026'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'well_rodrigues@outlook.com'],
            [
                'name' => 'Wellington Ferreira Rodrigues',
                'password' => Hash::make('Ferreira7878'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'motoboy@aky.com'],
            [
                'name' => 'Carlos da Silva (Motoboy)',
                'password' => Hash::make('Motoboy123'),
                'role' => 'motoboy',
                'is_active' => true,
            ]
        );
    }
}

