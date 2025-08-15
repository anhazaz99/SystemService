<?php

namespace Modules\Auth\app\Services;

use Modules\Auth\app\Repositories\Interfaces\AuthRepositoryInterface;
use Modules\Auth\app\Models\Student;
use Illuminate\Support\Str;

class StudentService
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Lấy tất cả sinh viên
     */
    public function getAllStudents()
    {
        return Student::with('account', 'classroom')->get();
    }
    
    /**
     * Lấy sinh viên theo ID
     */
    public function getStudentById(int $id)
    {
        return Student::with('account', 'classroom')->find($id);
    }
    
    /**
     * Tạo sinh viên mới và tự động tạo tài khoản
     */
    public function createStudentWithAccount(array $studentData): Student
    {
        // Tạo sinh viên mới
        $student = Student::create($studentData);
        
        // Tự động tạo tài khoản
        $this->createStudentAccount($student);
        
        return $student;
    }
    
    /**
     * Tự động tạo tài khoản cho sinh viên
     */
    private function createStudentAccount(Student $student): void
    {
        $username = $this->generateUsername($student->student_code);
        $password = $this->generateDefaultPassword();
        
        $this->authRepository->createStudentAccount([
            'username' => $username,
            'password' => $password,
            'student_id' => $student->id
        ]);
    }
    
    /**
     * Tạo username từ mã sinh viên
     */
    private function generateUsername(string $studentCode): string
    {
        return 'sv_' . $studentCode;
    }
    
    /**
     * Tạo mật khẩu mặc định
     */
    private function generateDefaultPassword(): string
    {
        // Mật khẩu mặc định: ngày sinh (YYYYMMDD)
        return '123456';
    }
    
    /**
     * Cập nhật thông tin sinh viên
     */
    public function updateStudent(Student $student, array $data): Student
    {
        $student->update($data);
        return $student;
    }
    
    /**
     * Xóa sinh viên và tài khoản liên quan
     */
    public function deleteStudent(Student $student): bool
    {
        // Xóa tài khoản trước
        if ($student->account) {
            $student->account->delete();
        }
        
        // Xóa sinh viên
        return $student->delete();
    }
}
