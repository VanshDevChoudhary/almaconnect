<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->year('batch')->nullable();
            $table->string('branch', 100)->nullable();
            $table->string('roll_no', 50)->nullable();
            $table->string('current_company')->nullable();
            $table->string('current_role')->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('batch');
            $table->index('branch');
            $table->index('industry');
            $table->index('city');
            $table->index(['batch', 'branch']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
