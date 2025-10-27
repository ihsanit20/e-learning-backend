<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundleCourse extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'course_price' => 'int',
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
