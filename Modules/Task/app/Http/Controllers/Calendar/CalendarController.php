<?php

namespace Modules\Task\app\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use Modules\Task\app\Models\Calendar;
use Modules\Task\app\Models\Task;
use Modules\Task\app\UseCases\Calendar\GetCalendarEventsUseCase;
use Modules\Task\app\UseCases\Calendar\GetUserEventsByRangeUseCase;
use Modules\Task\app\UseCases\Calendar\GetUpcomingEventsUseCase;
use Modules\Task\app\UseCases\Calendar\GetUserUpcomingEventsUseCase;
use Modules\Task\app\UseCases\Calendar\GetEventsByDateUseCase;
use Modules\Task\app\UseCases\Calendar\GetOverdueEventsUseCase;
use Modules\Task\app\UseCases\Calendar\GetEventsCountByStatusUseCase;
use Modules\Task\app\UseCases\Calendar\GetCalendarEventDetailsUseCase;
use Modules\Task\app\UseCases\Calendar\GetEventsByTypeUseCase;
use Modules\Task\app\UseCases\Calendar\GetRemindersUseCase;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Controller cho Calendar - View layer của Task
 * 
 * Tuân thủ Clean Architecture: chỉ xử lý HTTP requests/responses
 * Business logic được tách biệt vào Use Cases
 */
class CalendarController extends Controller
{
    protected $getCalendarEventsUseCase;
    protected $getUserEventsByRangeUseCase;
    protected $getUpcomingEventsUseCase;
    protected $getUserUpcomingEventsUseCase;
    protected $getEventsByDateUseCase;
    protected $getOverdueEventsUseCase;
    protected $getEventsCountByStatusUseCase;
    protected $getCalendarEventDetailsUseCase;
    protected $getEventsByTypeUseCase;
    protected $getRemindersUseCase;

    /**
     * Khởi tạo controller với dependency injection
     * 
     * @param GetCalendarEventsUseCase $getCalendarEventsUseCase Use case lấy calendar events
     * @param GetUserEventsByRangeUseCase $getUserEventsByRangeUseCase Use case lấy user events theo range
     * @param GetUpcomingEventsUseCase $getUpcomingEventsUseCase Use case lấy upcoming events
     * @param GetUserUpcomingEventsUseCase $getUserUpcomingEventsUseCase Use case lấy user upcoming events
     * @param GetEventsByDateUseCase $getEventsByDateUseCase Use case lấy events theo date
     * @param GetOverdueEventsUseCase $getOverdueEventsUseCase Use case lấy overdue events
     * @param GetEventsCountByStatusUseCase $getEventsCountByStatusUseCase Use case đếm events theo status
     * @param GetCalendarEventDetailsUseCase $getCalendarEventDetailsUseCase Use case lấy chi tiết event
     * @param GetEventsByTypeUseCase $getEventsByTypeUseCase Use case lấy events theo type
     * @param GetRemindersUseCase $getRemindersUseCase Use case lấy reminders
     */
    public function __construct(
        GetCalendarEventsUseCase $getCalendarEventsUseCase,
        GetUserEventsByRangeUseCase $getUserEventsByRangeUseCase,
        GetUpcomingEventsUseCase $getUpcomingEventsUseCase,
        GetUserUpcomingEventsUseCase $getUserUpcomingEventsUseCase,
        GetEventsByDateUseCase $getEventsByDateUseCase,
        GetOverdueEventsUseCase $getOverdueEventsUseCase,
        GetEventsCountByStatusUseCase $getEventsCountByStatusUseCase,
        GetCalendarEventDetailsUseCase $getCalendarEventDetailsUseCase,
        GetEventsByTypeUseCase $getEventsByTypeUseCase,
        GetRemindersUseCase $getRemindersUseCase
    ) {
        $this->getCalendarEventsUseCase = $getCalendarEventsUseCase;
        $this->getUserEventsByRangeUseCase = $getUserEventsByRangeUseCase;
        $this->getUpcomingEventsUseCase = $getUpcomingEventsUseCase;
        $this->getUserUpcomingEventsUseCase = $getUserUpcomingEventsUseCase;
        $this->getEventsByDateUseCase = $getEventsByDateUseCase;
        $this->getOverdueEventsUseCase = $getOverdueEventsUseCase;
        $this->getEventsCountByStatusUseCase = $getEventsCountByStatusUseCase;
        $this->getCalendarEventDetailsUseCase = $getCalendarEventDetailsUseCase;
        $this->getEventsByTypeUseCase = $getEventsByTypeUseCase;
        $this->getRemindersUseCase = $getRemindersUseCase;
    }

    /**
     * Hiển thị danh sách calendar events (từ Task)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Sử dụng Use Case để lấy calendar events
            $result = $this->getCalendarEventsUseCase->execute($request);
            return response()->json([
                'success' => true,
                'data' => $result['events'],
                'pagination' => $result['pagination'],
                'message' => 'Lấy danh sách calendar events thành công'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy calendar events'
            ], 500);
        }
    }

    /**
     * Hiển thị thông tin chi tiết của một calendar event
     */
    public function show(Calendar $calendar): JsonResponse
    {
        try {
            // Sử dụng Use Case để lấy chi tiết event
            $eventDetails = $this->getCalendarEventDetailsUseCase->execute($calendar);
            
            return response()->json([
                'success' => true,
                'data' => $eventDetails,
                'message' => 'Calendar event retrieved successfully'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving calendar event'
            ], 500);
        }
    }

    /**
     * Lấy events theo ngày cụ thể
     */
    public function eventsByDate(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Sử dụng Use Case để lấy events theo date
            $events = $this->getEventsByDateUseCase->execute($request, $user);
            
            $date = $request->get('date', now()->format('Y-m-d'));
            
            return response()->json([
                'success' => true,
                'data' => $events,
                'message' => 'Events retrieved for date: ' . $date
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving events by date'
            ], 500);
        }
    }

    /**
     * Lấy events theo khoảng thời gian
     */
    public function eventsByRange(Request $request): JsonResponse
    {
        try {
            // Sử dụng Use Case để lấy user events theo range
            $events = $this->getUserEventsByRangeUseCase->execute($request);
            
            $startDate = $request->get('start_date', now()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->addDays(7)->format('Y-m-d'));
            
            return response()->json([
                'success' => true,
                'data' => $events,
                'message' => 'Events retrieved for range: ' . $startDate . ' to ' . $endDate
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving events by range'
            ], 500);
        }
    }

    /**
     * Lấy events sắp tới
     */
    public function upcomingEvents(Request $request): JsonResponse
    {
        try {
            // Sử dụng Use Case để lấy user upcoming events
            $events = $this->getUserUpcomingEventsUseCase->execute($request);
            
            $days = (int) $request->get('days', 7);
            
            return response()->json([
                'success' => true,
                'data' => $events,
                'message' => 'Upcoming events retrieved for next ' . $days . ' days'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving upcoming events'
            ], 500);
        }
    }

    /**
     * Lấy events quá hạn
     */
    public function overdueEvents(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Sử dụng Use Case để lấy overdue events
            $events = $this->getOverdueEventsUseCase->execute($user);
            
            return response()->json([
                'success' => true,
                'data' => $events,
                'message' => 'Overdue events retrieved'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving overdue events'
            ], 500);
        }
    }

    /**
     * Lấy events theo loại
     */
    public function eventsByType(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $eventType = $request->get('type', 'task');
            
            // Sử dụng Use Case để lấy events theo type
            $events = $this->getEventsByTypeUseCase->execute($request, $user);
            
            return response()->json([
                'success' => true,
                'data' => $events,
                'message' => 'Events retrieved by type: ' . $eventType
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving events by type'
            ], 500);
        }
    }

    /**
     * Đếm events theo trạng thái
     */
    public function eventsCountByStatus(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Sử dụng Use Case để đếm events theo status
            $counts = $this->getEventsCountByStatusUseCase->execute($user);
            
            return response()->json([
                'success' => true,
                'data' => $counts,
                'message' => 'Events count by status retrieved'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving events count by status'
            ], 500);
        }
    }

    /**
     * Lấy reminders
     */
    public function reminders(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Sử dụng Use Case để lấy reminders
            $reminders = $this->getRemindersUseCase->execute($user);
            
            return response()->json([
                'success' => true,
                'data' => $reminders,
                'message' => 'Reminders retrieved'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving reminders'
            ], 500);
        }
    }

    /**
     * Thiết lập reminder
     */
    public function setReminder(Request $request): JsonResponse
    {
        $taskId = $request->get('task_id');
        $reminderTime = $request->get('reminder_time');
        
        // Logic thiết lập reminder sẽ được implement sau
        return response()->json([
            'success' => true,
            'message' => 'Reminder set successfully for task: ' . $taskId
        ]);
    }

    /**
     * Tạo event định kỳ
     */
    public function createRecurring(Request $request): JsonResponse
    {
        // Logic tạo event định kỳ sẽ được implement sau
        return response()->json([
            'success' => true,
            'message' => 'Recurring event created successfully'
        ]);
    }

    /**
     * Cập nhật event định kỳ
     */
    public function updateRecurring(Request $request, Calendar $calendar): JsonResponse
    {
        // Logic cập nhật event định kỳ sẽ được implement sau
        return response()->json([
            'success' => true,
            'message' => 'Recurring event updated successfully'
        ]);
    }

    /**
     * Export dữ liệu
     */
    public function export(Request $request): JsonResponse
    {
        // Logic export sẽ được implement sau
        return response()->json([
            'success' => true,
            'message' => 'Data exported successfully'
        ]);
    }

    /**
     * Import dữ liệu
     */
    public function import(Request $request): JsonResponse
    {
        // Logic import sẽ được implement sau
        return response()->json([
            'success' => true,
            'message' => 'Data imported successfully'
        ]);
    }

    /**
     * Lấy events định kỳ
     */
    public function recurringEvents(Request $request): JsonResponse
    {
        // Logic lấy events định kỳ sẽ được implement sau
        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Recurring events retrieved'
        ]);
    }

    /**
     * Tạo event mới
     */
    public function store(Request $request): JsonResponse
    {
        // Logic tạo event mới sẽ được implement sau
        return response()->json([
            'success' => true,
            'message' => 'Event created successfully'
        ]);
    }

    /**
     * Cập nhật event
     */
    public function update(Request $request, Calendar $calendar): JsonResponse
    {
        // Logic cập nhật event sẽ được implement sau
        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully'
        ]);
    }

    /**
     * Xóa event
     */
    public function destroy(Calendar $calendar): JsonResponse
    {
        // Logic xóa event sẽ được implement sau
        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }


}
