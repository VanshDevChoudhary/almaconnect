<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_entries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->year('batch');
            $table->string('branch', 100);
            $table->string('roll_no', 50)->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('batch');
            $table->index(['batch', 'branch']);
            $table->fullText('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_entries');
    }
};
