<?php

namespace Modules\Task\app\Services;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Repositories\Interfaces\TaskRepositoryInterface;
use Modules\Task\app\Services\Interfaces\TaskServiceInterface;
use Modules\Task\app\DTOs\TaskDTO;
use Illuminate\Support\Facades\Log;

/**
 * Service chứa business logic cho Task
 * 
 * Service này chứa tất cả logic nghiệp vụ liên quan đến Task
 * Tuân thủ Clean Architecture: chỉ chứa business logic, không xử lý data access trực tiếp
 */
class TaskService implements TaskServiceInterface
{
    protected $taskRepository;

    /**
     * Khởi tạo service với dependency injection
     * 
     * @param TaskRepositoryInterface $taskRepository Repository xử lý data access
     */
    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Tạo mới một task với business logic
     * 
     * @param array $data Dữ liệu task cần tạo
     * @return Task Task vừa được tạo
     * @throws \Exception Nếu có lỗi trong quá trình tạo
     */
    public function createTask(array $data): Task
    {
        try {
            // Tách receivers ra khỏi data chính
            $receivers = $data['receivers'] ?? [];
            unset($data['receivers']);
            
            // Tạo task
            $task = $this->taskRepository->create($data);
            
            // Thêm receivers cho task
            $this->addReceiversToTask($task, $receivers);
            
            Log::info('Task created', [
                'task_id' => $task->id,
                'title' => $task->title,
                'creator_id' => $task->creator_id,
                'receivers_count' => count($receivers)
            ]);
            
            return $task;
        } catch (\Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cập nhật một task với business logic
     * 
     * @param Task $task Task cần cập nhật
     * @param array $data Dữ liệu cập nhật
     * @return Task Task sau khi cập nhật
     * @throws \Exception Nếu có lỗi trong quá trình cập nhật
     */
    public function updateTask(Task $task, array $data): Task
    {
        try {
            // Tách receivers ra khỏi data chính
            $receivers = $data['receivers'] ?? null;
            unset($data['receivers']);
            
            // Cập nhật task
            $task = $this->taskRepository->update($task, $data);
            
            // Cập nhật receivers nếu có
            if ($receivers !== null) {
                $this->updateReceiversForTask($task, $receivers);
            }
            
            Log::info('Task updated', [
                'task_id' => $task->id,
                'title' => $task->title,
                'receivers_updated' => $receivers !== null
            ]);
            
            return $task;
        } catch (\Exception $e) {
            Log::error('Error updating task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xóa một task với business logic
     * 
     * @param Task $task Task cần xóa
     * @return bool True nếu xóa thành công
     * @throws \Exception Nếu có lỗi trong quá trình xóa
     */
    public function deleteTask(Task $task): bool
    {
        try {
            $taskId = $task->id;
            $taskTitle = $task->title;
            
            $result = $this->taskRepository->delete($task);
            
            Log::info('Task deleted', [
                'task_id' => $taskId,
                'title' => $taskTitle
            ]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error deleting task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy task theo ID
     * 
     * @param int $id ID của task
     * @return Task|null Task nếu tìm thấy, null nếu không tìm thấy
     */
    public function getTaskById(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    /**
     * Lấy tất cả tasks với phân trang
     * 
     * @param int $perPage Số lượng task trên mỗi trang
     * @return LengthAwarePaginator Danh sách tasks đã phân trang
     */
    public function getAllTasks(int $perPage = 15)
    {
        return $this->taskRepository->getAllTasks($perPage);
    }

    /**
     * Lấy tasks với bộ lọc
     * 
     * @param array $filters Mảng chứa các điều kiện lọc
     * @param int $perPage Số lượng task trên mỗi trang
     * @return LengthAwarePaginator Danh sách tasks đã lọc và phân trang
     */
    public function getTasksWithFilters(array $filters, int $perPage = 15)
    {
        return $this->taskRepository->getTasksWithFilters($filters, $perPage);
    }

    /**
     * Lấy tasks theo người nhận
     * 
     * @param int $receiverId ID người nhận
     * @param string $receiverType Loại người nhận
     * @return mixed Danh sách tasks của người nhận
     */
    public function getTasksByReceiver(int $receiverId, string $receiverType)
    {
        return $this->taskRepository->getTasksByReceiver($receiverId, $receiverType);
    }

    /**
     * Lấy tasks theo người tạo
     * 
     * @param int $creatorId ID người tạo
     * @param string $creatorType Loại người tạo
     * @return mixed Danh sách tasks của người tạo
     */
    public function getTasksByCreator(int $creatorId, string $creatorType)
    {
        return $this->taskRepository->getTasksByCreator($creatorId, $creatorType);
    }

    /**
     * Lấy thống kê task
     * 
     * @return array Thống kê về tasks
     */
    public function getTaskStatistics(): array
    {
        return $this->taskRepository->getTaskStatistics();
    }

    /**
     * Thêm receivers cho task
     * 
     * @param Task $task
     * @param array $receivers
     */
    private function addReceiversToTask(Task $task, array $receivers): void
    {
        foreach ($receivers as $receiver) {
            $task->addReceiver($receiver['receiver_id'], $receiver['receiver_type']);
        }
    }

    /**
     * Cập nhật receivers cho task
     * 
     * @param Task $task
     * @param array $receivers
     */
    private function updateReceiversForTask(Task $task, array $receivers): void
    {
        // Xóa tất cả receivers cũ
        $task->receivers()->delete();
        
        // Thêm receivers mới
        $this->addReceiversToTask($task, $receivers);
    }

    /**
     * Lấy tasks cho một user cụ thể
     * 
     * @param int $userId
     * @param string $userType
     * @return mixed
     */
    public function getTasksForUser(int $userId, string $userType)
    {
        return $this->taskRepository->getTasksForUser($userId, $userType);
    }

    /**
     * Lấy danh sách tasks cho người dùng hiện tại
     * 
     * @param mixed $user User hiện tại
     * @param int $perPage Số lượng items per page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTasksForCurrentUser($user, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Kiểm tra user có tồn tại không
        if (!$user || !isset($user->id)) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }
        
        $userType = $this->getUserType($user);
        $userId = $user->id;
        
        return $this->taskRepository->getTasksForUser($userId, $userType, $perPage);
    }

    /**
     * Lấy danh sách tasks đã tạo bởi người dùng
     * 
     * @param mixed $user User hiện tại
     * @param int $perPage Số lượng items per page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTasksCreatedByUser($user, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Kiểm tra user có tồn tại không
        if (!$user || !isset($user->id)) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }
        
        $userType = $this->getUserType($user);
        $userId = $user->id;
        
        return $this->taskRepository->getTasksCreatedByUser($userId, $userType, $perPage);
    }

    /**
     * Kiểm tra quyền cập nhật trạng thái task
     * 
     * @param mixed $user User hiện tại
     * @param Task $task Task cần kiểm tra
     * @return bool
     */
    public function canUpdateTaskStatus($user, Task $task): bool
    {
        // Kiểm tra user có tồn tại không
        if (!$user || !isset($user->id)) {
            return false;
        }
        
        // Kiểm tra xem user có phải là người nhận task không
        $userType = $this->getUserType($user);
        $userId = $user->id;
        
        return $this->taskRepository->isTaskReceiver($task, $userId, $userType);
    }

    /**
     * Cập nhật trạng thái task
     * 
     * @param Task $task Task cần cập nhật
     * @param string $status Trạng thái mới
     * @return Task
     */
    public function updateTaskStatus(Task $task, string $status): Task
    {
        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Trạng thái không hợp lệ');
        }
        
        return $this->taskRepository->update($task, ['status' => $status]);
    }

    /**
     * Kiểm tra quyền upload files cho task
     * 
     * @param mixed $user User hiện tại
     * @param Task $task Task cần kiểm tra
     * @return bool
     */
    public function canUploadFiles($user, Task $task): bool
    {
        // Kiểm tra xem user có phải là người nhận task không
        return $this->canUpdateTaskStatus($user, $task);
    }

    /**
     * Upload files cho task
     * 
     * @param Task $task Task cần upload files
     * @param array $files Files cần upload
     * @return array
     */
    public function uploadTaskFiles(Task $task, array $files): array
    {
        $uploadedFiles = [];
        
        foreach ($files as $file) {
            $fileData = [
                'task_id' => $task->id,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'path' => $file->store('task-files/' . $task->id),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now()
            ];
            
            $uploadedFile = $this->taskRepository->createTaskFile($fileData);
            $uploadedFiles[] = $uploadedFile;
        }
        
        return $uploadedFiles;
    }

    /**
     * Kiểm tra quyền xóa file
     * 
     * @param mixed $user User hiện tại
     * @param Task $task Task chứa file
     * @param int $fileId ID của file
     * @return bool
     */
    public function canDeleteFile($user, Task $task, int $fileId): bool
    {
        $file = $this->taskRepository->findTaskFile($fileId);
        
        if (!$file) {
            return false;
        }
        
        // Kiểm tra xem user có phải là người upload file không
        return $file->uploaded_by == $user->id;
    }

    /**
     * Xóa file của task
     * 
     * @param Task $task Task chứa file
     * @param int $fileId ID của file
     * @return bool
     */
    public function deleteTaskFile(Task $task, int $fileId): bool
    {
        return $this->taskRepository->deleteTaskFile($fileId);
    }

    /**
     * Lấy thống kê tasks của người dùng
     * 
     * @param mixed $user User hiện tại
     * @return array
     */
    public function getUserTaskStatistics($user): array
    {
        // Kiểm tra user có tồn tại không
        if (!$user || !isset($user->id)) {
            return [
                'total' => 0,
                'pending' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
        }
        
        $userType = $this->getUserType($user);
        $userId = $user->id;
        
        return $this->taskRepository->getUserTaskStatistics($userId, $userType);
    }

    /**
     * Lấy thống kê tasks đã tạo
     * 
     * @param mixed $user User hiện tại
     * @return array
     */
    public function getCreatedTaskStatistics($user): array
    {
        // Kiểm tra user có tồn tại không
        if (!$user || !isset($user->id)) {
            return [
                'total' => 0,
                'pending' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
        }
        
        $userType = $this->getUserType($user);
        $userId = $user->id;
        
        return $this->taskRepository->getCreatedTaskStatistics($userId, $userType);
    }

    /**
     * Lấy thống kê tổng quan (admin)
     * 
     * @return array
     */
    public function getOverviewTaskStatistics(): array
    {
        return $this->taskRepository->getOverviewTaskStatistics();
    }

    /**
     * Kiểm tra quyền gán task
     * 
     * @param mixed $user User hiện tại
     * @param Task $task Task cần gán
     * @return bool
     */
    public function canAssignTask($user, Task $task): bool
    {
        // Kiểm tra user có tồn tại không
        if (!$user || !isset($user->id)) {
            return false;
        }
        
        // Chỉ người tạo task mới được gán
        $userType = $this->getUserType($user);
        $userId = $user->id;
        
        return $task->creator_id == $userId && $task->creator_type == $userType;
    }

    /**
     * Gán task cho receiver
     * 
     * @param Task $task Task cần gán
     * @param int $receiverId ID của receiver
     * @param string $receiverType Loại receiver
     * @return Task
     */
    public function assignTaskToReceiver(Task $task, int $receiverId, string $receiverType): Task
    {
        $receiverData = [
            'task_id' => $task->id,
            'receiver_id' => $receiverId,
            'receiver_type' => $receiverType,
            'assigned_at' => now()
        ];
        
        $this->taskRepository->addReceiverToTask($receiverData);
        
        return $task->fresh();
    }

    /**
     * Kiểm tra quyền thu hồi task
     * 
     * @param mixed $user User hiện tại
     * @param Task $task Task cần thu hồi
     * @return bool
     */
    public function canRevokeTask($user, Task $task): bool
    {
        // Chỉ người tạo task mới được thu hồi
        return $this->canAssignTask($user, $task);
    }

    /**
     * Thu hồi task
     * 
     * @param Task $task Task cần thu hồi
     * @return bool
     */
    public function revokeTask(Task $task): bool
    {
        return $this->taskRepository->deleteAllTaskReceivers($task->id);
    }

    /**
     * Tạo tasks định kỳ
     * 
     * @param array $data Dữ liệu task
     * @param mixed $user User tạo task
     * @return array
     */
    public function createRecurringTasks(array $data, $user): array
    {
        $recurringTasks = [];
        $pattern = $data['recurring_pattern'];
        $endDate = \Carbon\Carbon::parse($data['recurring_end_date']);
        $currentDate = \Carbon\Carbon::parse($data['deadline']);
        
        while ($currentDate <= $endDate) {
            $taskData = $data;
            $taskData['deadline'] = $currentDate->format('Y-m-d H:i:s');
            $taskData['creator_id'] = $user->id;
            $taskData['creator_type'] = $this->getUserType($user);
            
            $task = $this->createTask($taskData);
            $recurringTasks[] = $task;
            
            // Tăng ngày theo pattern
            switch ($pattern) {
                case 'daily':
                    $currentDate->addDay();
                    break;
                case 'weekly':
                    $currentDate->addWeek();
                    break;
                case 'monthly':
                    $currentDate->addMonth();
                    break;
            }
        }
        
        return $recurringTasks;
    }

    /**
     * Xóa task vĩnh viễn (admin)
     * 
     * @param Task $task Task cần xóa
     * @return bool
     */
    public function forceDeleteTask(Task $task): bool
    {
        return $this->taskRepository->forceDelete($task);
    }

    /**
     * Khôi phục task đã xóa (admin)
     * 
     * @param Task $task Task cần khôi phục
     * @return bool
     */
    public function restoreTask(Task $task): bool
    {
        return $this->taskRepository->restore($task);
    }

    /**
     * Lấy loại user từ model
     * 
     * @param mixed $user User object
     * @return string
     */
    protected function getUserType($user): string
    {
        // Kiểm tra nếu user là admin (có is_admin = true)
        if (isset($user->account) && isset($user->account['is_admin']) && $user->account['is_admin']) {
            return 'admin';
        }
        
        // Nếu user có user_type property (từ JWT)
        if (isset($user->user_type)) {
            return $user->user_type;
        }
        
        // Kiểm tra instance của model
        if ($user instanceof \Modules\Auth\app\Models\Lecturer) {
            return 'lecturer';
        } elseif ($user instanceof \Modules\Auth\app\Models\Student) {
            return 'student';
        } elseif ($user instanceof \Modules\Auth\app\Models\LecturerAccount) {
            return 'lecturer';
        } elseif ($user instanceof \Modules\Auth\app\Models\StudentAccount) {
            return 'student';
        }
        
        return 'unknown';
    }

    /**
     * Kiểm tra quyền tạo task cho receiver
     * 
     * @param mixed $user User tạo task
     * @param array $taskData Dữ liệu task
     * @return bool
     */
    public function canCreateTaskForReceiver($user, array $taskData): bool
    {
        $userType = $this->getUserType($user);
        
        // Admin có quyền tạo task cho tất cả
        if ($userType === 'admin') {
            return true;
        }
        
        // Lecturer chỉ có quyền tạo task cho student, class, all_students
        if ($userType === 'lecturer') {
            $receiverType = $taskData['receivers'][0]['receiver_type'] ?? null;
            return in_array($receiverType, ['student', 'class', 'all_students']);
        }
        
        // Student không có quyền tạo task
        return false;
    }

    /**
     * Lấy danh sách faculties cho user
     * 
     * @param mixed $user User hiện tại
     * @return array
     */
    public function getFacultiesForUser($user): array
    {
        $userType = $this->getUserType($user);
        
        // Admin có thể xem tất cả faculties
        if ($userType === 'admin') {
            return \App\Models\Faculty::all()->toArray();
        }
        
        // Lecturer chỉ có thể xem faculty của mình
        if ($userType === 'lecturer') {
            $lecturer = \App\Models\Lecturer::find($user->id);
            if ($lecturer && $lecturer->faculty_id) {
                $faculty = \App\Models\Faculty::find($lecturer->faculty_id);
                if ($faculty) {
                    return [$faculty->toArray()];
                }
            }
        }
        
        return [];
    }

    /**
     * Lấy danh sách classes theo faculty cho user
     * 
     * @param mixed $user User hiện tại
     * @param int $facultyId ID của faculty
     * @return array
     */
    public function getClassesByFacultyForUser($user, int $facultyId): array
    {
        $userType = $this->getUserType($user);
        
        // Admin có thể xem tất cả classes
        if ($userType === 'admin') {
            return \App\Models\Classroom::where('faculty_id', $facultyId)->get()->toArray();
        }
        
        // Lecturer chỉ có thể xem classes thuộc faculty của mình
        if ($userType === 'lecturer') {
            $lecturer = \App\Models\Lecturer::find($user->id);
            if ($lecturer && $lecturer->faculty_id == $facultyId) {
                return \App\Models\Classroom::where('faculty_id', $facultyId)->get()->toArray();
            }
        }
        
        return [];
    }

    /**
     * Lấy danh sách students theo class cho user
     * 
     * @param mixed $user User hiện tại
     * @param int $classId ID của class
     * @return array
     */
    public function getStudentsByClassForUser($user, int $classId): array
    {
        $userType = $this->getUserType($user);
        
        // Admin có thể xem tất cả students
        if ($userType === 'admin') {
            return \App\Models\Student::where('class_id', $classId)->get()->toArray();
        }
        
        // Lecturer chỉ có thể xem students thuộc faculty của mình
        if ($userType === 'lecturer') {
            $lecturer = \App\Models\Lecturer::find($user->id);
            if ($lecturer && $lecturer->faculty_id) {
                $class = \App\Models\Classroom::where('id', $classId)
                    ->where('faculty_id', $lecturer->faculty_id)
                    ->first();
                
                if ($class) {
                    return \App\Models\Student::where('class_id', $classId)->get()->toArray();
                }
            }
        }
        
        return [];
    }

    /**
     * Lấy danh sách lecturers cho user
     * 
     * @param mixed $user User hiện tại
     * @return array
     */
    public function getLecturersForUser($user): array
    {
        $userType = $this->getUserType($user);
        
        // Chỉ admin mới có thể xem danh sách lecturers
        if ($userType === 'admin') {
            return \App\Models\Lecturer::with('faculty')->get()->toArray();
        }
        
        return [];
    }

    /**
     * Lấy danh sách tất cả students cho user
     * 
     * @param mixed $user User hiện tại
     * @return array
     */
    public function getAllStudentsForUser($user): array
    {
        $userType = $this->getUserType($user);
        
        // Admin có thể xem tất cả students
        if ($userType === 'admin') {
            return \App\Models\Student::with('classroom')->get()->toArray();
        }
        
        // Lecturer chỉ có thể xem students thuộc faculty của mình
        if ($userType === 'lecturer') {
            $lecturer = \App\Models\Lecturer::find($user->id);
            if ($lecturer && $lecturer->faculty_id) {
                return \App\Models\Student::whereHas('classroom', function($query) use ($lecturer) {
                    $query->where('faculty_id', $lecturer->faculty_id);
                })->with('classroom')->get()->toArray();
            }
        }
        
        return [];
    }
}
