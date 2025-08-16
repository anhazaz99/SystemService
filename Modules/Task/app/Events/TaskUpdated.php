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

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $changes;
    public $updater;
    public $timestamp;
    public $oldValues;
    public $newValues;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, array $changes, array $oldValues = [], array $newValues = [])
    {
        $this->task = $task;
        $this->changes = $changes;
        $this->oldValues = $oldValues;
        $this->newValues = $newValues;
        $this->updater = auth()->user();
        $this->timestamp = now();
        
        // Log the event with detailed changes
        Log::info('Task updated', [
            'task_id' => $task->id,
            'title' => $task->title,
            'updater_id' => $this->updater ? $this->updater->id : null,
            'changes' => $changes,
            'old_values' => $oldValues,
            'new_values' => $newValues,
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
            new PrivateChannel('user.' . ($this->updater ? $this->updater->id : 0)),
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
            'updater_id' => $this->updater ? $this->updater->id : null,
            'updater_name' => $this->updater ? $this->updater->name : 'System',
            'changes' => $this->changes,
            'old_values' => $this->oldValues,
            'new_values' => $this->newValues,
            'status' => $this->task->status,
            'priority' => $this->task->priority,
            'updated_at' => $this->task->updated_at,
            'event_type' => 'task_updated',
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Get the event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'task.updated';
    }

    /**
     * Check if specific field was changed.
     */
    public function wasChanged(string $field): bool
    {
        return in_array($field, $this->changes);
    }

    /**
     * Get old value for specific field.
     */
    public function getOldValue(string $field)
    {
        return $this->oldValues[$field] ?? null;
    }

    /**
     * Get new value for specific field.
     */
    public function getNewValue(string $field)
    {
        return $this->newValues[$field] ?? null;
    }
}
