<?php

namespace Database\Seeders;

use App\Models\Stat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $emailIds = 0;
        $emailMessages = 0;
        if (Schema::hasTable('metas')) {
            $object = DB::table('metas')->where('key', 'email_ids_created')->first();
            $emailIds = intval($object->value);
            $object = DB::table('metas')->where('key', 'messages_received')->first();
            $emailMessages = intval($object->value);
        }
        if (Stat::where('type', 'messages_received')->where('date', '1970-01-01')->count() == 0) {
            Stat::create([
                'type' => 'messages_received',
                'count' => $emailMessages,
                'date' => '1970-01-01',
            ]);
        }
        if (Stat::where('type', 'emails_created')->where('date', '1970-01-01')->count() == 0) {
            Stat::create([
                'type' => 'emails_created',
                'count' => $emailIds,
                'date' => '1970-01-01',
            ]);
        }
    }
}
