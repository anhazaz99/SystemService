<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Use Case: Lấy user events theo range thời gian
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetUserEventsByRangeUseCase
{
    /**
     * Thực hiện lấy user events theo range
     * 
     * @param Request $request Request chứa start_date, end_date và user info
     * @return array Danh sách user events
     * @throws TaskException Nếu có lỗi
     */
    public function execute(Request $request): array
    {
        try {
            $startDate = $request->get('start_date', now()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->addDays(7)->format('Y-m-d'));
            $userId = $request->attributes->get('jwt_user_id');
            $userType = $request->attributes->get('jwt_user_type');
            
            // Validate user authentication
            $this->validateUserAuthentication($userId, $userType);
            
            // Lấy user tasks trong range
            $tasks = $this->getUserTasksInRange($userId, $userType, $startDate, $endDate);
            
            // Chuyển đổi thành events
            $events = $this->transformTasksToEvents($tasks);
            
            // Log success
            Log::info('User events by range retrieved successfully via UseCase', [
                'user_id' => $userId,
                'user_type' => $userType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'events_count' => count($events)
            ]);
            
            return $events;
        } catch (\Exception $e) {
            Log::error('Error retrieving user events by range via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate user authentication
     * 
     * @param int $userId User ID
     * @param string $userType User type
     * @throws TaskException Nếu user không được authenticate
     */
    private function validateUserAuthentication(int $userId, string $userType): void
    {
        if (!$userId || !$userType) {
            throw TaskException::businessRuleViolation(
                'User not authenticated',
                ['user_id' => $userId, 'user_type' => $userType]
            );
        }
    }

    /**
     * Lấy user tasks trong range thời gian
     * 
     * @param int $userId User ID
     * @param string $userType User type
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUserTasksInRange(int $userId, string $userType, string $startDate, string $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return Task::whereBetween('deadline', [$startDate, $endDate])
            ->whereHas('receivers', function ($q) use ($userId, $userType) {
                $q->where('receiver_id', $userId)
                  ->where('receiver_type', $userType);
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
}
