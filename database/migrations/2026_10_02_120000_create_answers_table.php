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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('question', 300);
            $table->string('question_hash', 40);
            $table->string('locale', 5)->default('en');
            $table->string('status')->default('pending');
            $table->json('content')->nullable();
            $table->string('model')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('asked_count')->default(1);
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['locale', 'question_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
