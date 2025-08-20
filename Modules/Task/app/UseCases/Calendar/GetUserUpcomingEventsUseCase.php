<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Use Case: Lấy upcoming events của user cụ thể
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetUserUpcomingEventsUseCase
{
    /**
     * Thực hiện lấy upcoming events của user
     * 
     * @param Request $request Request chứa days parameter và user info
     * @return array Danh sách upcoming events
     * @throws TaskException Nếu có lỗi
     */
    public function execute(Request $request): array
    {
        try {
            $userId = $request->attributes->get('jwt_user_id');
            $userType = $request->attributes->get('jwt_user_type');
            $days = (int) $request->get('days', 7);
            
            // Validate input
            $this->validateInput($userId, $userType, $days);
            
            // Lấy upcoming tasks của user
            $tasks = $this->getUserUpcomingTasks($userId, $userType, $days);
            
            // Chuyển đổi thành events
            $events = $this->transformTasksToEvents($tasks);
            
            // Log success
            Log::info('User upcoming events retrieved successfully via UseCase', [
                'user_id' => $userId,
                'user_type' => $userType,
                'days' => $days,
                'events_count' => count($events)
            ]);
            
            return $events;
        } catch (\Exception $e) {
            Log::error('Error retrieving user upcoming events via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate input
     * 
     * @param int $userId User ID
     * @param string $userType User type
     * @param int $days Number of days
     * @throws TaskException Nếu input không hợp lệ
     */
    private function validateInput(int $userId, string $userType, int $days): void
    {
        if (!$userId || !$userType) {
            throw TaskException::businessRuleViolation(
                'User not authenticated',
                ['user_id' => $userId, 'user_type' => $userType]
            );
        }

        if ($days <= 0 || $days > 365) {
            throw TaskException::businessRuleViolation(
                'Days must be between 1 and 365',
                ['days' => $days]
            );
        }
    }

    /**
     * Lấy upcoming tasks của user
     * 
     * @param int $userId User ID
     * @param string $userType User type
     * @param int $days Number of days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUserUpcomingTasks(int $userId, string $userType, int $days): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('deadline', '>=', now())
            ->where('deadline', '<=', now()->addDays($days))
            ->whereHas('receivers', function ($q) use ($userId, $userType) {
                $q->where('receiver_id', $userId)
                  ->where('receiver_type', $userType);
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
                'priority' => $task->priority,
                'days_until_deadline' => Carbon::now()->diffInDays($task->deadline, false)
            ];
        })->toArray();
    }
}
