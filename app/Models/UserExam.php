<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExam extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_practice' => 'boolean',
    ];

    public function scopeRanked($query)
    {
        return $query
            ->orderByDesc('obtained_mark')
            ->orderBy('mcq_negative_mark')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function user_mcq_answers()
    {
        return $this->hasMany(UserMcqAnswer::class);
    }

    public function user_written_answers()
    {
        return $this->hasMany(UserWrittenAnswer::class);
    }
}
