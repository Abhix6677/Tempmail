<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        if (Page::where('slug', 'blog')->doesntExist()) {
            Page::create([
                'title' => 'Blog',
                'slug' => 'blog',
                'content' => '<p>Welcome to our blog!</p><p>[split]</p><p>You can add more content to the bottom of the list.</p>',
                'is_published' => true,
            ]);
        }
    }
}
