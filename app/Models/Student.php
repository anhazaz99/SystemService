<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'student';

    protected $fillable = [
        'full_name', 'birth_date', 'gender', 'address', 'email', 'phone', 'student_code', 'class_id'
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function account()
    {
        return $this->hasOne(StudentAccount::class, 'student_id');
    }
}
