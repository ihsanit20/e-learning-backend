<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleFolder extends Model
{
    use HasFactory;

    protected $casts = [
        'is_active' => 'bool',
    ];

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
        'is_active',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
