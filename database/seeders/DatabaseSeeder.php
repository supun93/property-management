<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Optional: Truncate the users table before seeding (only in dev)
        // User::truncate();

        // Create a default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@a.b',
            'password' => Hash::make('12345678'), // Hashed securely
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
}
