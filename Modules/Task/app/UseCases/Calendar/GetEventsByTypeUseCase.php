<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * Use Case: Lấy events theo loại
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetEventsByTypeUseCase
{
    /**
     * Thực hiện lấy events theo loại
     * 
     * @param Request $request Request chứa type parameter
     * @param object $user User object
     * @return array Danh sách events
     * @throws TaskException Nếu có lỗi
     */
    public function execute(Request $request, object $user): array
    {
        try {
            $eventType = $request->get('type', 'task');
            
            // Validate user
            $this->validateUser($user);
            
            // Lấy tasks theo user
            $tasks = $this->getTasksByUser($user);
            
            // Chuyển đổi thành events
            $events = $this->transformTasksToEvents($tasks);
            
            // Log success
            Log::info('Events by type retrieved successfully via UseCase', [
                'user_id' => $user->id,
                'event_type' => $eventType,
                'events_count' => count($events)
            ]);
            
            return $events;
        } catch (\Exception $e) {
            Log::error('Error retrieving events by type via UseCase: ' . $e->getMessage());
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
     * Lấy tasks theo user
     * 
     * @param object $user User object
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTasksByUser(object $user): \Illuminate\Database\Eloquent\Collection
    {
        return Task::whereHas('receivers', function ($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->where('receiver_type', $this->getUserType($user));
            })
            ->with(['receivers'])
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
