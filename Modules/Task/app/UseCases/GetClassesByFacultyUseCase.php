<?php

namespace Modules\Task\app\UseCases;

use Modules\Task\app\Services\Interfaces\TaskServiceInterface;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Lấy danh sách classes theo faculty
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetClassesByFacultyUseCase
{
    public function __construct(
        private TaskServiceInterface $taskService
    ) {}

    /**
     * Thực hiện lấy danh sách classes theo faculty
     * 
     * @param object $user User hiện tại
     * @param int $facultyId ID của faculty
     * @return array Danh sách classes
     * @throws TaskException Nếu có lỗi
     */
    public function execute(object $user, int $facultyId): array
    {
        try {
            // Validate input
            $this->validateInput($user, $facultyId);
            
            // Lấy classes thông qua service
            $classes = $this->taskService->getClassesByFacultyForUser($user, $facultyId);
            
            // Log success
            Log::info('Classes retrieved successfully via UseCase', [
                'user_id' => $user->id,
                'user_type' => $user->user_type ?? 'unknown',
                'faculty_id' => $facultyId,
                'classes_count' => count($classes)
            ]);
            
            return $classes;
        } catch (\Exception $e) {
            Log::error('Error retrieving classes via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate input
     * 
     * @param object $user User cần validate
     * @param int $facultyId Faculty ID cần validate
     * @throws TaskException Nếu input không hợp lệ
     */
    private function validateInput(object $user, int $facultyId): void
    {
        if (!isset($user->id)) {
            throw TaskException::businessRuleViolation(
                'User ID is required',
                ['user' => $user]
            );
        }

        if (!isset($user->user_type)) {
            throw TaskException::businessRuleViolation(
                'User type is required',
                ['user' => $user]
            );
        }

        if ($facultyId <= 0) {
            throw TaskException::businessRuleViolation(
                'Faculty ID must be positive',
                ['faculty_id' => $facultyId]
            );
        }
    }
}
