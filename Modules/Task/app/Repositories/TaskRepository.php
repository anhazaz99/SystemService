<?php

namespace Modules\Task\app\Repositories;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Repository xử lý data access cho Task
 * 
 * Repository này chứa tất cả logic truy cập database liên quan đến Task
 * Tuân thủ Clean Architecture: chỉ xử lý data access, không chứa business logic
 */
class TaskRepository implements TaskRepositoryInterface
{
    /**
     * Tìm task theo ID
     * 
     * @param int $id ID của task cần tìm
     * @return Task|null Task nếu tìm thấy, null nếu không tìm thấy
     */
    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }

    /**
     * Lấy tất cả tasks với phân trang
     * 
     * @param int $perPage Số lượng task trên mỗi trang
     * @return LengthAwarePaginator Danh sách tasks đã phân trang
     */
    public function getAllTasks(int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['files', 'calendarEvents'])->paginate($perPage);
    }

    /**
     * Lấy tasks theo người nhận (cũ - deprecated)
     * 
     * @param int $receiverId ID của người nhận
     * @param string $receiverType Loại người nhận (lecturer/student)
     * @return Collection Danh sách tasks của người nhận
     */
    public function getTasksByReceiver(int $receiverId, string $receiverType): Collection
    {
        return $this->getTasksForUser($receiverId, $receiverType);
    }



    /**
     * Lấy tasks theo người tạo
     * 
     * @param int $creatorId ID của người tạo
     * @param string $creatorType Loại người tạo (lecturer/student)
     * @return Collection Danh sách tasks của người tạo
     */
    public function getTasksByCreator(int $creatorId, string $creatorType): Collection
    {
        return Task::where('creator_id', $creatorId)
                  ->where('creator_type', $creatorType)
                  ->with(['files', 'calendarEvents'])
                  ->get();
    }

    /**
     * Lấy tasks với bộ lọc
     * 
     * @param array $filters Mảng chứa các điều kiện lọc
     * @param int $perPage Số lượng task trên mỗi trang
     * @return LengthAwarePaginator Danh sách tasks đã lọc và phân trang
     */
    public function getTasksWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Task::with(['files', 'calendarEvents']);

        if (isset($filters['receiver_id'])) {
            $query->where('receiver_id', $filters['receiver_id']);
        }

        if (isset($filters['receiver_type'])) {
            $query->where('receiver_type', $filters['receiver_type']);
        }

        if (isset($filters['creator_id'])) {
            $query->where('creator_id', $filters['creator_id']);
        }

        if (isset($filters['creator_type'])) {
            $query->where('creator_type', $filters['creator_type']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Tạo mới một task
     * 
     * @param array $data Dữ liệu task cần tạo
     * @return Task Task vừa được tạo
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Cập nhật một task
     * 
     * @param Task $task Task cần cập nhật
     * @param array $data Dữ liệu cập nhật
     * @return Task Task sau khi cập nhật
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    /**
     * Xóa một task
     * 
     * @param Task $task Task cần xóa
     * @return bool True nếu xóa thành công
     */
    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Lấy tasks có files.
     */
    public function getTasksWithFiles(): Collection
    {
        return Task::with('files')->get();
    }

    /**
     * Lấy tasks có calendar events.
     */
    public function getTasksWithCalendarEvents(): Collection
    {
        return Task::with('calendarEvents')->get();
    }

    /**
     * Lấy thống kê task.
     */
    public function getTaskStatistics(): array
    {
        $totalTasks = Task::count();
        
        // Thống kê theo receiver type từ bảng task_receivers
        $tasksByReceiverType = \DB::table('task_receivers')
            ->selectRaw('receiver_type, count(*) as count')
            ->groupBy('receiver_type')
            ->get()
            ->pluck('count', 'receiver_type')
            ->toArray();

        $tasksByCreatorType = Task::selectRaw('creator_type, count(*) as count')
                                 ->groupBy('creator_type')
                                 ->get()
                                 ->pluck('count', 'creator_type')
                                 ->toArray();

        // Thống kê theo status
        $tasksByStatus = Task::selectRaw('status, count(*) as count')
                            ->groupBy('status')
                            ->get()
                            ->pluck('count', 'status')
                            ->toArray();

        // Thống kê theo priority
        $tasksByPriority = Task::selectRaw('priority, count(*) as count')
                              ->groupBy('priority')
                              ->get()
                              ->pluck('count', 'priority')
                              ->toArray();

        return [
            'total_tasks' => $totalTasks,
            'tasks_by_receiver_type' => $tasksByReceiverType,
            'tasks_by_creator_type' => $tasksByCreatorType,
            'tasks_by_status' => $tasksByStatus,
            'tasks_by_priority' => $tasksByPriority,
        ];
    }

    /**
     * Lấy tasks cho user hiện tại với phân trang
     * 
     * @param int $userId ID của user
     * @param string $userType Loại user
     * @param int $perPage Số lượng items per page
     * @return LengthAwarePaginator
     */
    public function getTasksForUser(int $userId, string $userType, int $perPage = 15): LengthAwarePaginator
    {
        \Log::info("getTasksForUser called with userId: $userId, userType: $userType");
        
        $query = Task::with(['files', 'calendarEvents', 'receivers']);

        // Tìm tasks có receiver trực tiếp
        $query->whereHas('receivers', function ($q) use ($userId, $userType) {
            $q->where('receiver_id', $userId)
              ->where('receiver_type', $userType);
        });

        // Nếu user là student, cũng tìm tasks có class hoặc all_students
        if ($userType === 'student') {
            $student = \Modules\Auth\app\Models\Student::find($userId);
            if ($student) {
                $query->orWhereHas('receivers', function ($q) use ($student) {
                    $q->where('receiver_type', 'class')
                      ->where('receiver_id', $student->class_id);
                });

                $query->orWhereHas('receivers', function ($q) {
                    $q->where('receiver_type', 'all_students');
                });
            }
        }

        $result = $query->orderBy('created_at', 'desc')->paginate($perPage);
        \Log::info("getTasksForUser result: " . $result->count() . " tasks found");
        
        return $result;
    }

    /**
     * Lấy tasks đã tạo bởi user với phân trang
     * 
     * @param int $userId ID của user
     * @param string $userType Loại user
     * @param int $perPage Số lượng items per page
     * @return LengthAwarePaginator
     */
    public function getTasksCreatedByUser(int $userId, string $userType, int $perPage = 15): LengthAwarePaginator
    {
        \Log::info("getTasksCreatedByUser called with userId: $userId, userType: $userType");
        
        $result = Task::where('creator_id', $userId)
                  ->where('creator_type', $userType)
                  ->with(['files', 'calendarEvents', 'receivers'])
                  ->orderBy('created_at', 'desc')
                  ->paginate($perPage);
        
        \Log::info("getTasksCreatedByUser result: " . $result->count() . " tasks found");
        
        return $result;
    }

    /**
     * Kiểm tra xem user có phải là receiver của task không
     * 
     * @param Task $task Task cần kiểm tra
     * @param int $userId ID của user
     * @param string $userType Loại user
     * @return bool
     */
    public function isTaskReceiver(Task $task, int $userId, string $userType): bool
    {
        $isDirectReceiver = $task->receivers()
            ->where('receiver_id', $userId)
            ->where('receiver_type', $userType)
            ->exists();

        if ($isDirectReceiver) {
            return true;
        }

        // Nếu user là student, kiểm tra thêm class và all_students
        if ($userType === 'student') {
            $student = \Modules\Auth\app\Models\Student::find($userId);
            if ($student) {
                $isClassReceiver = $task->receivers()
                    ->where('receiver_type', 'class')
                    ->where('receiver_id', $student->class_id)
                    ->exists();

                $isAllStudentsReceiver = $task->receivers()
                    ->where('receiver_type', 'all_students')
                    ->exists();

                return $isClassReceiver || $isAllStudentsReceiver;
            }
        }

        return false;
    }

    /**
     * Lấy tất cả tasks với filters (admin)
     * 
     * @param array $filters Các bộ lọc
     * @param int $perPage Số lượng items per page
     * @return LengthAwarePaginator
     */
    public function getAllTasksWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Task::with(['files', 'calendarEvents', 'receivers']);

        // Áp dụng các bộ lọc
        if (isset($filters['receiver_id'])) {
            $query->whereHas('receivers', function ($q) use ($filters) {
                $q->where('receiver_id', $filters['receiver_id']);
                if (isset($filters['receiver_type'])) {
                    $q->where('receiver_type', $filters['receiver_type']);
                }
            });
        }

        if (isset($filters['creator_id'])) {
            $query->where('creator_id', $filters['creator_id']);
            if (isset($filters['creator_type'])) {
                $query->where('creator_type', $filters['creator_type']);
            }
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Tạo task file
     * 
     * @param array $fileData Dữ liệu file
     * @return mixed
     */
    public function createTaskFile(array $fileData): mixed
    {
        return \Modules\Task\app\Models\TaskFile::create($fileData);
    }

    /**
     * Tìm task file theo ID
     * 
     * @param int $fileId ID của file
     * @return mixed
     */
    public function findTaskFile(int $fileId): mixed
    {
        return \Modules\Task\app\Models\TaskFile::find($fileId);
    }

    /**
     * Xóa task file
     * 
     * @param int $fileId ID của file
     * @return bool
     */
    public function deleteTaskFile(int $fileId): bool
    {
        $file = \Modules\Task\app\Models\TaskFile::find($fileId);
        if ($file) {
            return $file->delete();
        }
        return false;
    }

    /**
     * Lấy thống kê tasks của user
     * 
     * @param int $userId ID của user
     * @param string $userType Loại user
     * @return array
     */
    public function getUserTaskStatistics(int $userId, string $userType): array
    {
        $tasks = $this->getTasksForUser($userId, $userType);

        return [
            'total' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'cancelled' => $tasks->where('status', 'cancelled')->count(),
            'overdue' => $tasks->where('deadline', '<', now())->whereNotIn('status', ['completed', 'cancelled'])->count()
        ];
    }

    /**
     * Lấy thống kê tasks đã tạo
     * 
     * @param int $userId ID của user
     * @param string $userType Loại user
     * @return array
     */
    public function getCreatedTaskStatistics(int $userId, string $userType): array
    {
        $tasks = Task::where('creator_id', $userId)
                    ->where('creator_type', $userType)
                    ->get();

        return [
            'total_created' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'cancelled' => $tasks->where('status', 'cancelled')->count(),
            'overdue' => $tasks->where('deadline', '<', now())->whereNotIn('status', ['completed', 'cancelled'])->count()
        ];
    }

    /**
     * Lấy thống kê tổng quan (admin)
     * 
     * @return array
     */
    public function getOverviewTaskStatistics(): array
    {
        $totalTasks = Task::count();
        $totalLecturers = \Modules\Auth\app\Models\Lecturer::count();
        $totalStudents = \Modules\Auth\app\Models\Student::count();

        return [
            'total_tasks' => $totalTasks,
            'total_lecturers' => $totalLecturers,
            'total_students' => $totalStudents,
            'tasks_by_status' => [
                'pending' => Task::where('status', 'pending')->count(),
                'in_progress' => Task::where('status', 'in_progress')->count(),
                'completed' => Task::where('status', 'completed')->count(),
                'cancelled' => Task::where('status', 'cancelled')->count(),
            ],
            'overdue_tasks' => Task::where('deadline', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'tasks_this_month' => Task::whereMonth('created_at', now()->month)->count(),
            'tasks_this_week' => Task::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
        ];
    }

    /**
     * Thêm receiver cho task
     * 
     * @param array $receiverData Dữ liệu receiver
     * @return void
     */
    public function addReceiverToTask(array $receiverData): void
    {
        \Modules\Task\app\Models\TaskReceiver::create($receiverData);
    }

    /**
     * Xóa tất cả receivers của task
     * 
     * @param int $taskId ID của task
     * @return bool
     */
    public function deleteAllTaskReceivers(int $taskId): bool
    {
        return \Modules\Task\app\Models\TaskReceiver::where('task_id', $taskId)->delete();
    }

    /**
     * Xóa task vĩnh viễn
     * 
     * @param Task $task Task cần xóa
     * @return bool
     */
    public function forceDelete(Task $task): bool
    {
        return $task->forceDelete();
    }

    /**
     * Khôi phục task đã xóa
     * 
     * @param Task $task Task cần khôi phục
     * @return bool
     */
    public function restore(Task $task): bool
    {
        return $task->restore();
    }
}
