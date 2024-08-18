<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        if(!\App\Models\User::where('phone', '00000000000')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'Admin',
                'phone' => '00000000000',
                'role' => 'developer',
                'password' => '$2y$10$9R4enwc8ts5hRTog1Vnkve32nCpq0Nc5Lv8K/3fM5nmWmtPscKASW', // password
            ]);
        }

    }
}
