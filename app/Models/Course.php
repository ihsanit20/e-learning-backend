<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'price',
        'start_date',
        'course_category',
        'course_type', // Add the new attribute here
    ];

    protected $casts = [
        'price' => 'int',
    ];

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? url('/images/course.jpg'),
        );
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'purchases');
    }
}
