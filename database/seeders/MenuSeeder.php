<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        if (Menu::where('link', route('page', 'blog'))->doesntExist()) {
            Menu::create([
                'name' => 'Blog',
                'link' => route('page', 'blog'),
                'status' => 1,
                'order' => 1,
                'location' => 'primary',
            ]);
        }
    }
}
