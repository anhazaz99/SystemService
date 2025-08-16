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

class TaskCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $completer;
    public $timestamp;
    public $completionTime;
    public $performanceMetrics;
    public $completionNotes;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, $completer = null, $completionNotes = null, array $performanceMetrics = [])
    {
        $this->task = $task;
        $this->completer = $completer ?? auth()->user();
        $this->completionNotes = $completionNotes;
        $this->performanceMetrics = $performanceMetrics;
        $this->timestamp = now();
        $this->completionTime = $this->calculateCompletionTime();
        
        // Log the completion event
        Log::info('Task completed', [
            'task_id' => $task->id,
            'title' => $task->title,
            'completer_id' => $this->completer ? $this->completer->id : null,
            'completion_time' => $this->completionTime,
            'performance_metrics' => $this->performanceMetrics,
            'completion_notes' => $this->completionNotes,
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
            new PrivateChannel('user.' . $this->task->creator_id),
        ];

        // Add completer channel
        if ($this->completer) {
            $channels[] = new PrivateChannel('user.' . $this->completer->id);
        }

        // Add assignee channel if different from completer
        if ($this->task->assignee_id && (!$this->completer || $this->task->assignee_id !== $this->completer->id)) {
            $channels[] = new PrivateChannel('user.' . $this->task->assignee_id);
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
            'completer_id' => $this->completer ? $this->completer->id : null,
            'completer_name' => $this->completer ? $this->completer->name : 'System',
            'completion_time' => $this->completionTime,
            'performance_metrics' => $this->performanceMetrics,
            'completion_notes' => $this->completionNotes,
            'status' => $this->task->status,
            'priority' => $this->task->priority,
            'deadline' => $this->task->deadline,
            'completed_at' => $this->timestamp,
            'event_type' => 'task_completed',
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Get the event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'task.completed';
    }

    /**
     * Calculate completion time in hours.
     */
    private function calculateCompletionTime(): float
    {
        if (!$this->task->created_at) {
            return 0;
        }

        $startTime = $this->task->created_at;
        $endTime = $this->timestamp;
        
        return $startTime->diffInHours($endTime, true);
    }

    /**
     * Get completion time.
     */
    public function getCompletionTime(): float
    {
        return $this->completionTime;
    }

    /**
     * Get performance metrics.
     */
    public function getPerformanceMetrics(): array
    {
        return $this->performanceMetrics;
    }

    /**
     * Get completion notes.
     */
    public function getCompletionNotes(): ?string
    {
        return $this->completionNotes;
    }

    /**
     * Check if task was completed on time.
     */
    public function wasCompletedOnTime(): bool
    {
        if (!$this->task->deadline) {
            return true;
        }

        return $this->timestamp <= $this->task->deadline;
    }

    /**
     * Check if task was completed early.
     */
    public function wasCompletedEarly(): bool
    {
        if (!$this->task->deadline) {
            return false;
        }

        return $this->timestamp < $this->task->deadline;
    }

    /**
     * Get days early/late.
     */
    public function getDaysEarlyLate(): int
    {
        if (!$this->task->deadline) {
            return 0;
        }

        return $this->task->deadline->diffInDays($this->timestamp, false);
    }
}
