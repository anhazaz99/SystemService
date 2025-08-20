<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * Use Case: Lấy events theo ngày cụ thể
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetEventsByDateUseCase
{
    /**
     * Thực hiện lấy events theo ngày
     * 
     * @param Request $request Request chứa date parameter
     * @param object $user User object
     * @return array Danh sách events
     * @throws TaskException Nếu có lỗi
     */
    public function execute(Request $request, object $user): array
    {
        try {
            $date = $request->get('date', now()->format('Y-m-d'));
            
            // Validate input
            $this->validateInput($user, $date);
            
            // Lấy tasks theo ngày
            $tasks = $this->getTasksByDate($user, $date);
            
            // Chuyển đổi thành events
            $events = $this->transformTasksToEvents($tasks);
            
            // Log success
            Log::info('Events by date retrieved successfully via UseCase', [
                'user_id' => $user->id,
                'date' => $date,
                'events_count' => count($events)
            ]);
            
            return $events;
        } catch (\Exception $e) {
            Log::error('Error retrieving events by date via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate input
     * 
     * @param object $user User object
     * @param string $date Date string
     * @throws TaskException Nếu input không hợp lệ
     */
    private function validateInput(object $user, string $date): void
    {
        if (!$user || !isset($user->id)) {
            throw TaskException::businessRuleViolation(
                'User not authenticated',
                ['user' => $user]
            );
        }

        if (!$date || !strtotime($date)) {
            throw TaskException::businessRuleViolation(
                'Invalid date format',
                ['date' => $date]
            );
        }
    }

    /**
     * Lấy tasks theo ngày
     * 
     * @param object $user User object
     * @param string $date Date string
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTasksByDate(object $user, string $date): \Illuminate\Database\Eloquent\Collection
    {
        return Task::whereDate('deadline', $date)
            ->whereHas('receivers', function ($q) use ($user) {
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