<?php

namespace Modules\Task\app\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use Modules\Task\app\Models\Task;
use Modules\Task\app\Services\Interfaces\TaskServiceInterface;
use Modules\Task\app\Services\UserContextService;
use Modules\Task\app\Http\Requests\TaskRequest;
use Modules\Task\app\Transformers\TaskResource;
use Modules\Task\app\DTOs\TaskDTO;
use Modules\Task\app\UseCases\CreateTaskUseCase;
use Modules\Task\app\UseCases\CreateTaskWithPermissionsUseCase;
use Modules\Task\app\UseCases\GetFacultiesUseCase;
use Modules\Task\app\UseCases\GetClassesByFacultyUseCase;
use Modules\Task\app\UseCases\GetStudentsByClassUseCase;
use Modules\Task\app\UseCases\GetLecturersUseCase;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Task\app\Jobs\ProcessTaskJob;
use Modules\Task\app\Jobs\ProcessTaskFileJob;
use Modules\Task\app\Jobs\GenerateTaskReportJob;
use Modules\Task\app\Jobs\SyncTaskDataJob;

/**
 * Controller quản lý các thao tác với Task
 * 
 * Controller này xử lý tất cả các HTTP requests liên quan đến Task
 * Tuân thủ Clean Architecture: chỉ xử lý HTTP requests/responses, không chứa business logic
 */
class TaskController extends Controller
{
    protected $taskService;
    protected $createTaskUseCase;
    protected $createTaskWithPermissionsUseCase;
    protected $userContextService;
    protected $getFacultiesUseCase;
    protected $getClassesByFacultyUseCase;
    protected $getStudentsByClassUseCase;
    protected $getLecturersUseCase;

    /**
     * Khởi tạo controller với dependency injection
     * 
     * @param TaskServiceInterface $taskService Service chứa business logic
     * @param CreateTaskUseCase $createTaskUseCase Use case tạo task
     * @param CreateTaskWithPermissionsUseCase $createTaskWithPermissionsUseCase Use case tạo task với permissions
     * @param UserContextService $userContextService Service xử lý user context
     * @param GetFacultiesUseCase $getFacultiesUseCase Use case lấy faculties
     * @param GetClassesByFacultyUseCase $getClassesByFacultyUseCase Use case lấy classes
     * @param GetStudentsByClassUseCase $getStudentsByClassUseCase Use case lấy students
     * @param GetLecturersUseCase $getLecturersUseCase Use case lấy lecturers
     */
    public function __construct(
        TaskServiceInterface $taskService,
        CreateTaskUseCase $createTaskUseCase,
        CreateTaskWithPermissionsUseCase $createTaskWithPermissionsUseCase,
        UserContextService $userContextService,
        GetFacultiesUseCase $getFacultiesUseCase,
        GetClassesByFacultyUseCase $getClassesByFacultyUseCase,
        GetStudentsByClassUseCase $getStudentsByClassUseCase,
        GetLecturersUseCase $getLecturersUseCase
    ) {
        $this->taskService = $taskService;
        $this->createTaskUseCase = $createTaskUseCase;
        $this->createTaskWithPermissionsUseCase = $createTaskWithPermissionsUseCase;
        $this->userContextService = $userContextService;
        $this->getFacultiesUseCase = $getFacultiesUseCase;
        $this->getClassesByFacultyUseCase = $getClassesByFacultyUseCase;
        $this->getStudentsByClassUseCase = $getStudentsByClassUseCase;
        $this->getLecturersUseCase = $getLecturersUseCase;
    }

    /**
     * Hiển thị danh sách tất cả tasks với phân trang và bộ lọc
     * 
     * @param Request $request Request chứa các tham số lọc và phân trang
     * @return JsonResponse Response JSON chứa danh sách tasks
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['receiver_id', 'receiver_type', 'creator_id', 'creator_type', 'search']);
        $perPage = $request->get('per_page', 15);
        
        $tasks = $this->taskService->getTasksWithFilters($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => TaskResource::collection($tasks->items()),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage()
            ],
            'message' => 'Tasks retrieved successfully'
        ]);
    }

    /**
     * Tạo mới một task
     * 
     * @param TaskRequest $request Request đã được validate
     * @return JsonResponse Response JSON chứa thông tin task vừa tạo
     */
    public function store(TaskRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $task = $this->createTaskUseCase->execute($validatedData);
            
            // Dispatch ProcessTaskJob để xử lý background tasks
            if ($task && $task->id) {
                ProcessTaskJob::dispatch($task->toArray(), 'task_created')
                    ->onQueue('high')
                    ->delay(now()->addSeconds(30));
            }
            
            return response()->json([
                'success' => true,
                'data' => new TaskResource($task),
                'message' => 'Task created successfully and is being processed'
            ], 201);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the task'
            ], 500);
        }
    }

    /**
     * Hiển thị thông tin chi tiết của một task
     * 
     * @param Task $task Task cần hiển thị (được inject tự động bởi Laravel)
     * @return JsonResponse Response JSON chứa thông tin task
     */
    public function show(Task $task): JsonResponse
    {
        $task = $this->taskService->getTaskById($task->id);
        
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
            'message' => 'Task retrieved successfully'
        ]);
    }

    /**
     * Cập nhật thông tin của một task
     * 
     * @param TaskRequest $request Request chứa dữ liệu cập nhật đã được validate
     * @param Task $task Task cần cập nhật
     * @return JsonResponse Response JSON chứa thông tin task sau khi cập nhật
     */
    public function update(TaskRequest $request, Task $task): JsonResponse
    {
        $data = $request->validated();
        $task = $this->taskService->updateTask($task, $data);
        
        // Dispatch ProcessTaskJob để xử lý background tasks khi update
        ProcessTaskJob::dispatch($task->toArray(), 'task_updated', $data)
            ->onQueue('high')
            ->delay(now()->addSeconds(20));
        
        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
            'message' => 'Task updated successfully and is being processed'
        ]);
    }

    /**
     * Xóa một task
     * 
     * @param Task $task Task cần xóa
     * @return JsonResponse Response JSON thông báo xóa thành công
     */
    public function destroy(Task $task): JsonResponse
    {
        $taskId = $task->id;
        $this->taskService->deleteTask($task);
        
        // Dispatch ProcessTaskJob để xử lý cleanup tasks
        ProcessTaskJob::dispatch(['id' => $taskId], 'task_deleted')
            ->onQueue('cleanup')
            ->delay(now()->addMinutes(5));
        
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully and cleanup is scheduled'
        ]);
    }



    /**
     * Lấy thống kê tổng quan về tasks
     * 
     * @return JsonResponse Response JSON chứa các thống kê về tasks
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->taskService->getTaskStatistics();
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Task statistics retrieved successfully'
        ]);
    }

    /**
     * Lấy danh sách tasks của người dùng hiện tại
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getMyTasks(Request $request): JsonResponse
    {
        // Get user info from JWT middleware
        $userId = $request->attributes->get('jwt_user_id');
        $userType = $request->attributes->get('jwt_user_type');
        
        // Kiểm tra user có tồn tại không
        if (!$userId || !$userType) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $perPage = $request->get('per_page', 15);
        
        // Create a mock user object for the service
        $user = (object) [
            'id' => $userId,
            'user_type' => $userType
        ];
        
        $tasks = $this->taskService->getTasksForCurrentUser($user, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => TaskResource::collection($tasks->items()),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage()
            ],
            'message' => 'My tasks retrieved successfully'
        ]);
    }

    /**
     * Lấy danh sách tasks đã tạo (chỉ giảng viên)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCreatedTasks(Request $request): JsonResponse
    {
        // Get user info from JWT middleware
        $userId = $request->attributes->get('jwt_user_id');
        $userType = $request->attributes->get('jwt_user_type');
        
        // Kiểm tra user có tồn tại không
        if (!$userId || !$userType) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $perPage = $request->get('per_page', 15);
        
        // Create a mock user object for the service
        $user = (object) [
            'id' => $userId,
            'user_type' => $userType
        ];
        
        $tasks = $this->taskService->getTasksCreatedByUser($user, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => TaskResource::collection($tasks->items()),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage()
            ],
            'message' => 'Created tasks retrieved successfully'
        ]);
    }

    /**
     * Cập nhật trạng thái task (chỉ người nhận task)
     * 
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        // Get user info from JWT middleware
        $userId = $request->attributes->get('jwt_user_id');
        $userType = $request->attributes->get('jwt_user_type');
        
        // Kiểm tra user có tồn tại không
        if (!$userId || !$userType) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $status = $request->get('status');
        
        // Create a mock user object for the service
        $user = (object) [
            'id' => $userId,
            'user_type' => $userType
        ];
        
        // Kiểm tra quyền: chỉ người nhận task mới được cập nhật trạng thái
        if (!$this->taskService->canUpdateTaskStatus($user, $task)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật trạng thái task này'
            ], 403);
        }
        
        $updatedTask = $this->taskService->updateTaskStatus($task, $status);
        
        // Dispatch ProcessTaskJob để xử lý background tasks khi status thay đổi
        ProcessTaskJob::dispatch($task->toArray(), 'status_updated', ['old_status' => $task->status, 'new_status' => $status])
            ->onQueue('high')
            ->delay(now()->addSeconds(15));
        
        return response()->json([
            'success' => true,
            'data' => new TaskResource($updatedTask),
            'message' => 'Task status updated successfully and is being processed'
        ]);
    }



    /**
     * Lấy thống kê tasks của người dùng hiện tại
     * 
     * @return JsonResponse
     */
    public function getMyStatistics(Request $request): JsonResponse
    {
        // Get user info from JWT middleware
        $userId = $request->attributes->get('jwt_user_id');
        $userType = $request->attributes->get('jwt_user_type');
        
        // Kiểm tra user có tồn tại không
        if (!$userId || !$userType) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        // Create a mock user object for the service
        $user = (object) [
            'id' => $userId,
            'user_type' => $userType
        ];
        
        $statistics = $this->taskService->getUserTaskStatistics($user);
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'My task statistics retrieved successfully'
        ]);
    }

    /**
     * Lấy thống kê tasks đã tạo (chỉ giảng viên)
     * 
     * @return JsonResponse
     */
    public function getCreatedStatistics(Request $request): JsonResponse
    {
        // Get user info from JWT middleware
        $userId = $request->attributes->get('jwt_user_id');
        $userType = $request->attributes->get('jwt_user_type');
        
        // Kiểm tra user có tồn tại không
        if (!$userId || !$userType) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        // Create a mock user object for the service
        $user = (object) [
            'id' => $userId,
            'user_type' => $userType
        ];
        
        $statistics = $this->taskService->getCreatedTaskStatistics($user);
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Created task statistics retrieved successfully'
        ]);
    }

    /**
     * Lấy thống kê tổng quan (chỉ admin)
     * 
     * @return JsonResponse
     */
    public function getOverviewStatistics(): JsonResponse
    {
        $statistics = $this->taskService->getOverviewTaskStatistics();
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Overview task statistics retrieved successfully'
        ]);
    }

    /**
     * Lấy tất cả tasks (chỉ admin)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllTasks(Request $request): JsonResponse
    {
        $filters = $request->only(['receiver_id', 'receiver_type', 'creator_id', 'creator_type', 'search', 'status']);
        $perPage = $request->get('per_page', 15);
        
        $tasks = $this->taskService->getAllTasks($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => TaskResource::collection($tasks->items()),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage()
            ],
            'message' => 'All tasks retrieved successfully'
        ]);
    }



    /**
     * Lấy danh sách faculties (chỉ admin và lecturer thuộc faculty đó)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getFaculties(Request $request): JsonResponse
    {
        try {
            // Tạo user object từ JWT
            $user = $this->userContextService->createUserFromJwt($request);
            
            // Sử dụng Use Case để lấy faculties
            $faculties = $this->getFacultiesUseCase->execute($user);
            
            return response()->json([
                'success' => true,
                'data' => $faculties,
                'message' => 'Faculties retrieved successfully'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving faculties'
            ], 500);
        }
    }

    /**
     * Lấy danh sách classes theo faculty (chỉ admin và lecturer thuộc faculty đó)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getClassesByFaculty(Request $request): JsonResponse
    {
        try {
            // Validate faculty_id parameter
            $facultyId = $request->get('faculty_id');
            if (!$facultyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty ID is required'
                ], 400);
            }
            
            // Tạo user object từ JWT
            $user = $this->userContextService->createUserFromJwt($request);
            
            // Sử dụng Use Case để lấy classes
            $classes = $this->getClassesByFacultyUseCase->execute($user, $facultyId);
            
            return response()->json([
                'success' => true,
                'data' => $classes,
                'message' => 'Classes retrieved successfully'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving classes'
            ], 500);
        }
    }

    /**
     * Lấy danh sách students theo class (chỉ admin và lecturer thuộc faculty đó)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getStudentsByClass(Request $request): JsonResponse
    {
        try {
            // Validate class_id parameter
            $classId = $request->get('class_id');
            if (!$classId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class ID is required'
                ], 400);
            }
            
            // Tạo user object từ JWT
            $user = $this->userContextService->createUserFromJwt($request);
            
            // Sử dụng Use Case để lấy students
            $students = $this->getStudentsByClassUseCase->execute($user, $classId);
            
            return response()->json([
                'success' => true,
                'data' => $students,
                'message' => 'Students retrieved successfully'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving students'
            ], 500);
        }
    }

    /**
     * Lấy danh sách lecturers (chỉ admin)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getLecturers(Request $request): JsonResponse
    {
        try {
            // Tạo user object từ JWT
            $user = $this->userContextService->createUserFromJwt($request);
            
            // Sử dụng Use Case để lấy lecturers
            $lecturers = $this->getLecturersUseCase->execute($user);
            
            return response()->json([
                'success' => true,
                'data' => $lecturers,
                'message' => 'Lecturers retrieved successfully'
            ]);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving lecturers'
            ], 500);
        }
    }

    /**
     * Tạo báo cáo task với queue processing
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function generateReport(Request $request): JsonResponse
    {
        try {
            $reportType = $request->input('type', 'daily');
            $reportParams = $request->input('params', []);
            $recipients = $request->input('recipients', []);
            
            // Dispatch GenerateTaskReportJob
            GenerateTaskReportJob::dispatch($reportType, $reportParams, $recipients)
                ->onQueue('reports')
                ->delay(now()->addMinutes(2));
            
            return response()->json([
                'success' => true,
                'message' => 'Báo cáo đang được tạo, sẽ gửi email khi hoàn thành'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Đồng bộ dữ liệu task với queue processing
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function syncData(Request $request): JsonResponse
    {
        try {
            $syncType = $request->input('type', 'database');
            $syncParams = $request->input('params', []);
            
            // Dispatch SyncTaskDataJob
            SyncTaskDataJob::dispatch($syncType, $syncParams)
                ->onQueue('sync')
                ->delay(now()->addMinutes(1));
            
            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ dữ liệu đang được thực hiện'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đồng bộ dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý file upload cho task với queue processing
     * 
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function processTaskFiles(Request $request, Task $task): JsonResponse
    {
        try {
            $request->validate([
                'files.*' => 'required|file|max:10240' // Tối đa 10MB mỗi file
            ]);

            $processedFiles = [];
            
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $fileData = [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'path' => $file->store('task-files', 'public')
                    ];
                    
                    // Dispatch ProcessTaskFileJob
                    ProcessTaskFileJob::dispatch($fileData, 'upload_processing', $task->id)
                        ->onQueue('files')
                        ->delay(now()->addSeconds(30));
                        
                    $processedFiles[] = $fileData;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $processedFiles,
                'message' => 'Files đang được xử lý'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xử lý files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo task mới với phân quyền mới và queue processing
     * 
     * @param TaskRequest $request
     * @return JsonResponse
     */
    public function createTaskWithPermissions(TaskRequest $request): JsonResponse
    {
        try {
            // Tạo user object từ JWT
            $user = $this->userContextService->createUserFromJwt($request);
            
            $validatedData = $request->validated();
            
            // Sử dụng Use Case để tạo task với permissions
            $task = $this->createTaskWithPermissionsUseCase->execute($user, $validatedData);
            
            // Dispatch ProcessTaskJob để xử lý background tasks
            if ($task && $task->id) {
                ProcessTaskJob::dispatch($task->toArray(), 'task_created')
                    ->onQueue('high')
                    ->delay(now()->addSeconds(30));
            }
            
            return response()->json([
                'success' => true,
                'data' => new TaskResource($task),
                'message' => 'Task created successfully and is being processed'
            ], 201);
        } catch (TaskException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'context' => $e->getContext()
            ], $e->getCode() ?: 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the task'
            ], 500);
        }
    }
}
