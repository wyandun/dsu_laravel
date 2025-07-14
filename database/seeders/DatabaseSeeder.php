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
        $this->call([
            CoordinacionSeeder::class,
            DireccionSeeder::class,
            UserSeeder::class,
            ActivitySeeder::class,
        ]);
    }
}
