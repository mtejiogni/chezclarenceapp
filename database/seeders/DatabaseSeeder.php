<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ParametreSeeder::class,
            StatutSeeder::class,
            CategorieSeeder::class,
            TableSeeder::class,
            UserSeeder::class,
            MenuSeeder::class,
        ]);
    }
}