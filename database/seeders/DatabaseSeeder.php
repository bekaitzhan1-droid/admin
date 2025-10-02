<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use MoonShine\Laravel\Models\MoonshineUser;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        MoonshineUser::create([
            'name' => 'Admin',
            'email' => 'Bek.aitzhan1@gmail.com',
            'password' => bcrypt('admin'),
        ]);
    }
}
