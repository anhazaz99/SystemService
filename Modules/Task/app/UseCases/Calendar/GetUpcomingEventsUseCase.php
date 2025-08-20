<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Use Case: Lấy upcoming events
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetUpcomingEventsUseCase
{
    /**
     * Thực hiện lấy upcoming events
     * 
     * @param Request $request Request chứa days parameter
     * @return array Danh sách upcoming events
     * @throws TaskException Nếu có lỗi
     */
    public function execute(Request $request): array
    {
        try {
            $days = $request->get('days', 7); // Default 7 days
            
            // Validate days parameter
            $this->validateDays($days);
            
            // Tính toán date range
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->addDays($days)->endOfDay();
            
            // Lấy upcoming tasks
            $tasks = $this->getUpcomingTasks($startDate, $endDate);
            
            // Chuyển đổi thành events
            $events = $this->transformTasksToEvents($tasks);
            
            // Log success
            Log::info('Upcoming events retrieved successfully via UseCase', [
                'days' => $days,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'events_count' => count($events)
            ]);
            
            return $events;
        } catch (\Exception $e) {
            Log::error('Error retrieving upcoming events via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate days parameter
     * 
     * @param int $days Number of days
     * @throws TaskException Nếu days không hợp lệ
     */
    private function validateDays(int $days): void
    {
        if ($days <= 0 || $days > 365) {
            throw TaskException::businessRuleViolation(
                'Days must be between 1 and 365',
                ['days' => $days]
            );
        }
    }

    /**
     * Lấy upcoming tasks
     * 
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUpcomingTasks(Carbon $startDate, Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return Task::with(['receivers', 'calendarEvents'])
            ->whereBetween('deadline', [$startDate, $endDate])
            ->where('status', '!=', 'completed')
            ->orderBy('deadline', 'asc')
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
                'task_id' => $task->id,
                'creator_id' => $task->creator_id,
                'creator_type' => $task->creator_type,
                'status' => $task->status,
                'priority' => $task->priority,
                'receivers' => $task->receivers,
                'days_until_deadline' => Carbon::now()->diffInDays($task->deadline, false)
            ];
        })->toArray();
    }
}
