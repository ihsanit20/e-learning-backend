<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizWrittenAnswer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'answers' => 'json',
    ];

}
