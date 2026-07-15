<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $domains = Setting::pick('domains');
        foreach ($domains as $domain) {
            if (Domain::where('domain', $domain)->exists()) {
                continue; // Skip if the domain already exists
            }
            Domain::create([
                'domain' => $domain,
            ]);
        }
    }
}
