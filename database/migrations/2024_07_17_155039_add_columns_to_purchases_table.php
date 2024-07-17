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
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('paid_amount')->default(0);
            $table->string('trx_id')->unique()->nullable();
            $table->decimal('discount_amount')->default(0);
            $table->string('coupon_code')->nullable();
            $table->json('response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
            $table->dropColumn('trx_id');
            $table->dropColumn('discount_amount');
            $table->dropColumn('coupon_code');
            $table->dropColumn('response');
        });
    }
};
