<?php

namespace Modules\Task\app\Exceptions;

use Exception;

/**
 * Custom Exception cho Task Module
 * 
 * Tuân thủ Clean Architecture: Tách biệt error handling
 */
class TaskException extends Exception
{
    protected $errorCode;
    protected $context;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        string $errorCode = 'TASK_ERROR',
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    /**
     * Lấy error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Lấy context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Exception khi không tìm thấy task
     */
    public static function taskNotFound(int $taskId): self
    {
        return new self(
            "Task with ID {$taskId} not found",
            404,
            null,
            'TASK_NOT_FOUND',
            ['task_id' => $taskId]
        );
    }

    /**
     * Exception khi không có quyền truy cập
     */
    public static function accessDenied(string $action, int $taskId): self
    {
        return new self(
            "Access denied for action '{$action}' on task {$taskId}",
            403,
            null,
            'ACCESS_DENIED',
            ['action' => $action, 'task_id' => $taskId]
        );
    }

    /**
     * Exception khi validation business rules thất bại
     */
    public static function businessRuleViolation(string $rule, array $context = []): self
    {
        return new self(
            "Business rule violation: {$rule}",
            422,
            null,
            'BUSINESS_RULE_VIOLATION',
            array_merge(['rule' => $rule], $context)
        );
    }
}
