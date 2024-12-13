<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quiz extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function quiz_questions()
    {
        return $this->hasMany(QuizQuestion::class)
            ->oldest('quiz_questions.priority');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'quiz_questions')
            ->oldest('quiz_questions.priority');
    }

    public function user_quizzes()
    {
        return $this->hasMany(UserQuiz::class);
    }

    public function user_quiz()
    {
        return $this->hasOne(UserQuiz::class)
            ->where('user_id', auth('sanctum')->id());
    }
}
