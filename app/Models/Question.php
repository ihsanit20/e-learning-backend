<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    static $exam_id = null;
    static $quiz_id = null;

    protected $fillable = ['chapter_id', 'type', 'question_text', 'explanation'];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function mcq_options()
    {
        return $this->hasMany(McqOption::class);
    }

    public function user_mcq_answer()
    {
        return $this->hasOne(UserMcqAnswer::class)
            ->whereHas('user_exam', function ($query) {
                $query->where('user_id', auth('sanctum')->id())
                    ->when(self::$exam_id, function ($query, $exam_id) {
                        $query->where('exam_id', $exam_id);
                    });
            })
            ;
    }

    public function user_quiz_mcq_answer()
    {
        return $this->hasOne(UserQuizMcqAnswer::class)
            ->whereHas('user_quiz', function ($query) {
                $query->where('user_id', auth('sanctum')->id())
                    ->when(self::$quiz_id, function ($query, $quiz_id) {
                        $query->where('quiz_id', $quiz_id);
                    });
            })
            ;
    }

}
