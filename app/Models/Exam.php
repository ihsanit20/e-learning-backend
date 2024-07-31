<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = ['module_id', 'title', 'duration', 'opening_time', 'link'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
