<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'course_id',
        'code_type',
        'affiliate_user_id',
        'discount_type',
        'discount_value',
        'commission_value',
        'valid_from',
        'valid_until'
    ];

    public function isApplicableForCourseId($course_id): bool
    {
        return !$this->course_id || $this->course_id == $course_id;
    }

    public function scopeValidToday($query)
    {
        return $query
            ->whereDate('valid_from', '<=', now())
            ->whereDate('valid_until', '>=', now());
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}