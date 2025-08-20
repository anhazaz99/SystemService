<?php

namespace Modules\Task\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Task\app\Repositories\Interfaces\TaskRepositoryInterface;
use Modules\Task\app\Repositories\TaskRepository;
use Modules\Task\app\Services\Interfaces\TaskServiceInterface;
use Modules\Task\app\Services\TaskService;
use Modules\Task\app\Services\UserContextService;
use Modules\Task\app\UseCases\CreateTaskUseCase;
use Modules\Task\app\UseCases\CreateTaskWithPermissionsUseCase;
use Modules\Task\app\UseCases\GetFacultiesUseCase;
use Modules\Task\app\UseCases\GetClassesByFacultyUseCase;
use Modules\Task\app\UseCases\GetStudentsByClassUseCase;
use Modules\Task\app\UseCases\GetLecturersUseCase;
use Modules\Task\app\UseCases\Calendar\GetCalendarEventsUseCase;
use Modules\Task\app\UseCases\Calendar\GetUserEventsByRangeUseCase;
use Modules\Task\app\UseCases\Calendar\GetUpcomingEventsUseCase;
use Modules\Task\app\UseCases\Calendar\GetUserUpcomingEventsUseCase;
use Modules\Task\app\UseCases\Calendar\GetEventsByDateUseCase;
use Modules\Task\app\UseCases\Calendar\GetOverdueEventsUseCase;
use Modules\Task\app\UseCases\Calendar\GetEventsCountByStatusUseCase;
use Modules\Task\app\UseCases\Calendar\GetCalendarEventDetailsUseCase;
use Modules\Task\app\UseCases\Calendar\GetEventsByTypeUseCase;
use Modules\Task\app\UseCases\Calendar\GetRemindersUseCase;

/**
 * Service Provider cho Task Module
 * 
 * Tuân thủ Clean Architecture: Dependency Injection Container
 * Bind interfaces với concrete implementations
 */
class TaskServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind TaskRepository interface với implementation
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        
        // Bind TaskService interface với implementation
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
        
        // Bind UserContextService
        $this->app->singleton(UserContextService::class, UserContextService::class);
        
        // Bind Use Cases
        $this->app->bind(CreateTaskUseCase::class, CreateTaskUseCase::class);
        $this->app->bind(CreateTaskWithPermissionsUseCase::class, CreateTaskWithPermissionsUseCase::class);
        $this->app->bind(GetFacultiesUseCase::class, GetFacultiesUseCase::class);
        $this->app->bind(GetClassesByFacultyUseCase::class, GetClassesByFacultyUseCase::class);
        $this->app->bind(GetStudentsByClassUseCase::class, GetStudentsByClassUseCase::class);
        $this->app->bind(GetLecturersUseCase::class, GetLecturersUseCase::class);
        
        // Bind Calendar Use Cases
        $this->app->bind(GetCalendarEventsUseCase::class, GetCalendarEventsUseCase::class);
        $this->app->bind(GetUserEventsByRangeUseCase::class, GetUserEventsByRangeUseCase::class);
        $this->app->bind(GetUpcomingEventsUseCase::class, GetUpcomingEventsUseCase::class);
        $this->app->bind(GetUserUpcomingEventsUseCase::class, GetUserUpcomingEventsUseCase::class);
        $this->app->bind(GetEventsByDateUseCase::class, GetEventsByDateUseCase::class);
        $this->app->bind(GetOverdueEventsUseCase::class, GetOverdueEventsUseCase::class);
        $this->app->bind(GetEventsCountByStatusUseCase::class, GetEventsCountByStatusUseCase::class);
        $this->app->bind(GetCalendarEventDetailsUseCase::class, GetCalendarEventDetailsUseCase::class);
        $this->app->bind(GetEventsByTypeUseCase::class, GetEventsByTypeUseCase::class);
        $this->app->bind(GetRemindersUseCase::class, GetRemindersUseCase::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
