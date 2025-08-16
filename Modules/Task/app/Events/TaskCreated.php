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

class TaskCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $creator;
    public $assignee;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->creator = $task->creator;
        $this->assignee = $task->assignee;
        $this->timestamp = now();
        
        // Log the event
        Log::info('Task created', [
            'task_id' => $task->id,
            'title' => $task->title,
            'creator_id' => $task->creator_id,
            'assignee_id' => $task->assignee_id,
            'timestamp' => $this->timestamp
        ]);
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tasks'),
            new PrivateChannel('user.' . $this->task->assignee_id),
            new PrivateChannel('user.' . $this->task->creator_id),
        ];
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
            'creator_id' => $this->task->creator_id,
            'creator_name' => $this->creator ? $this->creator->name : 'Unknown',
            'assignee_id' => $this->task->assignee_id,
            'assignee_name' => $this->assignee ? $this->assignee->name : 'Unknown',
            'status' => $this->task->status ?? 'pending',
            'priority' => $this->task->priority ?? 'medium',
            'created_at' => $this->task->created_at,
            'event_type' => 'task_created',
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Get the event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'task.created';
    }
}
