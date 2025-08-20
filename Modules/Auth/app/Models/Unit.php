<?php

namespace Modules\Auth\app\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $table = 'faculty';

    protected $fillable = [
        'name', 'type', 'parent_id'
    ];

    protected $casts = [
        'parent_id' => 'integer'
    ];

    /**
     * Get the parent unit
     */
    public function parent()
    {
        return $this->belongsTo(Faculty::class, 'parent_id');
    }

    /**
     * Get the child units
     */
    public function children()
    {
        return $this->hasMany(Faculty::class, 'parent_id');
    }

    /**
     * Get the lecturers in this unit
     */
    public function lecturers()
    {
        return $this->hasMany(Lecturer::class, 'faculty_id');
    }

    /**
     * Get the classes in this unit (faculty)
     */
    public function classes()
    {
        return $this->hasMany(Classroom::class, 'faculty_id');
    }
}
