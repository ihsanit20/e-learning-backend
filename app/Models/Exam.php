<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'type',
        'duration',
        'opening_time',
        'result_publish_time',
        'link'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function exam_questions()
    {
        return $this->hasMany(ExamQuestion::class)
            ->oldest('exam_questions.priority');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->oldest('exam_questions.priority');
    }

    public function user_exams()
    {
        return $this->hasMany(UserExam::class);
    }

    public function user_regular_exams()
    {
        return $this->hasMany(UserExam::class)
            ->where('is_practice', 0);
    }

    public function user_practice_exams()
    {
        return $this->hasMany(UserExam::class)
            ->where('is_practice', 1);
    }

    public function user_exam()
    {
        return $this->hasOne(UserExam::class)
            ->where('user_id', auth('sanctum')->id());
    }
}
