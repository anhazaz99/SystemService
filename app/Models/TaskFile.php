<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskFile extends Model
{
    use HasFactory;

    protected $table = 'task_file';

    protected $fillable = [
        'task_id',
        'file_path'
    ];

    // Attribute mapping for API compatibility
    protected $appends = [
        'file_name',
        'file_size',
        'file_type'
    ];

    // Accessors for API compatibility
    public function getFileNameAttribute()
    {
        return basename($this->file_path);
    }

    public function getFileSizeAttribute()
    {
        if (file_exists(storage_path('app/public/' . $this->file_path))) {
            return filesize(storage_path('app/public/' . $this->file_path));
        }
        return 0;
    }

    public function getFileTypeAttribute()
    {
        return mime_content_type(storage_path('app/public/' . $this->file_path)) ?? 'application/octet-stream';
    }

    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    // Accessors & Mutators
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
