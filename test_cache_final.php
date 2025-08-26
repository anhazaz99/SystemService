<?php

/**
 * Final Cache System Test
 *
 * Chạy file này để test toàn bộ cache system:
 * php test_cache_final.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Final Cache System Test...\n";
echo "===============================\n\n";

$results = [];

try {
    // Test 1: Department Cache
    echo "1️⃣ Testing Department Cache...\n";
    $departmentService = app('\Modules\Auth\app\Services\DepartmentService\DepartmentService');
    
    $start = microtime(true);
    $departments1 = $departmentService->getAllDepartments();
    $time1 = microtime(true) - $start;
    
    $start = microtime(true);
    $departments2 = $departmentService->getAllDepartments();
    $time2 = microtime(true) - $start;
    
    $improvement = round(($time1 - $time2) / $time1 * 100, 1);
    echo "   📊 Database: " . round($time1 * 1000, 2) . "ms | Cache: " . round($time2 * 1000, 2) . "ms | Improvement: {$improvement}%\n";
    $results['departments'] = $improvement;
    
    // Test 2: Student Cache
    echo "\n2️⃣ Testing Student Cache...\n";
    $studentService = app('\Modules\Auth\app\Services\AuthUserService\StudentService');
    
    $start = microtime(true);
    $students1 = $studentService->getAllStudents();
    $time1 = microtime(true) - $start;
    
    $start = microtime(true);
    $students2 = $studentService->getAllStudents();
    $time2 = microtime(true) - $start;
    
    $improvement = round(($time1 - $time2) / $time1 * 100, 1);
    echo "   📊 Database: " . round($time1 * 1000, 2) . "ms | Cache: " . round($time2 * 1000, 2) . "ms | Improvement: {$improvement}%\n";
    $results['students'] = $improvement;
    
    // Test 3: Lecturer Cache
    echo "\n3️⃣ Testing Lecturer Cache...\n";
    $lecturerService = app('\Modules\Auth\app\Services\AuthUserService\LecturerService');
    
    $start = microtime(true);
    $lecturers1 = $lecturerService->getAllLecturers();
    $time1 = microtime(true) - $start;
    
    $start = microtime(true);
    $lecturers2 = $lecturerService->getAllLecturers();
    $time2 = microtime(true) - $start;
    
    $improvement = round(($time1 - $time2) / $time1 * 100, 1);
    echo "   📊 Database: " . round($time1 * 1000, 2) . "ms | Cache: " . round($time2 * 1000, 2) . "ms | Improvement: {$improvement}%\n";
    $results['lecturers'] = $improvement;
    
    // Test 4: Classroom Cache
    echo "\n4️⃣ Testing Classroom Cache...\n";
    $classService = app('\Modules\Auth\app\Services\ClassService\ClassService');
    
    $start = microtime(true);
    $classes1 = $classService->getAllClasses();
    $time1 = microtime(true) - $start;
    
    $start = microtime(true);
    $classes2 = $classService->getAllClasses();
    $time2 = microtime(true) - $start;
    
    $improvement = round(($time1 - $time2) / $time1 * 100, 1);
    echo "   📊 Database: " . round($time1 * 1000, 2) . "ms | Cache: " . round($time2 * 1000, 2) . "ms | Improvement: {$improvement}%\n";
    $results['classrooms'] = $improvement;
    
    // Test 5: Cache Driver Info
    echo "\n5️⃣ Cache System Info...\n";
    $cacheDriver = config('cache.default');
    echo "   🔧 Driver: {$cacheDriver}\n";
    
    // Test 6: Cache Invalidation Test
    echo "\n6️⃣ Testing Cache Invalidation...\n";
    
    // Lưu một số data vào cache trước
    \Illuminate\Support\Facades\Cache::put('test_departments', $departments1, 60);
    \Illuminate\Support\Facades\Cache::put('test_students', $students1, 60);
    echo "   ✅ Test data cached\n";
    
    // Clear cache
    echo "   🗑️  Clearing cache...\n";
    \Illuminate\Support\Facades\Cache::flush();
    echo "   ✅ Cache cleared\n";
    
    // Test lại - sẽ query từ database
    $start = microtime(true);
    $departmentsAfterClear = $departmentService->getAllDepartments();
    $timeAfterClear = microtime(true) - $start;
    echo "   📊 After clear: " . round($timeAfterClear * 1000, 2) . "ms (from database)\n";
    
    // Summary
    echo "\n📊 CACHE PERFORMANCE SUMMARY\n";
    echo "===============================\n";
    
    $totalImprovement = 0;
    $count = 0;
    
    foreach ($results as $service => $improvement) {
        echo "   {$service}: {$improvement}% improvement\n";
        $totalImprovement += $improvement;
        $count++;
    }
    
    $averageImprovement = $count > 0 ? round($totalImprovement / $count, 1) : 0;
    echo "\n   🚀 AVERAGE IMPROVEMENT: {$averageImprovement}%\n";
    
    if ($averageImprovement > 80) {
        echo "   🎉 EXCELLENT! Cache system is working perfectly!\n";
    } elseif ($averageImprovement > 60) {
        echo "   ✅ GOOD! Cache system is working well!\n";
    } elseif ($averageImprovement > 40) {
        echo "   ⚠️  FAIR! Cache system needs optimization!\n";
    } else {
        echo "   ❌ POOR! Cache system is not working properly!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🎯 Final cache test completed!\n";

