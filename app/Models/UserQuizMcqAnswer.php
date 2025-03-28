<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizMcqAnswer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'answers' => 'json',
    ];

    public function user_quiz()
    {
        return $this->belongsTo(UserQuiz::class);
    }
}
