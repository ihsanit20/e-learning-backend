<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMcqAnswer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'answers' => 'json',
    ];

    public function user_exam()
    {
        return $this->belongsTo(UserExam::class);
    }
}
