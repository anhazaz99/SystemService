<?php

namespace Modules\Task\Observers;

use Modules\Task\app\Models\TaskObserver;

class TaskObserverObserver
{
    /**
     * Handle the TaskObserver "created" event.
     */
    public function created(TaskObserver $taskobserver): void {}

    /**
     * Handle the TaskObserver "updated" event.
     */
    public function updated(TaskObserver $taskobserver): void {}

    /**
     * Handle the TaskObserver "deleted" event.
     */
    public function deleted(TaskObserver $taskobserver): void {}

    /**
     * Handle the TaskObserver "restored" event.
     */
    public function restored(TaskObserver $taskobserver): void {}

    /**
     * Handle the TaskObserver "force deleted" event.
     */
    public function forceDeleted(TaskObserver $taskobserver): void {}
}
