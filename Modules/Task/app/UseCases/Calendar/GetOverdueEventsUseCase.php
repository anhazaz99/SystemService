<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Lấy events quá hạn
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetOverdueEventsUseCase
{
    /**
     * Thực hiện lấy overdue events
     * 
     * @param object $user User object
     * @return array Danh sách overdue events
     * @throws TaskException Nếu có lỗi
     */
    public function execute(object $user): array
    {
        try {
            // Validate user
            $this->validateUser($user);
            
            // Lấy overdue tasks
            $tasks = $this->getOverdueTasks($user);
            
            // Chuyển đổi thành events
            $events = $this->transformTasksToEvents($tasks);
            
            // Log success
            Log::info('Overdue events retrieved successfully via UseCase', [
                'user_id' => $user->id,
                'events_count' => count($events)
            ]);
            
            return $events;
        } catch (\Exception $e) {
            Log::error('Error retrieving overdue events via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate user
     * 
     * @param object $user User object
     * @throws TaskException Nếu user không hợp lệ
     */
    private function validateUser(object $user): void
    {
        if (!$user || !isset($user->id)) {
            throw TaskException::businessRuleViolation(
                'User not authenticated',
                ['user' => $user]
            );
        }
    }

    /**
     * Lấy overdue tasks
     * 
     * @param object $user User object
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getOverdueTasks(object $user): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('deadline', '<', now())
            ->where('status', '!=', 'completed')
            ->whereHas('receivers', function ($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->where('receiver_type', $this->getUserType($user));
            })
            ->with(['receivers'])
            ->orderBy('deadline')
            ->get();
    }

    /**
     * Chuyển đổi tasks thành events
     * 
     * @param \Illuminate\Database\Eloquent\Collection $tasks Tasks collection
     * @return array Events array
     */
    private function transformTasksToEvents($tasks): array
    {
        return $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'start_time' => $task->deadline,
                'end_time' => $task->deadline,
                'event_type' => 'task',
                'status' => $task->status,
                'priority' => $task->priority
            ];
        })->toArray();
    }

    /**
     * Lấy loại user
     * 
     * @param object $user User object
     * @return string User type
     */
    private function getUserType(object $user): string
    {
        if ($user instanceof \Modules\Auth\app\Models\LecturerAccount) {
            return 'lecturer';
        } elseif ($user instanceof \Modules\Auth\app\Models\StudentAccount) {
            return 'student';
        }
        return 'unknown';
    }
}
