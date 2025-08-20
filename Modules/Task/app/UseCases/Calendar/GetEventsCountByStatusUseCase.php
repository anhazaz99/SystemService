<?php

namespace Modules\Task\app\UseCases\Calendar;

use Modules\Task\app\Models\Task;
use Modules\Task\app\Exceptions\TaskException;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Đếm events theo trạng thái
 * 
 * Tuân thủ Clean Architecture: Use Case chứa business logic cụ thể
 * Tách biệt khỏi Controller và Service
 */
class GetEventsCountByStatusUseCase
{
    /**
     * Thực hiện đếm events theo status
     * 
     * @param object $user User object
     * @return array Counts by status
     * @throws TaskException Nếu có lỗi
     */
    public function execute(object $user): array
    {
        try {
            // Validate user
            $this->validateUser($user);
            
            // Lấy counts theo status
            $counts = $this->getEventsCountByStatus($user);
            
            // Log success
            Log::info('Events count by status retrieved successfully via UseCase', [
                'user_id' => $user->id,
                'counts' => $counts
            ]);
            
            return $counts;
        } catch (\Exception $e) {
            Log::error('Error retrieving events count by status via UseCase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate user
     * 
     * @param object $user User object
     * @throws TaskException Nếu user không hợp lệ
     */
    private function validateUser(object $user): void
    {
        if (!$user || !isset($user->id)) {
            throw TaskException::businessRuleViolation(
                'User not authenticated',
                ['user' => $user]
            );
        }
    }

    /**
     * Lấy counts theo status
     * 
     * @param object $user User object
     * @return array Counts array
     */
    private function getEventsCountByStatus(object $user): array
    {
        return Task::whereHas('receivers', function ($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->where('receiver_type', $this->getUserType($user));
            })
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Lấy loại user
     * 
     * @param object $user User object
     * @return string User type
     */
    private function getUserType(object $user): string
    {
        if ($user instanceof \Modules\Auth\app\Models\LecturerAccount) {
            return 'lecturer';
        } elseif ($user instanceof \Modules\Auth\app\Models\StudentAccount) {
            return 'student';
        }
        return 'unknown';
    }
}
