<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'module_id',
        'title',
        'description',
        'opening_time',
        'source',
        'link',
    ];

    protected $appends = [
        'is_published',
        'download_link',
    ];

    public function getIsPublishedAttribute()
    {
        return $this->opening_time && $this->opening_time <= now();
    }

    public function getDownloadLinkAttribute()
    {
        if (!$this->link) {
            return null;
        }

        // Extract file ID from the Google Drive link
        preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $this->link, $matches);

        if (!isset($matches[1])) {
            return null;
        }

        $fileId = $matches[1];

        // Generate the download link
        return "https://drive.google.com/uc?export=download&id={$fileId}";
    }

    public function scopePublished($query)
    {
        return $query->where('opening_time', '<=', now());
    }    

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
