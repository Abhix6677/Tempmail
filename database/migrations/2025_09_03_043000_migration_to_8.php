<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * DELETE THE FILE IN v8.1.0
 */

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasColumn('pages', 'lang')) {
            $pages = DB::table('pages')->select([
                'id',
                'lang',
                'slug',
                'title',
                'content',
                'meta',
                'header',
                'created_at',
                'updated_at'
            ])->whereNotNull('lang')->get();

            foreach ($pages as $page) {
                $primary = DB::table('pages')->where('slug', $page->slug)->whereNull('lang')->first();
                DB::table('pages')->where('id', $page->id)->delete();
                if ($primary) {
                    DB::table('translations')->insert([
                        'translatable_id' => $primary->id,
                        'translatable_type' => 'page',
                        'language' => $page->lang,
                        'title' => $page->title,
                        'content' => $page->content,
                        'meta' => $page->meta,
                        'header' => $page->header,
                        'created_at' => $page->created_at,
                        'updated_at' => $page->updated_at,
                    ]);
                }
            }

            Setting::put('version', '8.0.0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
    }
};
