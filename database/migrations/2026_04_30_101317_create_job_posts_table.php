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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('department');
            $table->string('location')->nullable();
            $table->json('skills')->nullable();
            $table->integer('experience_level')->default(0);
            $table->string('status')->default('PENDING');
            $table->text('status_reason')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('status_updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('status_updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
