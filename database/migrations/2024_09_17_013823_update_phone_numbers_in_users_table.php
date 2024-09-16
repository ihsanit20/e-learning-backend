<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdatePhoneNumbersInUsersTable extends Migration
{
    public function up()
    {
        // সব ফোন নাম্বার ফরম্যাট করতে SQL UPDATE ব্যবহার করুন
        DB::table('users')->where('phone', 'not like', '+88%')
            ->update([
                'phone' => DB::raw("CONCAT('+88', phone)")
            ]);
    }

    public function down()
    {
        // যদি রোলব্যাক করতে হয়, '+88' রিমুভ করার কোড
        DB::table('users')->where('phone', 'like', '+88%')
            ->update([
                'phone' => DB::raw("SUBSTRING(phone, 4)") // +88 বাদ দিতে
            ]);
    }
}
