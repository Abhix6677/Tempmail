<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('lang');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('lang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('lang')->nullable()->default(null);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('lang')->nullable()->default(null);
        });
    }
};
