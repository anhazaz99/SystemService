<?php

namespace Modules\Task\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class TaskOverdue implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $assignee;
    public $creator;
    public $timestamp;
    public $overdueDays;
    public $escalationLevel;
    public $penaltyAmount;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, $assignee = null, $creator = null, $escalationLevel = 1, $penaltyAmount = 0)
    {
        $this->task = $task;
        $this->assignee = $assignee;
        $this->creator = $creator;
        $this->escalationLevel = $escalationLevel;
        $this->penaltyAmount = $penaltyAmount;
        $this->timestamp = now();
        $this->overdueDays = $this->calculateOverdueDays();
        
        // Log the overdue event
        Log::warning('Task overdue', [
            'task_id' => $task->id,
            'title' => $task->title,
            'assignee_id' => $this->assignee ? $this->assignee->id : null,
            'creator_id' => $this->creator ? $this->creator->id : null,
            'overdue_days' => $this->overdueDays,
            'escalation_level' => $this->escalationLevel,
            'penalty_amount' => $this->penaltyAmount,
            'deadline' => $task->deadline,
            'timestamp' => $this->timestamp
        ]);
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('tasks'),
            new PrivateChannel('task-overdue'),
        ];

        // Add assignee channel
        if ($this->assignee) {
            $channels[] = new PrivateChannel('user.' . $this->assignee->id);
        }

        // Add creator channel
        if ($this->creator) {
            $channels[] = new PrivateChannel('user.' . $this->creator->id);
        }

        // Add manager channels based on escalation level
        if ($this->escalationLevel >= 2) {
            $channels[] = new PrivateChannel('managers');
        }

        if ($this->escalationLevel >= 3) {
            $channels[] = new PrivateChannel('administrators');
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'description' => $this->task->description,
            'assignee_id' => $this->assignee ? $this->assignee->id : null,
            'assignee_name' => $this->assignee ? $this->assignee->name : 'Unknown',
            'creator_id' => $this->creator ? $this->creator->id : null,
            'creator_name' => $this->creator ? $this->creator->name : 'Unknown',
            'overdue_days' => $this->overdueDays,
            'escalation_level' => $this->escalationLevel,
            'penalty_amount' => $this->penaltyAmount,
            'status' => $this->task->status,
            'priority' => $this->task->priority,
            'deadline' => $this->task->deadline,
            'overdue_since' => $this->task->deadline,
            'event_type' => 'task_overdue',
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Get the event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'task.overdue';
    }

    /**
     * Calculate overdue days.
     */
    private function calculateOverdueDays(): int
    {
        if (!$this->task->deadline) {
            return 0;
        }

        return max(0, $this->task->deadline->diffInDays($this->timestamp, false));
    }

    /**
     * Get overdue days.
     */
    public function getOverdueDays(): int
    {
        return $this->overdueDays;
    }

    /**
     * Get escalation level.
     */
    public function getEscalationLevel(): int
    {
        return $this->escalationLevel;
    }

    /**
     * Get penalty amount.
     */
    public function getPenaltyAmount(): float
    {
        return $this->penaltyAmount;
    }

    /**
     * Check if task is severely overdue (more than 7 days).
     */
    public function isSeverelyOverdue(): bool
    {
        return $this->overdueDays > 7;
    }

    /**
     * Check if task is critically overdue (more than 30 days).
     */
    public function isCriticallyOverdue(): bool
    {
        return $this->overdueDays > 30;
    }

    /**
     * Get overdue severity level.
     */
    public function getOverdueSeverity(): string
    {
        if ($this->overdueDays <= 1) {
            return 'minor';
        } elseif ($this->overdueDays <= 7) {
            return 'moderate';
        } elseif ($this->overdueDays <= 30) {
            return 'severe';
        } else {
            return 'critical';
        }
    }

    /**
     * Check if escalation is needed.
     */
    public function needsEscalation(): bool
    {
        return $this->escalationLevel >= 2;
    }

    /**
     * Check if penalty should be applied.
     */
    public function shouldApplyPenalty(): bool
    {
        return $this->penaltyAmount > 0;
    }
}
