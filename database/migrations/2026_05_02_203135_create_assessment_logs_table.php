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
        Schema::create('assessment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained('assessment_attempts')->onDelete('cascade');
            $table->string('event_type');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at', 3); // Millisecond precision
            $table->timestamp('created_at')->nullable(); // Only created_at, no updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_logs');
    }
};
