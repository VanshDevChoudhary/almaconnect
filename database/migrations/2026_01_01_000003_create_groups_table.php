<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->enum('type', ['regional', 'batch', 'interest', 'professional'])->default('interest');
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index('slug');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
