<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
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
        'duration',
        'is_active',
        'prerequisite_module_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function prerequisite()
    {
        return $this->belongsTo(Module::class, 'prerequisite_module_id');
    }
}
