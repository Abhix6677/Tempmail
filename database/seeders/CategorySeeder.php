<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $categories = [
            [
                'name' => 'General',
                'slug' => 'general',
            ]
        ];
        foreach ($categories as $category) {
            if (Category::count() == 0) {
                Category::updateOrCreate(
                    ['slug' => $category['slug']],
                    ['name' => $category['name']]
                );
            }
        }
    }
}
