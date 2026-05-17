<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('company');
            $table->string('location')->nullable();
            $table->enum('type', ['full_time', 'internship', 'contract', 'part_time']);
            $table->text('description');
            $table->json('skills')->nullable();
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->string('salary_currency', 10)->default('INR');
            $table->string('apply_url', 500)->nullable();
            $table->string('apply_email')->nullable();
            $table->enum('status', ['active', 'filled', 'expired'])->default('active');
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_listings');
    }
};
