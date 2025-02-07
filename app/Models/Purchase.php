<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'auth_id',
        'course_id',
        'paid_amount',
        'trx_id',
        'discount_amount',
        'commission_amount',
        'coupon_code',
        'response',
    ];

    protected $casts = [
        'response' => 'json',
    ];

    protected $appends = [
        'date',
    ];

    public function getDateAttribute()
    {
        return $this->created_at
            ? $this->created_at->format('d-m-Y')
            : "";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
