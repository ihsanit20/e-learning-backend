<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdatePhoneNumbersWithMissingPlusSign extends Migration
{
    public function up()
    {
        // যাদের ফোন নাম্বার 88 দিয়ে শুরু কিন্তু + নেই তাদের আপডেট করুন
        DB::table('users')->where('phone', 'like', '88%')->where('phone', 'not like', '+88%')
            ->update([
                'phone' => DB::raw("CONCAT('+', phone)") // ফোন নাম্বারের সামনে '+' যোগ করুন
            ]);
    }

    public function down()
    {
        // রোলব্যাক করার ক্ষেত্রে '+88' বাদ দিন এবং শুধু 88 রেখে দিন
        DB::table('users')->where('phone', 'like', '+88%')
            ->update([
                'phone' => DB::raw("SUBSTRING(phone, 2)") // '+' বাদ দিয়ে নাম্বার রিস্টোর করুন
            ]);
    }
}
