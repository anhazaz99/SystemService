import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'react-hot-toast';
import { useRouter } from 'next/router';
import Link from 'next/link';
import apiClient from '@/lib/api';
import { RegisterStudentData, RegisterLecturerData } from '@/types';
import { EyeIcon, EyeSlashIcon } from '@heroicons/react/24/outline';

interface StudentFormData {
  student_code: string;
  full_name: string;
  email: string;
  phone?: string;
  address?: string;
  username: string;
  password: string;
  password_confirmation: string;
}

interface LecturerFormData {
  lecturer_code: string;
  full_name: string;
  email: string;
  phone?: string;
  address?: string;
  username: string;
  password: string;
  password_confirmation: string;
}

export default function RegisterPage() {
  const [userType, setUserType] = useState<'student' | 'lecturer'>('student');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const router = useRouter();

  const studentForm = useForm<StudentFormData>({
    defaultValues: {
      student_code: '',
      full_name: '',
      email: '',
      phone: '',
      address: '',
      username: '',
      password: '',
      password_confirmation: ''
    }
  });

  const lecturerForm = useForm<LecturerFormData>({
    defaultValues: {
      lecturer_code: '',
      full_name: '',
      email: '',
      phone: '',
      address: '',
      username: '',
      password: '',
      password_confirmation: ''
    }
  });

  const validateStudentForm = (data: StudentFormData): boolean => {
    if (!data.student_code.trim()) {
      toast.error('Mã sinh viên là bắt buộc');
      return false;
    }
    if (!data.full_name.trim()) {
      toast.error('Họ tên là bắt buộc');
      return false;
    }
    if (!data.email.trim() || !data.email.includes('@')) {
      toast.error('Email không hợp lệ');
      return false;
    }
    if (!data.username.trim() || data.username.length < 3) {
      toast.error('Tên đăng nhập phải có ít nhất 3 ký tự');
      return false;
    }
    if (!data.password || data.password.length < 6) {
      toast.error('Mật khẩu phải có ít nhất 6 ký tự');
      return false;
    }
    if (data.password !== data.password_confirmation) {
      toast.error('Mật khẩu xác nhận không khớp');
      return false;
    }
    return true;
  };

  const validateLecturerForm = (data: LecturerFormData): boolean => {
    if (!data.lecturer_code.trim()) {
      toast.error('Mã giảng viên là bắt buộc');
      return false;
    }
    if (!data.full_name.trim()) {
      toast.error('Họ tên là bắt buộc');
      return false;
    }
    if (!data.email.trim() || !data.email.includes('@')) {
      toast.error('Email không hợp lệ');
      return false;
    }
    if (!data.username.trim() || data.username.length < 3) {
      toast.error('Tên đăng nhập phải có ít nhất 3 ký tự');
      return false;
    }
    if (!data.password || data.password.length < 6) {
      toast.error('Mật khẩu phải có ít nhất 6 ký tự');
      return false;
    }
    if (data.password !== data.password_confirmation) {
      toast.error('Mật khẩu xác nhận không khớp');
      return false;
    }
    return true;
  };

  const onSubmitStudent = async (data: StudentFormData) => {
    if (!validateStudentForm(data)) return;
    
    setIsLoading(true);
    try {
      const response = await apiClient.registerStudent(data);
      toast.success('Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.');
      router.push('/login');
    } catch (error: any) {
      const message = error.response?.data?.message || 'Có lỗi xảy ra khi đăng ký';
      toast.error(message);
    } finally {
      setIsLoading(false);
    }
  };

  const onSubmitLecturer = async (data: LecturerFormData) => {
    if (!validateLecturerForm(data)) return;
    
    setIsLoading(true);
    try {
      const response = await apiClient.registerLecturer(data);
      toast.success('Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.');
      router.push('/login');
    } catch (error: any) {
      const message = error.response?.data?.message || 'Có lỗi xảy ra khi đăng ký';
      toast.error(message);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
      <div className="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Đăng ký tài khoản
        </h2>
        <p className="mt-2 text-center text-sm text-gray-600">
          Hoặc{' '}
          <Link href="/login" className="font-medium text-indigo-600 hover:text-indigo-500">
            đăng nhập nếu đã có tài khoản
          </Link>
        </p>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div className="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
          {/* User Type Selection */}
          <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Loại tài khoản
            </label>
            <div className="flex space-x-4">
              <button
                type="button"
                onClick={() => setUserType('student')}
                className={`flex-1 py-2 px-4 border rounded-md text-sm font-medium ${
                  userType === 'student'
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                }`}
              >
                Sinh viên
              </button>
              <button
                type="button"
                onClick={() => setUserType('lecturer')}
                className={`flex-1 py-2 px-4 border rounded-md text-sm font-medium ${
                  userType === 'lecturer'
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                }`}
              >
                Giảng viên
              </button>
            </div>
          </div>

          {/* Student Registration Form */}
          {userType === 'student' && (
            <form onSubmit={studentForm.handleSubmit(onSubmitStudent)} className="space-y-6">
              <div>
                <label htmlFor="student_code" className="block text-sm font-medium text-gray-700">
                  Mã sinh viên *
                </label>
                <input
                  {...studentForm.register('student_code')}
                  type="text"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                {studentForm.formState.errors.student_code && (
                  <p className="mt-1 text-sm text-red-600">{studentForm.formState.errors.student_code.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="full_name" className="block text-sm font-medium text-gray-700">
                  Họ tên *
                </label>
                <input
                  {...studentForm.register('full_name')}
                  type="text"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                {studentForm.formState.errors.full_name && (
                  <p className="mt-1 text-sm text-red-600">{studentForm.formState.errors.full_name.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                  Email *
                </label>
                <input
                  {...studentForm.register('email')}
                  type="email"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                {studentForm.formState.errors.email && (
                  <p className="mt-1 text-sm text-red-600">{studentForm.formState.errors.email.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="phone" className="block text-sm font-medium text-gray-700">
                  Số điện thoại
                </label>
                <input
                  {...studentForm.register('phone')}
                  type="tel"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>

              <div>
                <label htmlFor="address" className="block text-sm font-medium text-gray-700">
                  Địa chỉ
                </label>
                <textarea
                  {...studentForm.register('address')}
                  rows={3}
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>

              <div>
                <label htmlFor="username" className="block text-sm font-medium text-gray-700">
                  Tên đăng nhập *
                </label>
                <input
                  {...studentForm.register('username')}
                  type="text"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                {studentForm.formState.errors.username && (
                  <p className="mt-1 text-sm text-red-600">{studentForm.formState.errors.username.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                  Mật khẩu *
                </label>
                <div className="relative">
                  <input
                    {...studentForm.register('password')}
                    type={showPassword ? 'text' : 'password'}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    {showPassword ? (
                      <EyeSlashIcon className="h-5 w-5 text-gray-400" />
                    ) : (
                      <EyeIcon className="h-5 w-5 text-gray-400" />
                    )}
                  </button>
                </div>
                {studentForm.formState.errors.password && (
                  <p className="mt-1 text-sm text-red-600">{studentForm.formState.errors.password.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                  Xác nhận mật khẩu *
                </label>
                <div className="relative">
                  <input
                    {...studentForm.register('password_confirmation')}
                    type={showConfirmPassword ? 'text' : 'password'}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    {showConfirmPassword ? (
                      <EyeSlashIcon className="h-5 w-5 text-gray-400" />
                    ) : (
                      <EyeIcon className="h-5 w-5 text-gray-400" />
                    )}
                  </button>
                </div>
                {studentForm.formState.errors.password_confirmation && (
                  <p className="mt-1 text-sm text-red-600">{studentForm.formState.errors.password_confirmation.message}</p>
                )}
              </div>

              <div>
                <button
                  type="submit"
                  disabled={isLoading}
                  className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                  {isLoading ? 'Đang đăng ký...' : 'Đăng ký sinh viên'}
                </button>
              </div>
            </form>
          )}

          {/* Lecturer Registration Form */}
          {userType === 'lecturer' && (
            <form onSubmit={lecturerForm.handleSubmit(onSubmitLecturer)} className="space-y-6">
              <div>
                <label htmlFor="lecturer_code" className="block text-sm font-medium text-gray-700">
                  Mã giảng viên *
                </label>
                <input
                  {...lecturerForm.register('lecturer_code')}
                  type="text"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                {lecturerForm.formState.errors.lecturer_code && (
                  <p className="mt-1 text-sm text-red-600">{lecturerForm.formState.errors.lecturer_code.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="full_name" className="block text-sm font-medium text-gray-700">
                  Họ tên *
                </label>
                <input
                  {...lecturerForm.register('full_name')}
                  type="text"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                {lecturerForm.formState.errors.full_name && (
                  <p className="mt-1 text-sm text-red-600">{lecturerForm.formState.errors.full_name.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                  Email *
                </label>
                <input
                  {...lecturerForm.register('email')}
                  type="email"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                {lecturerForm.formState.errors.email && (
                  <p className="mt-1 text-sm text-red-600">{lecturerForm.formState.errors.email.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="phone" className="block text-sm font-medium text-gray-700">
                  Số điện thoại
                </label>
                <input
                  {...lecturerForm.register('phone')}
                  type="tel"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>

              <div>
                <label htmlFor="address" className="block text-sm font-medium text-gray-700">
                  Địa chỉ
                </label>
                <textarea
                  {...lecturerForm.register('address')}
                  rows={3}
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>

              <div>
                <label htmlFor="username" className="block text-sm font-medium text-gray-700">
                  Tên đăng nhập *
                </label>
                <input
                  {...lecturerForm.register('username')}
                  type="text"
                  className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                {lecturerForm.formState.errors.username && (
                  <p className="mt-1 text-sm text-red-600">{lecturerForm.formState.errors.username.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                  Mật khẩu *
                </label>
                <div className="relative">
                  <input
                    {...lecturerForm.register('password')}
                    type={showPassword ? 'text' : 'password'}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    {showPassword ? (
                      <EyeSlashIcon className="h-5 w-5 text-gray-400" />
                    ) : (
                      <EyeIcon className="h-5 w-5 text-gray-400" />
                    )}
                  </button>
                </div>
                {lecturerForm.formState.errors.password && (
                  <p className="mt-1 text-sm text-red-600">{lecturerForm.formState.errors.password.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                  Xác nhận mật khẩu *
                </label>
                <div className="relative">
                  <input
                    {...lecturerForm.register('password_confirmation')}
                    type={showConfirmPassword ? 'text' : 'password'}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    {showConfirmPassword ? (
                      <EyeSlashIcon className="h-5 w-5 text-gray-400" />
                    ) : (
                      <EyeIcon className="h-5 w-5 text-gray-400" />
                    )}
                  </button>
                </div>
                {lecturerForm.formState.errors.password_confirmation && (
                  <p className="mt-1 text-sm text-red-600">{lecturerForm.formState.errors.password_confirmation.message}</p>
                )}
              </div>

              <div>
                <button
                  type="submit"
                  disabled={isLoading}
                  className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                  {isLoading ? 'Đang đăng ký...' : 'Đăng ký giảng viên'}
                </button>
              </div>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}
