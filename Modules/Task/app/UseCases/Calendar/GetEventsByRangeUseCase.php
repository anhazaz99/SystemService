<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Use Case: Lấy events theo range thời gian
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetEventsByRangeUseCase
{
    /**
     * Thực hiện lấy events theo range
     * 
     * @param Request $request Request chứa start_date và end_date
     * @return array Danh sách events
     * @throws TaskException Nếu có lỗi
     */
    public function execute(Request $request): array
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            // Validate input
            $this->validateDateRange($startDate, $endDate);
            
            // Lấy tasks trong range
            $tasks = $this->getTasksInRange($startDate, $endDate);
            
            // Chuyển đổi thành calendar events
            $events = $this->transformTasksToEvents($tasks);
            
            // Log success
            Log::info('Events by range retrieved successfully via UseCase', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'events_count' => count($events)
            ]);
            
            return $events;
        } catch (\Exception $e) {
            Log::error('Error retrieving events by range via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @throws TaskException Nếu date range không hợp lệ
     */
    private function validateDateRange(string $startDate, string $endDate): void
    {
        if (!$startDate || !$endDate) {
            throw TaskException::businessRuleViolation(
                'Start date and end date are required',
                ['start_date' => $startDate, 'end_date' => $endDate]
            );
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->gt($end)) {
            throw TaskException::businessRuleViolation(
                'Start date must be before end date',
                ['start_date' => $startDate, 'end_date' => $endDate]
            );
        }
    }

    /**
     * Lấy tasks trong range thời gian
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTasksInRange(string $startDate, string $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return Task::with(['receivers', 'calendarEvents'])
            ->whereBetween('deadline', [$startDate, $endDate])
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
                'receivers' => $task->receivers
            ];
        })->toArray();
    }
}
