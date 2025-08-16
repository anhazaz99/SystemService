<?php

namespace Modules\Task\Services;

use Modules\Task\app\Models\Calendar;
use Modules\Task\app\Repositories\CalendarRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class CalendarService
{
    protected $calendarRepository;

    public function __construct(CalendarRepository $calendarRepository)
    {
        $this->calendarRepository = $calendarRepository;
    }

    /**
     * Create a new calendar event.
     */
    public function createEvent(array $data): Calendar
    {
        try {
            $event = Calendar::create($data);
            
            Log::info('Calendar event created', [
                'event_id' => $event->id,
                'title' => $event->tieu_de,
                'user_id' => $event->nguoi_tao_id
            ]);
            
            return $event;
        } catch (\Exception $e) {
            Log::error('Error creating calendar event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing calendar event.
     */
    public function updateEvent(Calendar $event, array $data): Calendar
    {
        try {
            $event->update($data);
            
            Log::info('Calendar event updated', [
                'event_id' => $event->id,
                'title' => $event->tieu_de
            ]);
            
            return $event;
        } catch (\Exception $e) {
            Log::error('Error updating calendar event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a calendar event.
     */
    public function deleteEvent(Calendar $event): bool
    {
        try {
            $eventId = $event->id;
            $eventTitle = $event->tieu_de;
            
            $event->delete();
            
            Log::info('Calendar event deleted', [
                'event_id' => $eventId,
                'title' => $eventTitle
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting calendar event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a recurring event.
     */
    public function createRecurringEvent(array $data): Calendar
    {
        try {
            // Add recurring pattern data
            $data['is_recurring'] = true;
            $data['recurring_pattern'] = $data['recurring_pattern'] ?? 'weekly';
            $data['recurring_interval'] = $data['recurring_interval'] ?? 1;
            
            $event = Calendar::create($data);
            
            Log::info('Recurring calendar event created', [
                'event_id' => $event->id,
                'pattern' => $data['recurring_pattern']
            ]);
            
            return $event;
        } catch (\Exception $e) {
            Log::error('Error creating recurring event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a recurring event.
     */
    public function updateRecurringEvent(Calendar $event, array $data): Calendar
    {
        try {
            $event->update($data);
            
            Log::info('Recurring calendar event updated', [
                'event_id' => $event->id
            ]);
            
            return $event;
        } catch (\Exception $e) {
            Log::error('Error updating recurring event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get calendar view (monthly, weekly, daily).
     */
    public function getCalendarView(string $viewType, Carbon $date): array
    {
        try {
            switch ($viewType) {
                case 'monthly':
                    return $this->getMonthlyView($date);
                case 'weekly':
                    return $this->getWeeklyView($date);
                case 'daily':
                    return $this->getDailyView($date);
                default:
                    return $this->getMonthlyView($date);
            }
        } catch (\Exception $e) {
            Log::error('Error getting calendar view: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get monthly calendar view.
     */
    private function getMonthlyView(Carbon $date): array
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        $events = Calendar::whereBetween('thoi_gian_bat_dau', [$startOfMonth, $endOfMonth])
                         ->orWhereBetween('thoi_gian_ket_thuc', [$startOfMonth, $endOfMonth])
                         ->get();
        
        return [
            'view_type' => 'monthly',
            'date' => $date->format('Y-m-d'),
            'events' => $events,
            'calendar_grid' => $this->generateCalendarGrid($startOfMonth, $endOfMonth, $events)
        ];
    }

    /**
     * Get weekly calendar view.
     */
    private function getWeeklyView(Carbon $date): array
    {
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();
        
        $events = Calendar::whereBetween('thoi_gian_bat_dau', [$startOfWeek, $endOfWeek])
                         ->orWhereBetween('thoi_gian_ket_thuc', [$startOfWeek, $endOfWeek])
                         ->get();
        
        return [
            'view_type' => 'weekly',
            'date' => $date->format('Y-m-d'),
            'events' => $events,
            'week_days' => $this->generateWeekDays($startOfWeek, $endOfWeek)
        ];
    }

    /**
     * Get daily calendar view.
     */
    private function getDailyView(Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        $events = Calendar::whereBetween('thoi_gian_bat_dau', [$startOfDay, $endOfDay])
                         ->orWhereBetween('thoi_gian_ket_thuc', [$startOfDay, $endOfDay])
                         ->get();
        
        return [
            'view_type' => 'daily',
            'date' => $date->format('Y-m-d'),
            'events' => $events,
            'time_slots' => $this->generateTimeSlots()
        ];
    }

    /**
     * Export calendar events.
     */
    public function exportCalendar(string $format, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        try {
            $events = $this->calendarRepository->getEventsForExport($startDate, $endDate);
            
            switch ($format) {
                case 'json':
                    return $this->exportToJson($events);
                case 'csv':
                    return $this->exportToCsv($events);
                case 'ical':
                    return $this->exportToIcal($events);
                default:
                    return $this->exportToJson($events);
            }
        } catch (\Exception $e) {
            Log::error('Error exporting calendar: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Import calendar events.
     */
    public function importCalendar(UploadedFile $file, string $format): array
    {
        try {
            $importedEvents = [];
            
            switch ($format) {
                case 'json':
                    $importedEvents = $this->importFromJson($file);
                    break;
                case 'csv':
                    $importedEvents = $this->importFromCsv($file);
                    break;
                case 'ical':
                    $importedEvents = $this->importFromIcal($file);
                    break;
            }
            
            Log::info('Calendar imported', [
                'format' => $format,
                'count' => count($importedEvents)
            ]);
            
            return [
                'imported_count' => count($importedEvents),
                'events' => $importedEvents
            ];
        } catch (\Exception $e) {
            Log::error('Error importing calendar: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync calendar with external service.
     */
    public function syncCalendar(string $service): array
    {
        try {
            switch ($service) {
                case 'google':
                    return $this->syncWithGoogle();
                case 'outlook':
                    return $this->syncWithOutlook();
                default:
                    throw new \Exception('Unsupported service: ' . $service);
            }
        } catch (\Exception $e) {
            Log::error('Error syncing calendar: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get calendar statistics.
     */
    public function getCalendarStatistics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        try {
            $stats = $this->calendarRepository->getStatistics($startDate, $endDate);
            
            return [
                'total_events' => $stats['total_events'] ?? 0,
                'completed_events' => $stats['completed_events'] ?? 0,
                'upcoming_events' => $stats['upcoming_events'] ?? 0,
                'overdue_events' => $stats['overdue_events'] ?? 0,
                'events_by_type' => $stats['events_by_type'] ?? [],
                'events_by_month' => $stats['events_by_month'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error('Error getting calendar statistics: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get calendar conflicts.
     */
    public function getCalendarConflicts(Carbon $startDate, Carbon $endDate, int $userId): array
    {
        try {
            $conflicts = $this->calendarRepository->getConflicts($startDate, $endDate, $userId);
            
            return [
                'conflicts' => $conflicts,
                'conflict_count' => count($conflicts)
            ];
        } catch (\Exception $e) {
            Log::error('Error getting calendar conflicts: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get calendar reminders.
     */
    public function getCalendarReminders(int $userId): array
    {
        try {
            $reminders = $this->calendarRepository->getReminders($userId);
            
            return [
                'reminders' => $reminders,
                'reminder_count' => count($reminders)
            ];
        } catch (\Exception $e) {
            Log::error('Error getting calendar reminders: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Set calendar reminder.
     */
    public function setCalendarReminder(int $eventId, string $reminderTime, string $reminderType): array
    {
        try {
            $reminder = [
                'event_id' => $eventId,
                'reminder_time' => $reminderTime,
                'reminder_type' => $reminderType,
                'created_at' => now()
            ];
            
            // Store reminder logic here
            Log::info('Calendar reminder set', $reminder);
            
            return $reminder;
        } catch (\Exception $e) {
            Log::error('Error setting calendar reminder: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate calendar grid for monthly view.
     */
    private function generateCalendarGrid(Carbon $startDate, Carbon $endDate, $events): array
    {
        $grid = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dayEvents = $events->filter(function ($event) use ($currentDate) {
                return $event->thoi_gian_bat_dau->format('Y-m-d') === $currentDate->format('Y-m-d');
            });
            
            $grid[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->day,
                'events' => $dayEvents->values()
            ];
            
            $currentDate->addDay();
        }
        
        return $grid;
    }

    /**
     * Generate week days for weekly view.
     */
    private function generateWeekDays(Carbon $startDate, Carbon $endDate): array
    {
        $days = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->format('l'),
                'day_number' => $currentDate->day
            ];
            $currentDate->addDay();
        }
        
        return $days;
    }

    /**
     * Generate time slots for daily view.
     */
    private function generateTimeSlots(): array
    {
        $slots = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $slots[] = [
                'hour' => $hour,
                'time' => sprintf('%02d:00', $hour)
            ];
        }
        return $slots;
    }

    /**
     * Export to JSON format.
     */
    private function exportToJson($events): array
    {
        return [
            'format' => 'json',
            'data' => $events->toArray(),
            'exported_at' => now()->toISOString()
        ];
    }

    /**
     * Export to CSV format.
     */
    private function exportToCsv($events): array
    {
        // CSV export logic
        return [
            'format' => 'csv',
            'data' => 'csv_data_here',
            'exported_at' => now()->toISOString()
        ];
    }

    /**
     * Export to iCal format.
     */
    private function exportToIcal($events): array
    {
        // iCal export logic
        return [
            'format' => 'ical',
            'data' => 'ical_data_here',
            'exported_at' => now()->toISOString()
        ];
    }

    /**
     * Import from JSON format.
     */
    private function importFromJson(UploadedFile $file): array
    {
        $content = $file->getContent();
        $data = json_decode($content, true);
        
        $importedEvents = [];
        foreach ($data as $eventData) {
            $event = Calendar::create($eventData);
            $importedEvents[] = $event;
        }
        
        return $importedEvents;
    }

    /**
     * Import from CSV format.
     */
    private function importFromCsv(UploadedFile $file): array
    {
        // CSV import logic
        return [];
    }

    /**
     * Import from iCal format.
     */
    private function importFromIcal(UploadedFile $file): array
    {
        // iCal import logic
        return [];
    }

    /**
     * Sync with Google Calendar.
     */
    private function syncWithGoogle(): array
    {
        // Google Calendar sync logic
        return [
            'service' => 'google',
            'synced_events' => 0,
            'status' => 'not_implemented'
        ];
    }

    /**
     * Sync with Outlook Calendar.
     */
    private function syncWithOutlook(): array
    {
        // Outlook Calendar sync logic
        return [
            'service' => 'outlook',
            'synced_events' => 0,
            'status' => 'not_implemented'
        ];
    }
}