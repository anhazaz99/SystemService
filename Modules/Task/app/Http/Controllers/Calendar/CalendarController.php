<?php

namespace Modules\Task\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use Modules\Task\app\Models\Calendar;
use Modules\Task\app\Services\CalendarService;
use Modules\Task\app\Repositories\CalendarRepository;
use Modules\Task\Http\Requests\CalendarRequest;
use Modules\Task\Transformers\CalendarResource;
use Modules\Task\Transformers\CalendarCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    protected $calendarService;
    protected $calendarRepository;

    public function __construct(CalendarService $calendarService, CalendarRepository $calendarRepository)
    {
        $this->calendarService = $calendarService;
        $this->calendarRepository = $calendarRepository;
    }

    /**
     * Display a listing of calendar events.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'event_type', 'user_id', 'status', 'search']);
        $perPage = $request->get('per_page', 15);
        
        $events = $this->calendarRepository->getEventsWithFilters($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => new CalendarCollection($events['data']),
            'message' => 'Calendar events retrieved successfully'
        ]);
    }

    /**
     * Store a newly created calendar event.
     */
    public function store(CalendarRequest $request): JsonResponse
    {
        $data = $request->validated();
        $event = $this->calendarService->createEvent($data);
        
        return response()->json([
            'success' => true,
            'data' => new CalendarResource($event),
            'message' => 'Calendar event created successfully'
        ], 201);
    }

    /**
     * Display the specified calendar event.
     */
    public function show(Calendar $calendar): JsonResponse
    {
        $event = $this->calendarRepository->findById($calendar->id);
        
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Calendar event not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => new CalendarResource($event),
            'message' => 'Calendar event retrieved successfully'
        ]);
    }

    /**
     * Update the specified calendar event.
     */
    public function update(CalendarRequest $request, Calendar $calendar): JsonResponse
    {
        $data = $request->validated();
        $event = $this->calendarService->updateEvent($calendar, $data);
        
        return response()->json([
            'success' => true,
            'data' => new CalendarResource($event),
            'message' => 'Calendar event updated successfully'
        ]);
    }

    /**
     * Remove the specified calendar event.
     */
    public function destroy(Calendar $calendar): JsonResponse
    {
        $this->calendarService->deleteEvent($calendar);
        
        return response()->json([
            'success' => true,
            'message' => 'Calendar event deleted successfully'
        ]);
    }

    /**
     * Get calendar view (monthly, weekly, daily).
     */
    public function view(Request $request): JsonResponse
    {
        $request->validate([
            'view_type' => 'required|in:monthly,weekly,daily',
            'date' => 'required|date'
        ]);

        $viewType = $request->get('view_type', 'monthly');
        $date = Carbon::parse($request->get('date', now()));
        
        $viewData = $this->calendarService->getCalendarView($viewType, $date);
        
        return response()->json([
            'success' => true,
            'data' => $viewData,
            'message' => 'Calendar view retrieved successfully'
        ]);
    }

    /**
     * Create a recurring calendar event.
     */
    public function createRecurring(CalendarRequest $request): JsonResponse
    {
        $data = $request->validated();
        $event = $this->calendarService->createRecurringEvent($data);
        
        return response()->json([
            'success' => true,
            'data' => new CalendarResource($event),
            'message' => 'Recurring calendar event created successfully'
        ], 201);
    }

    /**
     * Update a recurring calendar event.
     */
    public function updateRecurring(CalendarRequest $request, Calendar $calendar): JsonResponse
    {
        $data = $request->validated();
        $event = $this->calendarService->updateRecurringEvent($calendar, $data);
        
        return response()->json([
            'success' => true,
            'data' => new CalendarResource($event),
            'message' => 'Recurring calendar event updated successfully'
        ]);
    }

    /**
     * Export calendar events.
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:json,csv,ical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        $format = $request->get('format', 'json');
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;
        
        $exportData = $this->calendarService->exportCalendar($format, $startDate, $endDate);
        
        return response()->json([
            'success' => true,
            'data' => $exportData,
            'message' => 'Calendar exported successfully'
        ]);
    }

    /**
     * Import calendar events.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:json,csv,ics|max:10240',
            'format' => 'required|in:json,csv,ical'
        ]);

        $file = $request->file('file');
        $format = $request->get('format', 'json');
        
        $importResult = $this->calendarService->importCalendar($file, $format);
        
        return response()->json([
            'success' => true,
            'data' => $importResult,
            'message' => 'Calendar imported successfully'
        ]);
    }

    /**
     * Sync calendar with external service.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'service' => 'required|in:google,outlook'
        ]);

        $service = $request->get('service');
        
        $syncResult = $this->calendarService->syncCalendar($service);
        
        return response()->json([
            'success' => true,
            'data' => $syncResult,
            'message' => 'Calendar synced successfully'
        ]);
    }

    /**
     * Get calendar statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;
        
        $statistics = $this->calendarService->getCalendarStatistics($startDate, $endDate);
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Calendar statistics retrieved successfully'
        ]);
    }

    /**
     * Get calendar conflicts.
     */
    public function conflicts(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'user_id' => 'required|integer'
        ]);

        $startDate = Carbon::parse($request->get('start_date'));
        $endDate = Carbon::parse($request->get('end_date'));
        $userId = $request->get('user_id');
        
        $conflicts = $this->calendarService->getCalendarConflicts($startDate, $endDate, $userId);
        
        return response()->json([
            'success' => true,
            'data' => $conflicts,
            'message' => 'Calendar conflicts retrieved successfully'
        ]);
    }

    /**
     * Get calendar reminders.
     */
    public function reminders(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        $userId = $request->get('user_id');
        
        $reminders = $this->calendarService->getCalendarReminders($userId);
        
        return response()->json([
            'success' => true,
            'data' => $reminders,
            'message' => 'Calendar reminders retrieved successfully'
        ]);
    }

    /**
     * Set calendar reminder.
     */
    public function setReminder(Request $request): JsonResponse
    {
        $request->validate([
            'event_id' => 'required|integer|exists:calendar,id',
            'reminder_time' => 'required|string',
            'reminder_type' => 'required|in:email,push,sms'
        ]);

        $eventId = $request->get('event_id');
        $reminderTime = $request->get('reminder_time');
        $reminderType = $request->get('reminder_type');
        
        $reminder = $this->calendarService->setCalendarReminder($eventId, $reminderTime, $reminderType);
        
        return response()->json([
            'success' => true,
            'data' => $reminder,
            'message' => 'Calendar reminder set successfully'
        ]);
    }

    /**
     * Get events by date.
     */
    public function eventsByDate(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = Carbon::parse($request->get('date', now()));
        
        $events = $this->calendarRepository->getEventsByDate($date);
        
        return response()->json([
            'success' => true,
            'data' => CalendarResource::collection($events),
            'message' => 'Events by date retrieved successfully'
        ]);
    }

    /**
     * Get events by date range.
     */
    public function eventsByRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $startDate = Carbon::parse($request->get('start_date'));
        $endDate = Carbon::parse($request->get('end_date'));
        
        $events = $this->calendarRepository->getEventsByRange($startDate, $endDate);
        
        return response()->json([
            'success' => true,
            'data' => CalendarResource::collection($events),
            'message' => 'Events by range retrieved successfully'
        ]);
    }

    /**
     * Get recurring events.
     */
    public function recurringEvents(): JsonResponse
    {
        $events = $this->calendarRepository->getRecurringEvents();
        
        return response()->json([
            'success' => true,
            'data' => CalendarResource::collection($events),
            'message' => 'Recurring events retrieved successfully'
        ]);
    }

    /**
     * Get upcoming events.
     */
    public function upcomingEvents(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        $userId = $request->get('user_id');
        $limit = $request->get('limit', 10);
        
        $events = $this->calendarRepository->getUpcomingEvents($userId, $limit);
        
        return response()->json([
            'success' => true,
            'data' => CalendarResource::collection($events),
            'message' => 'Upcoming events retrieved successfully'
        ]);
    }

    /**
     * Get overdue events.
     */
    public function overdueEvents(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        $userId = $request->get('user_id');
        
        $events = $this->calendarRepository->getOverdueEvents($userId);
        
        return response()->json([
            'success' => true,
            'data' => CalendarResource::collection($events),
            'message' => 'Overdue events retrieved successfully'
        ]);
    }

    /**
     * Get events by type.
     */
    public function eventsByType(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:task,su_kien',
            'user_id' => 'nullable|integer'
        ]);

        $type = $request->get('type');
        $userId = $request->get('user_id');
        
        $events = $this->calendarRepository->getEventsByType($type, $userId);
        
        return response()->json([
            'success' => true,
            'data' => CalendarResource::collection($events),
            'message' => 'Events by type retrieved successfully'
        ]);
    }

    /**
     * Get events count by status.
     */
    public function eventsCountByStatus(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'nullable|integer'
        ]);

        $userId = $request->get('user_id');
        
        $counts = $this->calendarRepository->getEventsCountByStatus($userId);
        
        return response()->json([
            'success' => true,
            'data' => $counts,
            'message' => 'Events count by status retrieved successfully'
        ]);
    }
}
