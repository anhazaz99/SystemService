<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Task\Database\Factories\TaskFileFactory;

class TaskFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): TaskFileFactory
    // {
    //     // return TaskFileFactory::new();
    // }
}
