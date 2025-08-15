<?php

namespace Modules\Auth\app\Models;

use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    protected $table = 'lecturer';

    protected $fillable = [
        'full_name', 'gender', 'address', 'email', 'phone', 'lecturer_code', 'unit_id'
    ];

    protected $casts = [
        'unit_id' => 'integer'
    ];

    /**
     * Get the unit this lecturer belongs to
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get the account for this lecturer
     */
    public function account()
    {
        return $this->hasOne(LecturerAccount::class, 'lecturer_id');
    }

    /**
     * Get the classes this lecturer teaches
     */
    public function classes()
    {
        return $this->hasMany(Classroom::class, 'lecturer_id');
    }
}
