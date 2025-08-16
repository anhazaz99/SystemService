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

class TaskDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $deleter;
    public $timestamp;
    public $taskData;
    public $relatedData;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, array $taskData = [], array $relatedData = [])
    {
        $this->task = $task;
        $this->taskData = $taskData;
        $this->relatedData = $relatedData;
        $this->deleter = auth()->user();
        $this->timestamp = now();
        
        // Log the event with task data for backup
        Log::info('Task deleted', [
            'task_id' => $task->id,
            'title' => $task->title,
            'deleter_id' => $this->deleter ? $this->deleter->id : null,
            'task_data' => $taskData,
            'related_data' => $relatedData,
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
            new PrivateChannel('user.' . ($this->deleter ? $this->deleter->id : 0)),
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
            'deleter_id' => $this->deleter ? $this->deleter->id : null,
            'deleter_name' => $this->deleter ? $this->deleter->name : 'System',
            'task_data' => $this->taskData,
            'related_data' => $this->relatedData,
            'deleted_at' => $this->timestamp,
            'event_type' => 'task_deleted',
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Get the event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'task.deleted';
    }

    /**
     * Get task data for backup purposes.
     */
    public function getTaskData(): array
    {
        return $this->taskData;
    }

    /**
     * Get related data for cleanup.
     */
    public function getRelatedData(): array
    {
        return $this->relatedData;
    }

    /**
     * Check if task had files.
     */
    public function hadFiles(): bool
    {
        return isset($this->relatedData['files']) && count($this->relatedData['files']) > 0;
    }

    /**
     * Check if task had comments.
     */
    public function hadComments(): bool
    {
        return isset($this->relatedData['comments']) && count($this->relatedData['comments']) > 0;
    }

    /**
     * Check if task had dependencies.
     */
    public function hadDependencies(): bool
    {
        return isset($this->relatedData['dependencies']) && count($this->relatedData['dependencies']) > 0;
    }
}
