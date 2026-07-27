<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('received_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temp_email_id')->constrained('temp_emails')->onDelete('cascade');
            $table->string('from_address');
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_emails');
    }
};
