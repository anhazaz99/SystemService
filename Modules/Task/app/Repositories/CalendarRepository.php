<?php

namespace Modules\Task\Repositories;

use Modules\Task\app\Models\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarRepository
{
    /**
     * Find calendar event by ID.
     */
    public function findById(int $id): ?Calendar
    {
        return Calendar::find($id);
    }

    /**
     * Get events by specific date.
     */
    public function getEventsByDate(Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        return Calendar::whereBetween('thoi_gian_bat_dau', [$startOfDay, $endOfDay])
                     ->orWhereBetween('thoi_gian_ket_thuc', [$startOfDay, $endOfDay])
                     ->get()
                     ->toArray();
    }

    /**
     * Get events by date range.
     */
    public function getEventsByRange(Carbon $startDate, Carbon $endDate): array
    {
        return Calendar::whereBetween('thoi_gian_bat_dau', [$startDate, $endDate])
                     ->orWhereBetween('thoi_gian_ket_thuc', [$startDate, $endDate])
                     ->orWhere(function ($query) use ($startDate, $endDate) {
                         $query->where('thoi_gian_bat_dau', '<=', $startDate)
                               ->where('thoi_gian_ket_thuc', '>=', $endDate);
                     })
                     ->get()
                     ->toArray();
    }

    /**
     * Get recurring events.
     */
    public function getRecurringEvents(): array
    {
        return Calendar::where('is_recurring', true)
                     ->get()
                     ->toArray();
    }

    /**
     * Get events for export.
     */
    public function getEventsForExport(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Calendar::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('thoi_gian_bat_dau', [$startDate, $endDate]);
        }
        
        return $query->get()->toArray();
    }

    /**
     * Get calendar statistics.
     */
    public function getStatistics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Calendar::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('thoi_gian_bat_dau', [$startDate, $endDate]);
        }
        
        $totalEvents = $query->count();
        
        $completedEvents = $query->where('trang_thai', 'hoan_thanh')->count();
        $upcomingEvents = $query->where('thoi_gian_bat_dau', '>', now())->count();
        $overdueEvents = $query->where('thoi_gian_ket_thuc', '<', now())
                              ->where('trang_thai', '!=', 'hoan_thanh')
                              ->count();
        
        $eventsByType = $query->select('loai_su_kien', DB::raw('count(*) as count'))
                             ->groupBy('loai_su_kien')
                             ->get()
                             ->toArray();
        
        $eventsByMonth = $query->select(DB::raw('MONTH(thoi_gian_bat_dau) as month'), DB::raw('count(*) as count'))
                              ->groupBy(DB::raw('MONTH(thoi_gian_bat_dau)'))
                              ->get()
                              ->toArray();
        
        return [
            'total_events' => $totalEvents,
            'completed_events' => $completedEvents,
            'upcoming_events' => $upcomingEvents,
            'overdue_events' => $overdueEvents,
            'events_by_type' => $eventsByType,
            'events_by_month' => $eventsByMonth
        ];
    }

    /**
     * Get calendar conflicts.
     */
    public function getConflicts(Carbon $startDate, Carbon $endDate, int $userId): array
    {
        return Calendar::where('nguoi_tham_gia_id', $userId)
                     ->where(function ($query) use ($startDate, $endDate) {
                         $query->whereBetween('thoi_gian_bat_dau', [$startDate, $endDate])
                               ->orWhereBetween('thoi_gian_ket_thuc', [$startDate, $endDate])
                               ->orWhere(function ($q) use ($startDate, $endDate) {
                                   $q->where('thoi_gian_bat_dau', '<=', $startDate)
                                     ->where('thoi_gian_ket_thuc', '>=', $endDate);
                               });
                     })
                     ->get()
                     ->toArray();
    }

    /**
     * Get calendar reminders.
     */
    public function getReminders(int $userId): array
    {
        // This would typically query a reminders table
        // For now, return events that need reminders
        return Calendar::where('nguoi_tham_gia_id', $userId)
                     ->where('thoi_gian_bat_dau', '>', now())
                     ->where('thoi_gian_bat_dau', '<=', now()->addDays(7))
                     ->get()
                     ->toArray();
    }

    /**
     * Get events with pagination and filtering.
     */
    public function getEventsWithFilters(array $filters, int $perPage = 15): array
    {
        $query = Calendar::query();
        
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date']);
            $endDate = Carbon::parse($filters['end_date']);
            $query->whereBetween('thoi_gian_bat_dau', [$startDate, $endDate]);
        }
        
        if (isset($filters['event_type'])) {
            $query->where('loai_su_kien', $filters['event_type']);
        }
        
        if (isset($filters['user_id'])) {
            $query->where('nguoi_tham_gia_id', $filters['user_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('trang_thai', $filters['status']);
        }
        
        if (isset($filters['search'])) {
            $query->where('tieu_de', 'like', '%' . $filters['search'] . '%');
        }
        
        return $query->paginate($perPage)->toArray();
    }

    /**
     * Get upcoming events for user.
     */
    public function getUpcomingEvents(int $userId, int $limit = 10): array
    {
        return Calendar::where('nguoi_tham_gia_id', $userId)
                     ->where('thoi_gian_bat_dau', '>', now())
                     ->orderBy('thoi_gian_bat_dau')
                     ->limit($limit)
                     ->get()
                     ->toArray();
    }

    /**
     * Get overdue events for user.
     */
    public function getOverdueEvents(int $userId): array
    {
        return Calendar::where('nguoi_tham_gia_id', $userId)
                     ->where('thoi_gian_ket_thuc', '<', now())
                     ->where('trang_thai', '!=', 'hoan_thanh')
                     ->get()
                     ->toArray();
    }

    /**
     * Get events by type.
     */
    public function getEventsByType(string $type, int $userId = null): array
    {
        $query = Calendar::where('loai_su_kien', $type);
        
        if ($userId) {
            $query->where('nguoi_tham_gia_id', $userId);
        }
        
        return $query->get()->toArray();
    }

    /**
     * Get events count by status.
     */
    public function getEventsCountByStatus(int $userId = null): array
    {
        $query = Calendar::query();
        
        if ($userId) {
            $query->where('nguoi_tham_gia_id', $userId);
        }
        
        return $query->select('trang_thai', DB::raw('count(*) as count'))
                    ->groupBy('trang_thai')
                    ->get()
                    ->toArray();
    }
}