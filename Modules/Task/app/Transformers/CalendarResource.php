<?php

namespace Modules\Task\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CalendarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->tieu_de,
            'description' => $this->mo_ta,
            'start_date' => $this->thoi_gian_bat_dau ? $this->thoi_gian_bat_dau->format('Y-m-d H:i:s') : null,
            'end_date' => $this->thoi_gian_ket_thuc ? $this->thoi_gian_ket_thuc->format('Y-m-d H:i:s') : null,
            'event_type' => $this->loai_su_kien,
            'task_id' => $this->task_id,
            'participant_id' => $this->nguoi_tham_gia_id,
            'participant_type' => $this->loai_nguoi_tham_gia,
            'creator_id' => $this->nguoi_tao_id,
            'creator_type' => $this->loai_nguoi_tao,
            'is_recurring' => $this->is_recurring ?? false,
            'recurring_pattern' => $this->recurring_pattern ?? null,
            'recurring_interval' => $this->recurring_interval ?? null,
            'recurring_end_date' => $this->recurring_end_date ? $this->recurring_end_date->format('Y-m-d') : null,
            'status' => $this->trang_thai ?? 'active',
            'priority' => $this->do_uu_tien ?? 'medium',
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            
            // Computed attributes
            'duration' => $this->getDuration(),
            'is_all_day' => $this->isAllDay(),
            'is_past' => $this->isPast(),
            'is_today' => $this->isToday(),
            'is_upcoming' => $this->isUpcoming(),
            'status_text' => $this->getStatusText(),
            'priority_text' => $this->getPriorityText(),
            'event_type_text' => $this->getEventTypeText(),
            
            // Relationships
            'task' => $this->whenLoaded('task', function () {
                return [
                    'id' => $this->task->id,
                    'title' => $this->task->tieu_de,
                    'status' => $this->task->trang_thai ?? null
                ];
            }),
            
            // Additional metadata
            'metadata' => [
                'can_edit' => $this->canEdit(),
                'can_delete' => $this->canDelete(),
                'reminders' => $this->getReminders(),
                'attendees' => $this->getAttendees(),
            ]
        ];
    }

    /**
     * Get event duration in minutes.
     */
    private function getDuration(): int
    {
        if (!$this->thoi_gian_bat_dau || !$this->thoi_gian_ket_thuc) {
            return 0;
        }
        
        return $this->thoi_gian_bat_dau->diffInMinutes($this->thoi_gian_ket_thuc);
    }

    /**
     * Check if event is all day.
     */
    private function isAllDay(): bool
    {
        if (!$this->thoi_gian_bat_dau || !$this->thoi_gian_ket_thuc) {
            return false;
        }
        
        $startTime = $this->thoi_gian_bat_dau->format('H:i:s');
        $endTime = $this->thoi_gian_ket_thuc->format('H:i:s');
        
        return $startTime === '00:00:00' && $endTime === '23:59:59';
    }

    /**
     * Check if event is in the past.
     */
    private function isPast(): bool
    {
        if (!$this->thoi_gian_ket_thuc) {
            return false;
        }
        
        return $this->thoi_gian_ket_thuc->isPast();
    }

    /**
     * Check if event is today.
     */
    private function isToday(): bool
    {
        if (!$this->thoi_gian_bat_dau) {
            return false;
        }
        
        return $this->thoi_gian_bat_dau->isToday();
    }

    /**
     * Check if event is upcoming.
     */
    private function isUpcoming(): bool
    {
        if (!$this->thoi_gian_bat_dau) {
            return false;
        }
        
        return $this->thoi_gian_bat_dau->isFuture();
    }

    /**
     * Get status text.
     */
    private function getStatusText(): string
    {
        return match($this->trang_thai ?? 'active') {
            'active' => 'Đang hoạt động',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            'postponed' => 'Đã hoãn',
            default => 'Không xác định'
        };
    }

    /**
     * Get priority text.
     */
    private function getPriorityText(): string
    {
        return match($this->do_uu_tien ?? 'medium') {
            'low' => 'Thấp',
            'medium' => 'Trung bình',
            'high' => 'Cao',
            'urgent' => 'Khẩn cấp',
            default => 'Không xác định'
        };
    }

    /**
     * Get event type text.
     */
    private function getEventTypeText(): string
    {
        return match($this->loai_su_kien) {
            'task' => 'Công việc',
            'su_kien' => 'Sự kiện',
            default => 'Không xác định'
        };
    }

    /**
     * Check if user can edit this event.
     */
    private function canEdit(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        // Creator can always edit
        if ($this->nguoi_tao_id === $user->id) {
            return true;
        }
        
        // Participant can edit if event is not completed
        if ($this->nguoi_tham_gia_id === $user->id && ($this->trang_thai ?? 'active') !== 'completed') {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can delete this event.
     */
    private function canDelete(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        // Only creator can delete
        return $this->nguoi_tao_id === $user->id;
    }

    /**
     * Get event reminders.
     */
    private function getReminders(): array
    {
        // This would typically query reminders table
        // For now, return default reminders
        return [
            [
                'type' => 'email',
                'time' => '15_minutes_before',
                'enabled' => true
            ],
            [
                'type' => 'push',
                'time' => '1_hour_before',
                'enabled' => true
            ]
        ];
    }

    /**
     * Get event attendees.
     */
    private function getAttendees(): array
    {
        return [
            [
                'id' => $this->nguoi_tham_gia_id,
                'type' => $this->loai_nguoi_tham_gia,
                'role' => 'participant'
            ],
            [
                'id' => $this->nguoi_tao_id,
                'type' => $this->loai_nguoi_tao,
                'role' => 'creator'
            ]
        ];
    }
}