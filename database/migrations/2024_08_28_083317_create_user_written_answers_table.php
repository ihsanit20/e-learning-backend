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
        Schema::create('user_written_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_exam_id')->constrained('user_exams');
            $table->foreignId('question_id')->constrained('questions');
            $table->json('answers');
            $table->float('mark')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_written_answers');
    }
};
