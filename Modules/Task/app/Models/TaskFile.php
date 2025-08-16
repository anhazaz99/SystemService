<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Task\Database\Factories\TaskFileFactory;

class TaskFile extends Model
{
    use HasFactory;
    protected $table = 'task_file';

    protected $fillable = [
        'task_id',
        'file_path'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
