<?php

namespace Modules\Task\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model Task - Đại diện cho bảng task trong database
 * 
 * Model này định nghĩa cấu trúc và relationships của Task
 * Tuân thủ Clean Architecture: chỉ chứa relationships và basic accessors/mutators
 */
class Task extends Model
{
    use HasFactory;
    
    /**
     * Tên bảng trong database
     */
    protected $table = 'task';
    
    /**
     * Tạo factory instance cho model để sử dụng trong testing
     * 
     * @return \Database\Factories\TaskFactory
     */
    protected static function newFactory()
    {
        return \Database\Factories\TaskFactory::new();
    }
    
    /**
     * Các trường có thể mass assign
     */
    protected $fillable = [
        'title',
        'description',
        'deadline',
        'status',
        'priority',
        'creator_id',
        'creator_type'
    ];

    /**
     * Chỉ sử dụng created_at, không có updated_at
     */
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    
    /**
     * Các trường được cast sang kiểu dữ liệu cụ thể
     */
    protected $casts = [
        'created_at' => 'datetime',
        'deadline' => 'datetime',
    ];

    /**
     * Lấy danh sách files đính kèm của task
     * 
     * @return HasMany Relationship với TaskFile
     */
    public function files(): HasMany
    {
        return $this->hasMany(TaskFile::class, 'task_id');
    }

    /**
     * Lấy danh sách calendar events liên quan đến task
     * 
     * @return HasMany Relationship với Calendar
     */
    public function calendarEvents(): HasMany
    {
        return $this->hasMany(Calendar::class, 'task_id');
    }

    /**
     * Lấy tất cả receivers của task
     * 
     * @return HasMany Relationship với TaskReceiver
     */
    public function receivers(): HasMany
    {
        return $this->hasMany(TaskReceiver::class, 'task_id');
    }

    /**
     * Lấy tất cả students nhận task này
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllStudents()
    {
        $students = collect();
        
        foreach ($this->receivers as $receiver) {
            $students = $students->merge($receiver->getActualStudents());
        }
        
        return $students->unique('id');
    }

    /**
     * Lấy tất cả lecturers nhận task này
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllLecturers()
    {
        $lecturers = collect();
        
        foreach ($this->receivers as $receiver) {
            $lecturers = $lecturers->merge($receiver->getActualLecturers());
        }
        
        return $lecturers->unique('id');
    }

    /**
     * Thêm receiver cho task
     * 
     * @param int $receiverId
     * @param string $receiverType
     * @return TaskReceiver
     */
    public function addReceiver(int $receiverId, string $receiverType): TaskReceiver
    {
        return $this->receivers()->create([
            'receiver_id' => $receiverId,
            'receiver_type' => $receiverType
        ]);
    }

    /**
     * Xóa receiver khỏi task
     * 
     * @param int $receiverId
     * @param string $receiverType
     * @return bool
     */
    public function removeReceiver(int $receiverId, string $receiverType): bool
    {
        return $this->receivers()
            ->where('receiver_id', $receiverId)
            ->where('receiver_type', $receiverType)
            ->delete();
    }

    /**
     * Kiểm tra xem một user có nhận task này không
     * 
     * @param int $userId
     * @param string $userType
     * @return bool
     */
    public function hasReceiver(int $userId, string $userType): bool
    {
        // Kiểm tra trực tiếp
        if ($this->receivers()
            ->where('receiver_id', $userId)
            ->where('receiver_type', $userType)
            ->exists()) {
            return true;
        }
        
        // Kiểm tra qua class và all_students
        foreach ($this->receivers as $receiver) {
            if ($receiver->receiver_type === 'class') {
                // Kiểm tra xem user có thuộc class này không
                if ($userType === 'student') {
                    $student = \App\Models\Student::find($userId);
                    if ($student && $student->lop_id == $receiver->receiver_id) {
                        return true;
                    }
                }
            } elseif ($receiver->receiver_type === 'all_students') {
                // Kiểm tra xem user có thuộc faculty này không
                if ($userType === 'student') {
                    $student = \App\Models\Student::find($userId);
                    if ($student && $student->class && $student->class->khoa_id == $receiver->receiver_id) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}
