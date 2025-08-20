<?php

namespace Modules\Auth\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Lecturer extends Model
{
    use Notifiable;

    protected $table = 'lecturer';

    protected $fillable = [
        'full_name', 'gender', 'address', 'email', 'phone', 'lecturer_code', 'faculty_id'
    ];

    protected $casts = [
        'faculty_id' => 'integer'
    ];

    /**
     * Get the faculty this lecturer belongs to
     */
    public function faculty()
    {
        return $this->belongsTo(\App\Models\Faculty::class, 'faculty_id');
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

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->email;
    }
}
