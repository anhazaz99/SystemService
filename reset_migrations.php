<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

echo "🔄 Reset và chạy lại migration mới...\n\n";

try {
    // 1. Drop tất cả bảng
    echo "1️⃣ Drop tất cả bảng...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    $tables = [
        'task_receivers',
        'task_file', 
        'task',
        'calendar',
        'calendar_events',
        'student_account',
        'lecturer_account',
        'student',
        'class',
        'lecturer',
        'faculty',
        'sessions',
        'failed_jobs',
        'jobs',
        'cache_locks',
        'cache',
        'migrations'
    ];
    
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            Schema::dropIfExists($table);
            echo "   ✅ Dropped table: $table\n";
        }
    }
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "   ✅ Tất cả bảng đã được xóa\n\n";

    // 2. Chạy migration mới
    echo "2️⃣ Chạy migration mới...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo "   ✅ Migration đã chạy thành công\n\n";

    // 3. Tạo dữ liệu test
    echo "3️⃣ Tạo dữ liệu test...\n";
    
    // Tạo faculties
    DB::table('faculty')->insert([
        [
            'id' => 1,
            'name' => 'Khoa Công nghệ Thông tin',
            'type' => 'faculty',
            'parent_id' => null,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 2,
            'name' => 'Khoa Kinh tế',
            'type' => 'faculty',
            'parent_id' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "   ✅ Đã tạo 2 faculties\n";

    // Tạo lecturers
    DB::table('lecturer')->insert([
        [
            'id' => 1,
            'full_name' => 'Administrator',
            'gender' => 'male',
            'address' => 'Hà Nội',
            'email' => 'admin@university.edu.vn',
            'phone' => '0123456789',
            'lecturer_code' => 'ADMIN001',
            'faculty_id' => 1,
            'assignes_id' => null,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 2,
            'full_name' => 'Nguyễn Văn A',
            'gender' => 'male',
            'address' => 'Hà Nội',
            'email' => 'nguyenvana@university.edu.vn',
            'phone' => '0987654321',
            'lecturer_code' => 'GV001',
            'faculty_id' => 2,
            'assignes_id' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "   ✅ Đã tạo 2 lecturers\n";

    // Tạo lecturer accounts
    DB::table('lecturer_account')->insert([
        [
            'id' => 1,
            'lecturer_id' => 1,
            'username' => 'admin',
            'password' => bcrypt('123456'),
            'is_admin' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 2,
            'lecturer_id' => 2,
            'username' => 'gv001',
            'password' => bcrypt('123456'),
            'is_admin' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "   ✅ Đã tạo lecturer accounts\n";

    // Tạo classes
    DB::table('class')->insert([
        [
            'id' => 1,
            'class_name' => 'Công nghệ Phần mềm K18',
            'class_code' => 'CNPM18',
            'faculty_id' => 1,
            'lecturer_id' => 1,
            'school_year' => '2024-2025',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 2,
            'class_name' => 'Hệ thống Thông tin K18',
            'class_code' => 'HTTT18',
            'faculty_id' => 1,
            'lecturer_id' => 1,
            'school_year' => '2024-2025',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "   ✅ Đã tạo 2 classes\n";

    // Tạo students
    DB::table('student')->insert([
        [
            'id' => 1,
            'full_name' => 'Phạm Văn D',
            'birth_date' => '2000-01-01',
            'gender' => 'male',
            'address' => 'Hà Nội',
            'email' => 'phamvand@student.edu.vn',
            'phone' => '0123456789',
            'student_code' => 'SV001',
            'enrolled_id' => null,
            'class_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 2,
            'full_name' => 'Hoàng Thị E',
            'birth_date' => '2000-02-02',
            'gender' => 'female',
            'address' => 'Hà Nội',
            'email' => 'hoangthie@student.edu.vn',
            'phone' => '0987654321',
            'student_code' => 'SV002',
            'enrolled_id' => null,
            'class_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "   ✅ Đã tạo 2 students\n";

    // Tạo student accounts
    DB::table('student_account')->insert([
        [
            'id' => 1,
            'student_id' => 1,
            'username' => 'sv001',
            'password' => bcrypt('123456'),
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 2,
            'student_id' => 2,
            'username' => 'sv002',
            'password' => bcrypt('123456'),
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "   ✅ Đã tạo student accounts\n";

    echo "\n🎉 Hoàn thành reset và tạo lại database!\n";
    echo "\n📋 Thông tin đăng nhập:\n";
    echo "   👨‍💼 Admin: username=admin, password=123456\n";
    echo "   👨‍🏫 Lecturer: username=gv001, password=123456\n";
    echo "   👨‍🎓 Student: username=sv001, password=123456\n";

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
