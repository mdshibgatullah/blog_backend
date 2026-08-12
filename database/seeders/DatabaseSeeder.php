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
        // Prothom super admin — role='admin', email already verified rakha hocche
        // jate migrate:fresh --seed er por shathe shathe login kora jay
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'), 
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
