<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    protected $table = 'lecturer';

    protected $fillable = [
        'full_name', 'gender', 'address', 'email', 'phone', 'lecturer_code', 'faculty_id'
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function account()
    {
        return $this->hasOne(LecturerAccount::class, 'lecturer_id');
    }

    public function classes()
    {
        return $this->hasMany(Classroom::class, 'lecturer_id');
    }
}
