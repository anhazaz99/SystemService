import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import { useForm } from 'react-hook-form';
import { useAuth } from '@/contexts/AuthContext';
import { LoginCredentials } from '@/types';
import { EyeIcon, EyeSlashIcon } from '@heroicons/react/24/outline';
import Link from 'next/link';

interface FormData {
  username: string;
  password: string;
  user_type: 'lecturer' | 'student';
}

export default function LoginPage() {
  const router = useRouter();
  const { login, isAuthenticated, loading } = useAuth();
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<FormData>({
    defaultValues: {
      username: '',
      password: '',
      user_type: 'lecturer',
    },
  });

  useEffect(() => {
    if (!loading && isAuthenticated) {
      router.replace('/');
    }
  }, [isAuthenticated, loading, router]);

  const onSubmit = async (data: FormData) => {
    setIsLoading(true);
    try {
      const success = await login(data);
      if (success) {
        // Không cần redirect ở đây vì useEffect sẽ handle
        // router.push('/');
      }
    } finally {
      setIsLoading(false);
    }
  };

  if (loading || isAuthenticated) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
      <div className="sm:mx-auto sm:w-full sm:max-w-md">
        <div className="flex justify-center">
          <div className="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center">
            <span className="text-white text-xl font-bold">TM</span>
          </div>
        </div>
        <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Đăng nhập vào hệ thống
        </h2>
        <p className="mt-2 text-center text-sm text-gray-600">
          Task Management System with Clean Architecture
        </p>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div className="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
          <form className="space-y-6" onSubmit={handleSubmit(onSubmit)}>
            {/* User Type */}
            <div>
              <label className="block text-sm font-medium text-gray-700">
                Loại người dùng
              </label>
              <div className="mt-1">
                <select
                  {...register('user_type', { required: 'Vui lòng chọn loại người dùng' })}
                  className="form-select"
                >
                  <option value="lecturer">Giảng viên</option>
                  <option value="student">Sinh viên</option>
                </select>
              </div>
              {errors.user_type && (
                <p className="mt-2 text-sm text-red-600">{errors.user_type.message}</p>
              )}
            </div>

            {/* Username */}
            <div>
              <label className="block text-sm font-medium text-gray-700">
                Tên đăng nhập
              </label>
              <div className="mt-1">
                <input
                  type="text"
                  {...register('username', { 
                    required: 'Vui lòng nhập tên đăng nhập',
                    minLength: { value: 3, message: 'Tên đăng nhập phải có ít nhất 3 ký tự' }
                  })}
                  className="form-input"
                  placeholder="Nhập tên đăng nhập"
                />
              </div>
              {errors.username && (
                <p className="mt-2 text-sm text-red-600">{errors.username.message}</p>
              )}
            </div>

            {/* Password */}
            <div>
              <label className="block text-sm font-medium text-gray-700">
                Mật khẩu
              </label>
              <div className="mt-1 relative">
                <input
                  type={showPassword ? 'text' : 'password'}
                  {...register('password', { 
                    required: 'Vui lòng nhập mật khẩu',
                    minLength: { value: 6, message: 'Mật khẩu phải có ít nhất 6 ký tự' }
                  })}
                  className="form-input pr-10"
                  placeholder="Nhập mật khẩu"
                />
                <button
                  type="button"
                  className="absolute inset-y-0 right-0 flex items-center pr-3"
                  onClick={() => setShowPassword(!showPassword)}
                >
                  {showPassword ? (
                    <EyeSlashIcon className="h-5 w-5 text-gray-400" />
                  ) : (
                    <EyeIcon className="h-5 w-5 text-gray-400" />
                  )}
                </button>
              </div>
              {errors.password && (
                <p className="mt-2 text-sm text-red-600">{errors.password.message}</p>
              )}
            </div>

            {/* Submit Button */}
            <div>
              <button
                type="submit"
                disabled={isLoading}
                className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isLoading ? (
                  <div className="flex items-center">
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Đang đăng nhập...
                  </div>
                ) : (
                  'Đăng nhập'
                )}
              </button>
            </div>
          </form>

          {/* Demo accounts */}
          <div className="mt-6">
            <div className="relative">
              <div className="absolute inset-0 flex items-center">
                <div className="w-full border-t border-gray-300" />
              </div>
              <div className="relative flex justify-center text-sm">
                <span className="px-2 bg-white text-gray-500">Tài khoản demo</span>
              </div>
            </div>

            <div className="mt-4 text-sm text-gray-600 space-y-1">
              <div className="bg-gray-50 p-3 rounded-md">
                <p className="font-medium">Giảng viên:</p>
                <p>Username: <code className="bg-gray-200 px-1 rounded">lecturer001</code></p>
                <p>Password: <code className="bg-gray-200 px-1 rounded">password123</code></p>
              </div>
              <div className="bg-gray-50 p-3 rounded-md">
                <p className="font-medium">Sinh viên:</p>
                <p>Username: <code className="bg-gray-200 px-1 rounded">student001</code></p>
                <p>Password: <code className="bg-gray-200 px-1 rounded">password123</code></p>
              </div>
            </div>
          </div>

          {/* Registration link */}
          <div className="mt-6 text-center">
            <p className="text-sm text-gray-600">
              Chưa có tài khoản?{' '}
              <Link href="/register" className="font-medium text-blue-600 hover:text-blue-500">
                Đăng ký ngay
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
