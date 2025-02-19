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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('priority')->index()->default(0);

            $table->uuid('quiz_id');
            $table->foreign('quiz_id')->references('id')->on('quizzes');

            $table->foreignId('question_id')->constrained();
            $table->unsignedFloat('mark');
            $table->unsignedFloat('negative_mark')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
