<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * Use Case: Lấy danh sách calendar events
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetCalendarEventsUseCase
{
    /**
     * Thực hiện lấy danh sách calendar events
     * 
     * @param Request $request Request chứa filters
     * @return array Danh sách calendar events
     * @throws TaskException Nếu có lỗi
     */
    public function execute(Request $request): array
    {
        try {
            $filters = $request->only(['start_date', 'end_date', 'event_type', 'search']);
            $perPage = $request->get('per_page', 15);
            
            // Xây dựng query
            $query = $this->buildQuery($request, $filters);
            
            // Thực hiện pagination
            $tasks = $query->paginate($perPage);
            
            // Chuyển đổi tasks thành calendar events
            $calendarEvents = $this->transformTasksToCalendarEvents($tasks);
            
            // Log success
            Log::info('Calendar events retrieved successfully via UseCase', [
                'filters' => $filters,
                'events_count' => count($calendarEvents),
                'per_page' => $perPage
            ]);
            
            return [
                'events' => $calendarEvents,
                'pagination' => [
                    'current_page' => $tasks->currentPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                    'last_page' => $tasks->lastPage()
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error retrieving calendar events via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xây dựng query với filters
     * 
     * @param Request $request Request hiện tại
     * @param array $filters Filters từ request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQuery(Request $request, array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Task::with(['receivers', 'calendarEvents']);
        
        // Filter theo deadline
        if (isset($filters['start_date'])) {
            $query->where('deadline', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('deadline', '<=', $filters['end_date']);
        }
        
        // Filter theo receiver
        if ($request->has('participant_id') && $request->has('participant_type')) {
            $query->whereHas('receivers', function ($q) use ($request) {
                $q->where('receiver_id', $request->participant_id)
                  ->where('receiver_type', $request->participant_type);
            });
        }
        
        return $query;
    }

    /**
     * Chuyển đổi tasks thành calendar events
     * 
     * @param \Illuminate\Pagination\LengthAwarePaginator $tasks Tasks đã paginate
     * @return array Calendar events
     */
    private function transformTasksToCalendarEvents($tasks): array
    {
        return $tasks->getCollection()->map(function ($task) {
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
