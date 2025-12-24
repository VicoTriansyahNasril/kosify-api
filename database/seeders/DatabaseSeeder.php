<?php

namespace Database\Seeders;

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
        // 1. Buat Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@kosify.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890'
        ]);
    }
}