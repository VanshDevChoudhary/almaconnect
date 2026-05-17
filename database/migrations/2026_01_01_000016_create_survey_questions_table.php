<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('question', 500);
            $table->enum('type', ['text', 'single_choice', 'multi_choice']);
            $table->json('options')->nullable();
            $table->integer('order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
