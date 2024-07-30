<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCoursesTable extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('materials');
            $table->string('course_type')->default('Live Course'); // Adding new column with default value
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->json('materials');
            $table->dropColumn('course_type');
        });
    }
}
