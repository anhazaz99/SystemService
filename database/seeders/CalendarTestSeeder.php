<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\app\Models\Lecturer;
use Modules\Auth\app\Models\Student;
use Modules\Auth\app\Models\Classroom;
use Modules\Auth\app\Models\Unit;
use Modules\Task\app\Models\Calendar;
use Modules\Task\app\Models\CalendarParticipant;
use Carbon\Carbon;

class CalendarTestSeeder extends Seeder
{
    /**
     * Chạy database seeds.
     */
    public function run(): void
    {
        // Tạo đơn vị (Unit) - sử dụng DB::table vì không có timestamps
        $facultyId = \DB::table('faculty')->insertGetId([
            'name' => 'Khoa Công nghệ Thông tin',
            'type' => 'faculty',
            'parent_id' => null
        ]);

        // Tạo giảng viên
        $lecturer1Id = \DB::table('lecturer')->insertGetId([
            'full_name' => 'PGS.TS Nguyễn Văn A',
            'gender' => 'male',
            'address' => 'Hà Nội',
            'email' => 'nguyenvana@university.edu.vn',
            'phone' => '0123456789',
            'lecturer_code' => 'GV001',
            'faculty_id' => $facultyId,
            'assignes_id' => 1
        ]);

        $lecturer2Id = \DB::table('lecturer')->insertGetId([
            'full_name' => 'TS Trần Thị B',
            'gender' => 'female',
            'address' => 'Hà Nội',
            'email' => 'tranthib@university.edu.vn',
            'phone' => '0123456790',
            'lecturer_code' => 'GV002',
            'faculty_id' => $facultyId,
            'assignes_id' => 1
        ]);

        // Tạo lớp học
        $class1Id = \DB::table('class')->insertGetId([
            'class_name' => 'Công nghệ thông tin K15A',
            'class_code' => 'CNTT15A',
            'faculty_id' => $unitId,
            'lecturer_id' => $lecturer1Id,
            'school_year' => '2023-2024',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $class2Id = \DB::table('class')->insertGetId([
            'class_name' => 'Công nghệ thông tin K15B',
            'class_code' => 'CNTT15B',
            'faculty_id' => $unitId,
            'lecturer_id' => $lecturer2Id,
            'school_year' => '2023-2024',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Tạo sinh viên
        $students = [];
        for ($i = 1; $i <= 20; $i++) {
            $classId = $i <= 10 ? $class1Id : $class2Id;
            $studentId = \DB::table('student')->insertGetId([
                'full_name' => "Sinh viên số $i",
                'birth_date' => '2000-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'address' => 'Hà Nội',
                'email' => "sinhvien$i@student.edu.vn",
                'phone' => '012345' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'student_code' => 'SV' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'class_id' => $classId,
                'enrolled_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $students[] = (object)['id' => $studentId];
        }

        // Tạo calendar events với các loại participants khác nhau
        
        // 1. Event cho 1 sinh viên cụ thể
        $event1 = Calendar::create([
            'title' => 'Tư vấn học tập cá nhân',
            'description' => 'Tư vấn học tập cho sinh viên SV001',
            'start_time' => Carbon::now()->addDays(1)->setHour(9)->setMinute(0),
            'end_time' => Carbon::now()->addDays(1)->setHour(10)->setMinute(0),
            'event_type' => 'event',
            'creator_id' => $lecturer1Id,
            'creator_type' => 'lecturer'
        ]);
        
        CalendarParticipant::create([
            'calendar_id' => $event1->id,
            'participant_id' => $students[0]->id,
            'participant_type' => 'student'
        ]);

        // 2. Event cho 1 lớp
        $event2 = Calendar::create([
            'title' => 'Kiểm tra giữa kỳ môn Lập trình Web',
            'description' => 'Kiểm tra giữa kỳ cho lớp CNTT15A',
            'start_time' => Carbon::now()->addDays(7)->setHour(14)->setMinute(0),
            'end_time' => Carbon::now()->addDays(7)->setHour(16)->setMinute(0),
            'event_type' => 'task',
            'creator_id' => $lecturer1Id,
            'creator_type' => 'lecturer'
        ]);
        
        CalendarParticipant::create([
            'calendar_id' => $event2->id,
            'participant_id' => $class1Id,
            'participant_type' => 'class'
        ]);

        // 3. Event cho toàn bộ sinh viên
        $event3 = Calendar::create([
            'title' => 'Thông báo nghỉ lễ Quốc khánh',
            'description' => 'Thông báo nghỉ lễ Quốc khánh 2/9 cho toàn bộ sinh viên',
            'start_time' => Carbon::now()->addDays(30)->setHour(0)->setMinute(0),
            'end_time' => Carbon::now()->addDays(30)->setHour(23)->setMinute(59),
            'event_type' => 'event',
            'creator_id' => $lecturer1Id,
            'creator_type' => 'lecturer'
        ]);
        
        CalendarParticipant::create([
            'calendar_id' => $event3->id,
            'participant_id' => 0, // Không quan trọng cho all_students
            'participant_type' => 'all_students'
        ]);

        // 4. Event cho nhiều loại participants
        $event4 = Calendar::create([
            'title' => 'Hội thảo khoa học công nghệ',
            'description' => 'Hội thảo về AI và Machine Learning',
            'start_time' => Carbon::now()->addDays(14)->setHour(8)->setMinute(0),
            'end_time' => Carbon::now()->addDays(14)->setHour(17)->setMinute(0),
            'event_type' => 'event',
            'creator_id' => $lecturer1Id,
            'creator_type' => 'lecturer'
        ]);
        
        // Thêm nhiều participants cho event này
        CalendarParticipant::create([
            'calendar_id' => $event4->id,
            'participant_id' => $lecturer1Id,
            'participant_type' => 'lecturer'
        ]);
        
        CalendarParticipant::create([
            'calendar_id' => $event4->id,
            'participant_id' => $lecturer2Id,
            'participant_type' => 'lecturer'
        ]);
        
        CalendarParticipant::create([
            'calendar_id' => $event4->id,
            'participant_id' => $class1Id,
            'participant_type' => 'class'
        ]);

        // 5. Event cho nhiều sinh viên cụ thể
        $event5 = Calendar::create([
            'title' => 'Họp nhóm đồ án tốt nghiệp',
            'description' => 'Họp nhóm cho đồ án tốt nghiệp',
            'start_time' => Carbon::now()->addDays(3)->setHour(15)->setMinute(0),
            'end_time' => Carbon::now()->addDays(3)->setHour(17)->setMinute(0),
            'event_type' => 'event',
            'creator_id' => $lecturer1Id,
            'creator_type' => 'lecturer'
        ]);
        
        // Thêm 5 sinh viên đầu tiên
        for ($i = 0; $i < 5; $i++) {
            CalendarParticipant::create([
                'calendar_id' => $event5->id,
                'participant_id' => $students[$i]->id,
                'participant_type' => 'student'
            ]);
        }

        echo "✅ Đã tạo thành công:\n";
        echo "- Khoa Công nghệ Thông tin\n";
        echo "- 2 giảng viên\n";
        echo "- 2 lớp học\n";
        echo "- 20 sinh viên\n";
        echo "- 5 calendar events với các loại participants khác nhau\n";
    }
}
