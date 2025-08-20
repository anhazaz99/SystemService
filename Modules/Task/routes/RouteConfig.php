<?php

namespace Modules\Task\routes;

use Modules\Task\app\Http\Controllers\Task\TaskController;
use Modules\Task\app\Http\Controllers\Calendar\CalendarController;

/**
 * Cấu hình routes cho module Task với JWT và phân quyền
 * 
 * Class này chứa tất cả cấu hình routes để dễ dàng quản lý và bảo trì
 */
class RouteConfig
{
    /**
     * Cấu hình routes cho tất cả người dùng đã đăng nhập (JWT)
     * 
     * @return array
     */
    public static function getCommonRoutes(): array
    {
        return [
            'middleware' => ['jwt'],
            'prefix' => 'v1',
            'tasks' => [
                'prefix' => 'tasks',
                'controller' => TaskController::class,
                'name' => 'tasks',
                'additional_routes' => [
                    [
                        'methods' => ['GET'],
                        'uri' => 'my-tasks',
                        'action' => 'getMyTasks',
                        'name' => 'my-tasks'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'statistics/my',
                        'action' => 'getMyStatistics',
                        'name' => 'my-statistics'
                    ],
                    [
                        'methods' => ['PATCH'],
                        'uri' => '{task}/status',
                        'action' => 'updateStatus',
                        'name' => 'update-status'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => '{task}/files',
                        'action' => 'uploadFiles',
                        'name' => 'upload-files'
                    ],
                    [
                        'methods' => ['DELETE'],
                        'uri' => '{task}/files/{file}',
                        'action' => 'deleteFile',
                        'name' => 'delete-file'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'faculties',
                        'action' => 'getFaculties',
                        'name' => 'faculties'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'classes/by-faculty',
                        'action' => 'getClassesByFaculty',
                        'name' => 'classes.by-faculty'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'students/by-class',
                        'action' => 'getStudentsByClass',
                        'name' => 'students.by-class'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'lecturers',
                        'action' => 'getLecturers',
                        'name' => 'lecturers'
                    ],
                ],
                'resource_only' => ['show'] // Chỉ cho phép xem chi tiết
            ],
            'calendar' => [
                'prefix' => 'calendar',
                'controller' => CalendarController::class,
                'name' => 'calendar',
                'routes' => [
                    [
                        'methods' => ['GET'],
                        'uri' => 'events/by-date',
                        'action' => 'eventsByDate',
                        'name' => 'events.by-date'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'events/by-range',
                        'action' => 'eventsByRange',
                        'name' => 'events.by-range'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'events/upcoming',
                        'action' => 'upcomingEvents',
                        'name' => 'events.upcoming'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'events/overdue',
                        'action' => 'overdueEvents',
                        'name' => 'events.overdue'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'events/count-by-status',
                        'action' => 'eventsCountByStatus',
                        'name' => 'events.count-by-status'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'reminders',
                        'action' => 'reminders',
                        'name' => 'reminders'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => 'reminders',
                        'action' => 'setReminder',
                        'name' => 'reminders.set'
                    ],
                ]
            ]
        ];
    }

    /**
     * Cấu hình routes chỉ dành cho Giảng viên
     * 
     * @return array
     */
    public static function getLecturerRoutes(): array
    {
        return [
            'middleware' => ['jwt', 'lecturer'],
            'prefix' => 'v1',
            'tasks' => [
                'prefix' => 'tasks',
                'controller' => TaskController::class,
                'name' => 'tasks',
                'additional_routes' => [
                    [
                        'methods' => ['GET'],
                        'uri' => 'lecturer/created',
                        'action' => 'getCreatedTasks',
                        'name' => 'created'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'statistics/created',
                        'action' => 'getCreatedStatistics',
                        'name' => 'created-statistics'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => '{task}/assign',
                        'action' => 'assignTask',
                        'name' => 'assign'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => '{task}/revoke',
                        'action' => 'revokeTask',
                        'name' => 'revoke'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => 'recurring',
                        'action' => 'createRecurringTask',
                        'name' => 'recurring'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => 'create-with-permissions',
                        'action' => 'createTaskWithPermissions',
                        'name' => 'create-with-permissions'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => 'generate-report',
                        'action' => 'generateReport',
                        'name' => 'generate-report'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => 'sync-data',
                        'action' => 'syncData',
                        'name' => 'sync-data'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => '{task}/process-files',
                        'action' => 'processTaskFiles',
                        'name' => 'process-files'
                    ],
                ],
                'resource_actions' => ['store', 'update', 'destroy'] // Tạo, sửa, xóa
            ],
            'calendar' => [
                'prefix' => 'calendar',
                'controller' => CalendarController::class,
                'name' => 'calendar',
                'routes' => [
                    [
                        'methods' => ['POST'],
                        'uri' => 'events',
                        'action' => 'store',
                        'name' => 'events.store'
                    ],
                    [
                        'methods' => ['PUT'],
                        'uri' => 'events/{calendar}',
                        'action' => 'update',
                        'name' => 'events.update'
                    ],
                    [
                        'methods' => ['DELETE'],
                        'uri' => 'events/{calendar}',
                        'action' => 'destroy',
                        'name' => 'events.destroy'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => 'events/recurring',
                        'action' => 'createRecurring',
                        'name' => 'events.recurring'
                    ],
                    [
                        'methods' => ['PUT'],
                        'uri' => 'events/{calendar}/recurring',
                        'action' => 'updateRecurring',
                        'name' => 'events.recurring.update'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => 'export',
                        'action' => 'export',
                        'name' => 'export'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => 'import',
                        'action' => 'import',
                        'name' => 'import'
                    ],
                ]
            ]
        ];
    }

    /**
     * Cấu hình routes chỉ dành cho Admin
     * 
     * @return array
     */
    public static function getAdminRoutes(): array
    {
        return [
            'middleware' => ['jwt', 'admin'],
            'prefix' => 'v1',
            'tasks' => [
                'prefix' => 'tasks',
                'controller' => TaskController::class,
                'name' => 'tasks',
                'additional_routes' => [
                    [
                        'methods' => ['GET'],
                        'uri' => 'all',
                        'action' => 'getAllTasks',
                        'name' => 'all'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'statistics/overview',
                        'action' => 'getOverviewStatistics',
                        'name' => 'overview-statistics'
                    ],
                    [
                        'methods' => ['DELETE'],
                        'uri' => '{task}/force',
                        'action' => 'forceDelete',
                        'name' => 'force-delete'
                    ],
                    [
                        'methods' => ['POST'],
                        'uri' => '{task}/restore',
                        'action' => 'restore',
                        'name' => 'restore'
                    ],
                ],
                'resource_actions' => ['index'] // Xem tất cả
            ],
            'calendar' => [
                'prefix' => 'calendar',
                'controller' => CalendarController::class,
                'name' => 'calendar',
                'routes' => [
                    [
                        'methods' => ['GET'],
                        'uri' => 'events',
                        'action' => 'index',
                        'name' => 'events.index'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'events/by-type',
                        'action' => 'eventsByType',
                        'name' => 'events.by-type'
                    ],
                    [
                        'methods' => ['GET'],
                        'uri' => 'events/recurring',
                        'action' => 'recurringEvents',
                        'name' => 'events.recurring'
                    ],
                ]
            ]
        ];
    }

    /**
     * Lấy tất cả cấu hình routes theo cấp độ phân quyền
     * 
     * @return array
     */
    public static function getAllRoutes(): array
    {
        return [
            'common' => self::getCommonRoutes(),
            'lecturer' => self::getLecturerRoutes(),
            'admin' => self::getAdminRoutes(),
        ];
    }
}
