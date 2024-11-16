<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'code_type',
        'affiliate_user_id',
        'discount_type',
        'discount_value',
        'commission_value',
        'valid_from',
        'valid_until'
    ];
}