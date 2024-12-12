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
        Schema::create('user_quizzes', function (Blueprint $table) {
            $table->id();

            $table->uuid('quiz_id');
            $table->foreign('quiz_id')->references('id')->on('quizzes');

            $table->foreignId('user_id')->constrained();
            $table->float('obtained_mark')->nullable();
            $table->float('mcq_correct_mark')->nullable();
            $table->float('mcq_negative_mark')->nullable();
            $table->float('written_mark')->nullable();
            $table->boolean('is_practice')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quizzes');
    }
};
