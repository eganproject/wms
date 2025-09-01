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
        $this->call([
            JabatanSeeder::class,
            MenuSeeder::class,
            UserSeeder::class,
            WarehouseSeeder::class,
            UomSeeder::class,
            ItemCategorySeeder::class,
            ItemSeeder::class,
        ]);
    }
}