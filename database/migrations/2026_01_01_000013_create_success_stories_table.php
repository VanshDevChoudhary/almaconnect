<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('headline');
            $table->string('slug')->unique();
            $table->text('body');
            $table->string('cover_image', 500)->nullable();
            $table->enum('category', ['entrepreneurship', 'research', 'social_impact', 'career', 'other']);
            $table->enum('status', ['pending', 'published', 'rejected'])->default('pending');
            $table->foreignId('submitted_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('category');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('success_stories');
    }
};
