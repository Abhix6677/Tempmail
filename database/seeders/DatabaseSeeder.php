<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            SettingSeeder::class,
            StatSeeder::class,
            CategorySeeder::class,
            DomainSeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
        ]);
    }
}
