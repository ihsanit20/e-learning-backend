<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFolderIdToModulesTable extends Migration
{
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->unsignedBigInteger('module_folder_id')->nullable()->after('course_id');
            $table->foreign('module_folder_id')->references('id')->on('module_folders')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign(['module_folder_id']);
            $table->dropColumn('module_folder_id');
        });
    }
}
