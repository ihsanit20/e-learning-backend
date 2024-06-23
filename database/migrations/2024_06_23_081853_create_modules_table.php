<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // Module order
            $table->integer('duration')->nullable(); // Duration in minutes
            $table->boolean('is_active')->default(true); // Active status
            $table->unsignedBigInteger('prerequisite_module_id')->nullable(); // Prerequisite module
            $table->timestamps();

            $table->foreign('prerequisite_module_id')->references('id')->on('modules')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('modules');
    }
}
