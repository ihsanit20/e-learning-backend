<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuiz extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_practice' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user_quiz_mcq_answers()
    {
        return $this->hasMany(UserQuizMcqAnswer::class);
    }

    public function user_quiz_written_answers()
    {
        return $this->hasMany(UserQuizWrittenAnswer::class);
    }
}
