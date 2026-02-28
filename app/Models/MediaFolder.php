<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFolder extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id', 'type', 'event_id', 'description', 'show_on_frontend', 'cover_image', 'sort_order'];

    public function parent()
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MediaFolder::class, 'parent_id')->orderBy('sort_order');
    }

    public function files()
    {
        return $this->hasMany(MediaFile::class, 'folder_id')->orderBy('created_at', 'desc');
    }
}
