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

class TaskAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $assigner;
    public $assignee;
    public $previousAssignee;
    public $timestamp;
    public $assignmentReason;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, $assignee, $assigner = null, $previousAssignee = null, $assignmentReason = null)
    {
        $this->task = $task;
        $this->assignee = $assignee;
        $this->assigner = $assigner ?? auth()->user();
        $this->previousAssignee = $previousAssignee;
        $this->assignmentReason = $assignmentReason;
        $this->timestamp = now();
        
        // Log the assignment event
        Log::info('Task assigned', [
            'task_id' => $task->id,
            'title' => $task->title,
            'assigner_id' => $this->assigner ? $this->assigner->id : null,
            'assignee_id' => $this->assignee ? $this->assignee->id : null,
            'previous_assignee_id' => $this->previousAssignee ? $this->previousAssignee->id : null,
            'assignment_reason' => $this->assignmentReason,
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

        // Add assignee channel
        if ($this->assignee) {
            $channels[] = new PrivateChannel('user.' . $this->assignee->id);
        }

        // Add assigner channel
        if ($this->assigner) {
            $channels[] = new PrivateChannel('user.' . $this->assigner->id);
        }

        // Add previous assignee channel
        if ($this->previousAssignee) {
            $channels[] = new PrivateChannel('user.' . $this->previousAssignee->id);
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
            'assigner_id' => $this->assigner ? $this->assigner->id : null,
            'assigner_name' => $this->assigner ? $this->assigner->name : 'System',
            'assignee_id' => $this->assignee ? $this->assignee->id : null,
            'assignee_name' => $this->assignee ? $this->assignee->name : 'Unknown',
            'previous_assignee_id' => $this->previousAssignee ? $this->previousAssignee->id : null,
            'previous_assignee_name' => $this->previousAssignee ? $this->previousAssignee->name : null,
            'assignment_reason' => $this->assignmentReason,
            'status' => $this->task->status,
            'priority' => $this->task->priority,
            'deadline' => $this->task->deadline,
            'assigned_at' => $this->timestamp,
            'event_type' => 'task_assigned',
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Get the event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'task.assigned';
    }

    /**
     * Check if this is a reassignment.
     */
    public function isReassignment(): bool
    {
        return $this->previousAssignee !== null;
    }

    /**
     * Get assignment reason.
     */
    public function getAssignmentReason(): ?string
    {
        return $this->assignmentReason;
    }

    /**
     * Check if assignee changed.
     */
    public function hasAssigneeChanged(): bool
    {
        if (!$this->previousAssignee || !$this->assignee) {
            return false;
        }
        return $this->previousAssignee->id !== $this->assignee->id;
    }
}
