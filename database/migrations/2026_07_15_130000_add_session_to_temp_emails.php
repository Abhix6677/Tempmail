<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add session tracking columns to temp_emails for variant assignment.
     */
    public function up(): void
    {
        Schema::table('temp_emails', function (Blueprint $table) {
            $table->string('session_id', 255)->nullable()->after('generated_address');
            $table->string('assigned_to', 255)->nullable()->after('session_id');
            $table->timestamp('assigned_at')->nullable()->after('expires_at');
            $table->index('session_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temp_emails', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['session_id', 'assigned_to', 'assigned_at']);
        });
    }
};
