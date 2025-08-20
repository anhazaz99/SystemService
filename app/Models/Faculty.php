<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    protected $table = 'faculty';

    protected $fillable = [
        'name',
        'type',
        'parent_id'
    ];

    /**
     * Lấy faculty cha
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'parent_id');
    }

    /**
     * Lấy các faculty con
     */
    public function children(): HasMany
    {
        return $this->hasMany(Faculty::class, 'parent_id');
    }

    /**
     * Lấy các lecturer thuộc faculty này
     */
    public function lecturers(): HasMany
    {
        return $this->hasMany(Lecturer::class, 'faculty_id');
    }

    /**
     * Lấy các class thuộc faculty này
     */
    public function classes(): HasMany
    {
        return $this->hasMany(Classroom::class, 'faculty_id');
    }
}
