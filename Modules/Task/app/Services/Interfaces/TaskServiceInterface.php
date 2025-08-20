<?php

namespace Modules\Task\app\Services\Interfaces;

use Modules\Task\app\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface cho TaskService
 * 
 * Tuân thủ Clean Architecture: Dependency Inversion Principle
 * Controller phụ thuộc vào abstraction, không phụ thuộc vào concrete implementation
 */
interface TaskServiceInterface
{
    /**
     * Tạo task mới
     */
    public function createTask(array $data): Task;

    /**
     * Cập nhật task
     */
    public function updateTask(Task $task, array $data): Task;

    /**
     * Xóa task
     */
    public function deleteTask(Task $task): bool;

    /**
     * Lấy tasks cho user hiện tại
     */
    public function getTasksForCurrentUser($user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Lấy tasks đã tạo bởi user
     */
    public function getTasksCreatedByUser($user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Kiểm tra quyền cập nhật trạng thái task
     */
    public function canUpdateTaskStatus($user, Task $task): bool;

    /**
     * Cập nhật trạng thái task
     */
    public function updateTaskStatus(Task $task, string $status): Task;

    /**
     * Kiểm tra quyền upload files
     */
    public function canUploadFiles($user, Task $task): bool;

    /**
     * Upload files cho task
     */
    public function uploadTaskFiles(Task $task, array $files): array;

    /**
     * Kiểm tra quyền xóa file
     */
    public function canDeleteFile($user, Task $task, int $fileId): bool;

    /**
     * Xóa file của task
     */
    public function deleteTaskFile(Task $task, int $fileId): bool;

    /**
     * Lấy thống kê tasks của user
     */
    public function getUserTaskStatistics($user): array;

    /**
     * Lấy thống kê tasks đã tạo
     */
    public function getCreatedTaskStatistics($user): array;

    /**
     * Lấy thống kê tổng quan
     */
    public function getOverviewTaskStatistics(): array;

    /**
     * Kiểm tra quyền gán task
     */
    public function canAssignTask($user, Task $task): bool;

    /**
     * Gán task cho receiver
     */
    public function assignTaskToReceiver(Task $task, int $receiverId, string $receiverType): Task;

    /**
     * Kiểm tra quyền thu hồi task
     */
    public function canRevokeTask($user, Task $task): bool;

    /**
     * Thu hồi task
     */
    public function revokeTask(Task $task): bool;

    /**
     * Tạo tasks định kỳ
     */
    public function createRecurringTasks(array $data, $user): array;

    /**
     * Force delete task
     */
    public function forceDeleteTask(Task $task): bool;

    /**
     * Restore task
     */
    public function restoreTask(Task $task): bool;
}
