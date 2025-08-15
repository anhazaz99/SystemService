<?php

namespace Modules\Auth\app\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'unit';

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
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    /**
     * Get the child units
     */
    public function children()
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }

    /**
     * Get the lecturers in this unit
     */
    public function lecturers()
    {
        return $this->hasMany(Lecturer::class, 'unit_id');
    }

    /**
     * Get the classes in this unit (faculty)
     */
    public function classes()
    {
        return $this->hasMany(Classroom::class, 'faculty_id');
    }
}
