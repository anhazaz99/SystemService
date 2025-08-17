<?php

namespace Modules\Task\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Task\app\Models\TaskFile;

class Task extends Model
{
    use HasFactory;

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

    protected $casts = [
        'ngay_tao' => 'datetime',
    ];

    // Attribute mapping for API compatibility
    protected $appends = [
        'title',
        'description',
        'created_date',
        'assignee_id',
        'assignee_type',
        'creator_id',
        'creator_type'
    ];

    // Accessors for API compatibility
    public function getTitleAttribute()
    {
        return $this->tieu_de;
    }

    public function getDescriptionAttribute()
    {
        return $this->mo_ta;
    }

    public function getCreatedDateAttribute()
    {
        return $this->ngay_tao;
    }

    public function getAssigneeIdAttribute()
    {
        return $this->nguoi_nhan_id;
    }

    public function getAssigneeTypeAttribute()
    {
        return $this->loai_nguoi_nhan;
    }

    public function getCreatorIdAttribute()
    {
        return $this->nguoi_tao_id;
    }

    public function getCreatorTypeAttribute()
    {
        return $this->loai_nguoi_tao;
    }

    // Mutators for API compatibility
    public function setTitleAttribute($value)
    {
        $this->attributes['tieu_de'] = $value;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['mo_ta'] = $value;
    }

    public function setCreatedDateAttribute($value)
    {
        $this->attributes['ngay_tao'] = $value;
    }

    public function setAssigneeIdAttribute($value)
    {
        $this->attributes['nguoi_nhan_id'] = $value;
    }

    public function setAssigneeTypeAttribute($value)
    {
        $this->attributes['loai_nguoi_nhan'] = $value;
    }

    public function setCreatorIdAttribute($value)
    {
        $this->attributes['nguoi_tao_id'] = $value;
    }

    public function setCreatorTypeAttribute($value)
    {
        $this->attributes['loai_nguoi_tao'] = $value;
    }

    // Relationships
    public function files(): HasMany
    {
        return $this->hasMany(TaskFile::class, 'task_id');
    }

    // Accessors & Mutators
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Chờ xử lý',
            'in_progress' => 'Đang thực hiện',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    public function getPriorityTextAttribute(): string
    {
        return match($this->priority) {
            'low' => 'Thấp',
            'medium' => 'Trung bình',
            'high' => 'Cao',
            'urgent' => 'Khẩn cấp',
            default => 'Không xác định'
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        return $this->due_date->isPast() && $this->status !== 'completed';
    }
}
