<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Task\Database\Factories\TaskFactory;

class Task extends Model
{
    
    protected $table = 'task';

    protected $fillable = [
        'tieu_de',
        'mo_ta',
        'ngay_tao',
        'nguoi_nhan_id',
        'loai_nguoi_nhan',
        'nguoi_tao_id',
        'loai_nguoi_tao'
    ];

    public function files()
    {
        return $this->hasMany(TaskFile::class, 'task_id');
    }
}
