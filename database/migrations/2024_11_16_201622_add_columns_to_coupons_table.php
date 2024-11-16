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
        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('code_type', ['general', 'affiliate'])->default('general')->after('code');
            $table->foreignId('affiliate_user_id')->nullable()->after('code_type')->constrained('users');
            $table->json('course_ids')->nullable()->after('affiliate_user_id');
            $table->integer('commission_value')->default(0)->after('discount_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('commission_value');
            $table->dropColumn('course_ids');
            $table->dropForeign(['affiliate_user_id']);
            $table->dropColumn('affiliate_user_id');
            $table->dropColumn('code_type');
        });
    }
};
