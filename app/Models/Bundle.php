<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $casts = [
        'is_active' => 'bool',
    ];

    public function bundleCourses()
    {
        return $this->hasMany(BundleCourse::class);
    }
}
