<?php

namespace Modules\Task\app\UseCases;

use Modules\Task\app\Services\Interfaces\TaskServiceInterface;
use Modules\Task\app\DTOs\TaskDTO;
use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Tạo Task mới
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class CreateTaskUseCase
{
    public function __construct(
        private TaskServiceInterface $taskService
    ) {}

    /**
     * Thực hiện tạo task mới
     * 
     * @param array $data Dữ liệu task
     * @return Task Task đã được tạo
     * @throws \Exception Nếu có lỗi
     */
    public function execute(array $data): Task
    {
        try {
            // Validate business rules
            $this->validateBusinessRules($data);
            
            // Tạo DTO
            $taskDTO = TaskDTO::forCreate($data);
            
            // Tạo task thông qua service
            $task = $this->taskService->createTask($taskDTO->toArray());
            
            // Log success
            Log::info('Task created successfully via UseCase', [
                'task_id' => $task->id,
                'title' => $task->title,
                'creator_id' => $task->creator_id
            ]);
            
            return $task;
        } catch (\Exception $e) {
            Log::error('Error creating task via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate business rules
     * 
     * @param array $data Dữ liệu cần validate
     * @throws TaskException Nếu vi phạm business rules
     */
    private function validateBusinessRules(array $data): void
    {
        // Kiểm tra deadline không được trong quá khứ
        if (isset($data['deadline'])) {
            $deadline = \Carbon\Carbon::parse($data['deadline']);
            if ($deadline->isPast()) {
                throw TaskException::businessRuleViolation(
                    'Deadline cannot be in the past',
                    ['deadline' => $data['deadline']]
                );
            }
        }

        // Kiểm tra ít nhất 1 receiver
        if (empty($data['receivers'])) {
            throw TaskException::businessRuleViolation(
                'At least one receiver is required',
                ['receivers' => $data['receivers'] ?? []]
            );
        }

        // Kiểm tra creator phải là lecturer hoặc student
        if (!in_array($data['creator_type'], ['lecturer', 'student'])) {
            throw TaskException::businessRuleViolation(
                'Creator type must be lecturer or student',
                ['creator_type' => $data['creator_type']]
            );
        }
    }
}
