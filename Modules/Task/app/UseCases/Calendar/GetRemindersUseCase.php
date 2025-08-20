<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Lấy reminders
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetRemindersUseCase
{
    /**
     * Thực hiện lấy reminders
     * 
     * @param object $user User object
     * @return array Danh sách reminders
     * @throws TaskException Nếu có lỗi
     */
    public function execute(object $user): array
    {
        try {
            // Validate user
            $this->validateUser($user);
            
            // Lấy reminder tasks (deadline trong 3 ngày tới)
            $tasks = $this->getReminderTasks($user);
            
            // Chuyển đổi thành reminders
            $reminders = $this->transformTasksToReminders($tasks);
            
            // Log success
            Log::info('Reminders retrieved successfully via UseCase', [
                'user_id' => $user->id,
                'reminders_count' => count($reminders)
            ]);
            
            return $reminders;
        } catch (\Exception $e) {
            Log::error('Error retrieving reminders via UseCase: ' . $e->getMessage());
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
     * Lấy reminder tasks
     * 
     * @param object $user User object
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getReminderTasks(object $user): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('deadline', '>=', now())
            ->where('deadline', '<=', now()->addDays(3))
            ->whereHas('receivers', function ($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->where('receiver_type', $this->getUserType($user));
            })
            ->with(['receivers'])
            ->orderBy('deadline')
            ->get();
    }

    /**
     * Chuyển đổi tasks thành reminders
     * 
     * @param \Illuminate\Database\Eloquent\Collection $tasks Tasks collection
     * @return array Reminders array
     */
    private function transformTasksToReminders($tasks): array
    {
        return $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'deadline' => $task->deadline,
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
