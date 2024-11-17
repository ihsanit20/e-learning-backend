<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'password',
        'phone',
        'photo',
        'role',
        'affiliate_status',
        'additional_info',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'additional_info' => 'json',
    ];

    protected function photo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? ('https://ui-avatars.com/api/?name=' . str_replace(' ', '+', $this->name)),
        );
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'purchases');
    }
}