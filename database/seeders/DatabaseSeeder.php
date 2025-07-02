<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin Rajapaksha',
            'email' => 'admin@a.b',
            'email_verified_at' => now(),
            'password' => Hash::make("12345678"), // password
            'role' => 1,
        ]);
    }
}
