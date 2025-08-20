<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Calendar;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Lấy chi tiết calendar event
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetCalendarEventDetailsUseCase
{
    /**
     * Thực hiện lấy chi tiết calendar event
     * 
     * @param Calendar $calendar Calendar object
     * @return array Event details
     * @throws TaskException Nếu có lỗi
     */
    public function execute(Calendar $calendar): array
    {
        try {
            $task = $calendar->task;
            
            // Validate task exists
            if (!$task) {
                throw TaskException::taskNotFound(
                    'Calendar event not found',
                    ['calendar_id' => $calendar->id]
                );
            }
            
            // Transform to event details
            $eventDetails = $this->transformToEventDetails($calendar, $task);
            
            // Log success
            Log::info('Calendar event details retrieved successfully via UseCase', [
                'calendar_id' => $calendar->id,
                'task_id' => $task->id
            ]);
            
            return $eventDetails;
        } catch (\Exception $e) {
            Log::error('Error retrieving calendar event details via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Transform calendar và task thành event details
     * 
     * @param Calendar $calendar Calendar object
     * @param mixed $task Task object
     * @return array Event details
     */
    private function transformToEventDetails(Calendar $calendar, $task): array
    {
        return [
            'id' => $calendar->id,
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
    }
}
