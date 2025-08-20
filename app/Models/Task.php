<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $table = 'task';

    protected $fillable = [
        'title',
        'description',
        'creator_id',
        'creator_type',
        'deadline'
    ];

    public function files()
    {
        return $this->hasMany(TaskFile::class, 'task_id');
    }
}