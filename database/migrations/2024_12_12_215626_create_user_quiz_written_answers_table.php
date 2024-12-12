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
        Schema::create('user_quiz_written_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_quiz_id')->constrained();
            $table->foreignId('question_id')->constrained();
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
        Schema::dropIfExists('user_quiz_written_answers');
    }
};
