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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration_minutes');
            $table->integer('pass_mark');
            $table->integer('total_mark');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->integer('cooldown_period')->default(24); // hours or minutes, let's use hours based on user example (24 hours)
            $table->json('distribution_rules');
            $table->string('stage');
            $table->string('moss_userid')->nullable();
            $table->string('moss_language')->nullable();
            $table->integer('moss_sensitivity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
