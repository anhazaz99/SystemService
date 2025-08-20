<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Task\app\Models\Task;
use Modules\Task\app\Models\TaskReceiver;

/**
 * Seeder để tạo dữ liệu test cho Task với receivers mới
 */
class TaskTestSeeder extends Seeder
{
    public function run(): void
    {
        echo "🌱 Bắt đầu tạo dữ liệu test cho Task...\n";

        // Tạo các tasks với receivers khác nhau
        $this->createTasksWithReceivers();

        echo "✅ Hoàn thành tạo dữ liệu test cho Task!\n";
    }

    private function createTasksWithReceivers(): void
    {
        // Task 1: Gán cho 1 student
        $task1 = Task::create([
            'title' => 'Hoàn thành bài tập Laravel',
            'description' => 'Làm bài tập về Laravel Framework và submit trước deadline',
            'deadline' => now()->addDays(7),
            'status' => 'pending',
            'priority' => 'high',
            'creator_id' => 1,
            'creator_type' => 'lecturer'
        ]);

        TaskReceiver::create([
            'task_id' => $task1->id,
            'receiver_id' => 1,
            'receiver_type' => 'student'
        ]);

        // Task 2: Gán cho 1 class
        $task2 = Task::create([
            'title' => 'Thuyết trình nhóm',
            'description' => 'Chuẩn bị thuyết trình về chủ đề Database Design',
            'deadline' => now()->addDays(14),
            'status' => 'pending',
            'priority' => 'medium',
            'creator_id' => 1,
            'creator_type' => 'lecturer'
        ]);

        TaskReceiver::create([
            'task_id' => $task2->id,
            'receiver_id' => 1,
            'receiver_type' => 'class'
        ]);

        // Task 3: Gán cho tất cả students
        $task3 = Task::create([
            'title' => 'Đăng ký môn học kỳ mới',
            'description' => 'Tất cả sinh viên cần đăng ký môn học cho kỳ học mới',
            'deadline' => now()->addDays(30),
            'status' => 'pending',
            'priority' => 'high',
            'creator_id' => 1,
            'creator_type' => 'lecturer'
        ]);

        TaskReceiver::create([
            'task_id' => $task3->id,
            'receiver_id' => 0, // Không cần ID cho all_students
            'receiver_type' => 'all_students'
        ]);

        // Task 4: Gán cho nhiều receivers
        $task4 = Task::create([
            'title' => 'Họp khoa định kỳ',
            'description' => 'Cuộc họp định kỳ của khoa để thảo luận các vấn đề quan trọng',
            'deadline' => now()->addDays(3),
            'status' => 'pending',
            'priority' => 'medium',
            'creator_id' => 1,
            'creator_type' => 'lecturer'
        ]);

        // Gán cho 1 lecturer
        TaskReceiver::create([
            'task_id' => $task4->id,
            'receiver_id' => 1,
            'receiver_type' => 'lecturer'
        ]);

        // Gán cho 1 class
        TaskReceiver::create([
            'task_id' => $task4->id,
            'receiver_id' => 1,
            'receiver_type' => 'class'
        ]);

        // Task 5: Task của student tạo
        $task5 = Task::create([
            'title' => 'Yêu cầu gia hạn deadline',
            'description' => 'Xin gia hạn deadline cho bài tập Laravel thêm 2 ngày',
            'deadline' => now()->addDays(2),
            'status' => 'pending',
            'priority' => 'low',
            'creator_id' => 1,
            'creator_type' => 'student'
        ]);

        TaskReceiver::create([
            'task_id' => $task5->id,
            'receiver_id' => 1,
            'receiver_type' => 'lecturer'
        ]);

        echo "📋 Đã tạo 5 tasks với receivers khác nhau:\n";
        echo "- Task 1: Gán cho 1 student\n";
        echo "- Task 2: Gán cho 1 class\n";
        echo "- Task 3: Gán cho tất cả students\n";
        echo "- Task 4: Gán cho nhiều receivers (lecturer + class)\n";
        echo "- Task 5: Task của student tạo\n";
    }
}
