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
        'category_id',
        'course_type',
        'facebook_group_link',
        'is_published',
        'is_active',
    ];

    protected $casts = [
        'price' => 'int',
        'is_published' => 'bool',
        'is_active' => 'bool',
    ];

    public function scopeActive($query, $is_active = true)
    {
        return $query->where('is_active', $is_active);
    }

    public function scopePublished($query, $is_published = true)
    {
        return $query->where('is_published', $is_published);
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? url('/images/course.jpg'),
        );
    }

    public function modules()
    {
        return $this->hasMany(Module::class)
            ->orderBy('order');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'purchases');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function bundleCourses()
    {
        return $this->hasMany(BundleCourse::class);
    }
}
