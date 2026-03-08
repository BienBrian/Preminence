<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'folder_id', 'filename', 'original_name',
        'path', 'mime_type', 'size', 'alt_text', 'uploaded_by',
    ];

    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
