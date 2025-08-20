<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $table = 'class';

    protected $fillable = [
        'class_name', 'class_code', 'faculty_id', 'lecturer_id', 'school_year'
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
