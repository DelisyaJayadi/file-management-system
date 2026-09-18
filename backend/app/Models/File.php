<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    protected $fillable = ['name', 'title', 'original_name', 'path', 'mime_type', 'size', 'folder_id', 'department_id', 'user_id'];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Tambahkan relasi department ini
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}