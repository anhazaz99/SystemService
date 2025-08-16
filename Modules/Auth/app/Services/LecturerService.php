<?php

namespace Modules\Auth\app\Services;

use Modules\Auth\app\Repositories\Interfaces\AuthRepositoryInterface;
use Modules\Auth\app\Models\Lecturer;

class LecturerService
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Lấy tất cả giảng viên
     */
    public function getAllLecturers()
    {
        return Lecturer::with('account', 'unit')->get();
    }
    
    /**
     * Lấy giảng viên theo ID
     */
    public function getLecturerById(int $id)
    {
        return Lecturer::with('account', 'unit')->find($id);
    }
    
    /**
     * Tạo giảng viên mới và tự động tạo tài khoản
     */
    public function createLecturerWithAccount(array $lecturerData): Lecturer
    {
        // Tạo giảng viên mới
        $lecturer = Lecturer::create($lecturerData);
        
        // Tự động tạo tài khoản
        $this->createLecturerAccount($lecturer);
        
        return $lecturer;
    }
    
    /**
     * Tự động tạo tài khoản cho giảng viên
     */
    private function createLecturerAccount(Lecturer $lecturer): void
    {
        $username = $this->generateUsername($lecturer->lecturer_code);
        $password = $this->generateDefaultPassword();
        
        $this->authRepository->createLecturerAccount([
            'username' => $username,
            'password' => $password,
            'lecturer_id' => $lecturer->id,
            'is_admin' => false // Mặc định không phải admin
        ]);
    }
    
    /**
     * Tạo username từ mã giảng viên
     */
    private function generateUsername(string $lecturerCode): string
    {
        return 'gv_' . $lecturerCode;
    }
    
    /**
     * Tạo mật khẩu mặc định
     */
    private function generateDefaultPassword(): string
    {
        // Mật khẩu mặc định
        return '123456';
    }
    
    /**
     * Cập nhật thông tin giảng viên
     */
    public function updateLecturer(Lecturer $lecturer, array $data): Lecturer
    {
        $lecturer->update($data);
        return $lecturer;
    }
    
    /**
     * Xóa giảng viên và tài khoản liên quan
     */
    public function deleteLecturer(Lecturer $lecturer): bool
    {
        // Xóa tài khoản trước
        if ($lecturer->account) {
            $lecturer->account->delete();
        }
        
        // Xóa giảng viên
        return $lecturer->delete();
    }
    
    /**
     * Cập nhật quyền admin cho giảng viên
     */
    public function updateAdminStatus(Lecturer $lecturer, bool $isAdmin): bool
    {
        if ($lecturer->account) {
            return $lecturer->account->update(['is_admin' => $isAdmin]);
        }
        
        return false;
    }
}
