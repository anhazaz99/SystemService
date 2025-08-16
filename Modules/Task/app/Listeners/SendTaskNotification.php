<?php

namespace Modules\Task\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Modules\Task\Notifications\TaskAssigned;
use Modules\Task\Notifications\TaskReminder;
use Modules\Task\Notifications\TaskOverdue;
use Modules\Task\Notifications\TaskCompleted;
use App\Models\User;

class SendTaskNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            $eventType = $event->event_type ?? $this->getEventType($event);
            
            switch ($eventType) {
                case 'task_created':
                    $this->handleTaskCreated($event);
                    break;
                case 'task_assigned':
                    $this->handleTaskAssigned($event);
                    break;
                case 'task_updated':
                    $this->handleTaskUpdated($event);
                    break;
                case 'task_completed':
                    $this->handleTaskCompleted($event);
                    break;
                case 'task_overdue':
                    $this->handleTaskOverdue($event);
                    break;
                case 'task_deleted':
                    $this->handleTaskDeleted($event);
                    break;
                default:
                    Log::warning('Unknown event type: ' . $eventType);
            }
        } catch (\Exception $e) {
            Log::error('Error in SendTaskNotification listener: ' . $e->getMessage(), [
                'event' => $event,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle task created event.
     */
    private function handleTaskCreated($event): void
    {
        $assignee = $this->getUser($event->assignee_id);
        $creator = $this->getUser($event->creator_id);

        if ($assignee && $assignee->id !== $creator->id) {
            // Send notification to assignee
            $this->sendNotification($assignee, new TaskAssigned($event->task, $event->creator));
        }

        // Send confirmation to creator
        if ($creator) {
            $this->sendNotification($creator, new TaskAssigned($event->task, $creator, 'created'));
        }
    }

    /**
     * Handle task assigned event.
     */
    private function handleTaskAssigned($event): void
    {
        $assignee = $this->getUser($event->assignee_id);
        $assigner = $this->getUser($event->assigner_id ?? $event->creator_id);

        if ($assignee) {
            $this->sendNotification($assignee, new TaskAssigned($event->task, $assigner));
        }

        // Notify previous assignee if reassigned
        if ($event->isReassignment() && $event->previousAssignee) {
            $previousAssignee = $this->getUser($event->previousAssignee->id);
            if ($previousAssignee) {
                $this->sendNotification($previousAssignee, new TaskAssigned($event->task, $assigner, 'reassigned'));
            }
        }
    }

    /**
     * Handle task updated event.
     */
    private function handleTaskUpdated($event): void
    {
        $assignee = $this->getUser($event->assignee_id);
        $updater = $this->getUser($event->updater_id);

        // Notify assignee of important changes
        if ($assignee && $assignee->id !== $updater->id) {
            $importantChanges = ['status', 'priority', 'deadline'];
            $hasImportantChanges = array_intersect($importantChanges, $event->changes);
            
            if (!empty($hasImportantChanges)) {
                $this->sendNotification($assignee, new TaskAssigned($event->task, $updater, 'updated'));
            }
        }
    }

    /**
     * Handle task completed event.
     */
    private function handleTaskCompleted($event): void
    {
        $completer = $this->getUser($event->completer_id);
        $creator = $this->getUser($event->creator_id);

        // Notify creator
        if ($creator && $creator->id !== $completer->id) {
            $this->sendNotification($creator, new TaskCompleted($event->task, $completer));
        }

        // Notify assignee if different from completer
        if ($event->assignee_id && $event->assignee_id !== $event->completer_id) {
            $assignee = $this->getUser($event->assignee_id);
            if ($assignee) {
                $this->sendNotification($assignee, new TaskCompleted($event->task, $completer));
            }
        }
    }

    /**
     * Handle task overdue event.
     */
    private function handleTaskOverdue($event): void
    {
        $assignee = $this->getUser($event->assignee_id);
        $creator = $this->getUser($event->creator_id);

        // Notify assignee
        if ($assignee) {
            $this->sendNotification($assignee, new TaskOverdue($event->task, $event->overdueDays));
        }

        // Notify creator
        if ($creator && $creator->id !== $assignee->id) {
            $this->sendNotification($creator, new TaskOverdue($event->task, $event->overdueDays, 'creator'));
        }

        // Escalate to managers if needed
        if ($event->needsEscalation()) {
            $this->escalateToManagers($event);
        }
    }

    /**
     * Handle task deleted event.
     */
    private function handleTaskDeleted($event): void
    {
        $assignee = $this->getUser($event->assignee_id);
        $deleter = $this->getUser($event->deleter_id);

        // Notify assignee if different from deleter
        if ($assignee && $assignee->id !== $deleter->id) {
            $this->sendNotification($assignee, new TaskAssigned($event->task, $deleter, 'deleted'));
        }
    }

    /**
     * Send notification to user.
     */
    private function sendNotification($user, $notification): void
    {
        try {
            // Check user notification preferences
            if ($this->shouldSendNotification($user, $notification)) {
                Notification::send($user, $notification);
                
                Log::info('Notification sent', [
                    'user_id' => $user->id,
                    'notification_type' => get_class($notification),
                    'task_id' => $notification->task->id ?? null
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'notification_type' => get_class($notification)
            ]);
        }
    }

    /**
     * Get user by ID.
     */
    private function getUser($userId)
    {
        if (!$userId) {
            return null;
        }

        try {
            return User::find($userId);
        } catch (\Exception $e) {
            Log::error('Failed to get user: ' . $e->getMessage(), ['user_id' => $userId]);
            return null;
        }
    }

    /**
     * Check if notification should be sent based on user preferences.
     */
    private function shouldSendNotification($user, $notification): bool
    {
        // Check if user has disabled notifications
        if ($user->notification_preferences['disabled'] ?? false) {
            return false;
        }

        // Check specific notification type preferences
        $notificationType = $this->getNotificationType($notification);
        $preferences = $user->notification_preferences[$notificationType] ?? true;

        return $preferences;
    }

    /**
     * Get notification type from notification class.
     */
    private function getNotificationType($notification): string
    {
        $className = class_basename($notification);
        
        return strtolower(str_replace('Task', '', $className));
    }

    /**
     * Get event type from event object.
     */
    private function getEventType($event): string
    {
        $className = class_basename($event);
        
        return strtolower(str_replace('Task', '', $className));
    }

    /**
     * Escalate overdue task to managers.
     */
    private function escalateToManagers($event): void
    {
        try {
            $managers = User::where('role', 'manager')->orWhere('role', 'admin')->get();
            
            foreach ($managers as $manager) {
                $this->sendNotification($manager, new TaskOverdue($event->task, $event->overdueDays, 'escalation'));
            }
        } catch (\Exception $e) {
            Log::error('Failed to escalate to managers: ' . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, $exception): void
    {
        Log::error('SendTaskNotification job failed', [
            'event' => $event,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
