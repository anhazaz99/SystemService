import React, { ReactNode } from 'react';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { useAuth } from '@/contexts/AuthContext';
import {
  HomeIcon,
  ClipboardDocumentListIcon,
  ChartBarIcon,
  CalendarIcon,
  UserIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/react/24/outline';

interface LayoutProps {
  children: ReactNode;
  title?: string;
}

const navigation = [
  { name: 'Dashboard', href: '/', icon: HomeIcon },
  { name: 'My Tasks', href: '/tasks/my-tasks', icon: ClipboardDocumentListIcon },
  { name: 'Created Tasks', href: '/tasks/created', icon: ClipboardDocumentListIcon },
  { name: 'All Tasks', href: '/tasks', icon: ClipboardDocumentListIcon },
  { name: 'Statistics', href: '/statistics', icon: ChartBarIcon },
  { name: 'Calendar', href: '/calendar', icon: CalendarIcon },
];

export default function Layout({ children, title = 'Task Management' }: LayoutProps) {
  const router = useRouter();
  const { user, logout, isAuthenticated } = useAuth();
  const isLecturer = user?.user_type === 'lecturer';
  const isAdmin = !!user?.account?.is_admin;

  if (!isAuthenticated) {
    return <>{children}</>;
  }

  const handleLogout = async () => {
    await logout();
    router.push('/login');
  };

  return (
    <>
      <Head>
        <title>{title}</title>
        <meta name="description" content="Task Management System with Clean Architecture" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 128 128'%3E%3Crect width='128' height='128' rx='24' fill='%231e40af'/%3E%3Ctext x='50%' y='58%' dominant-baseline='middle' text-anchor='middle' font-family='Arial, Helvetica, sans-serif' font-size='64' fill='white'%3ET%3C/text%3E%3C/svg%3E" />
      </Head>

      <div className="min-h-screen bg-gray-50">
        {/* Sidebar */}
        <div className="fixed inset-y-0 left-0 w-64 bg-white shadow-lg z-50">
          <div className="flex flex-col h-full">
            {/* Logo */}
            <div className="flex items-center justify-center h-16 bg-blue-600">
              <h1 className="text-xl font-bold text-white">Task Manager</h1>
            </div>

            {/* User info */}
            <div className="p-4 border-b border-gray-200">
              <div className="flex items-center space-x-3">
                <div className="flex-shrink-0">
                  <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
<UserIcon className="w-6 h-6 text-blue-600" />
                  </div>
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-gray-900 truncate">
                    {user?.full_name}
                  </p>
                  <p className="text-xs text-gray-500 capitalize">
                    {user?.user_type}
                  </p>
                </div>
              </div>
            </div>

            {/* Navigation */}
            <nav className="flex-1 px-4 py-4 space-y-1">
              {navigation
                .filter((item) => {
                  if (item.href === '/tasks/created') return isLecturer;
                  if (item.href === '/tasks') return isAdmin;
                  return true;
                })
                .map((item) => {
                  const isActive = router.pathname === item.href;
                  return (
                    <button
                      key={item.name}
                      onClick={() => router.push(item.href)}
                      className={`
                        w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors
                        ${isActive
                          ? 'bg-blue-100 text-blue-700'
                          : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                        }
                      `}
                    >
                      <item.icon className="w-5 h-5 mr-3" />
                      {item.name}
                    </button>
                  );
                })}
            </nav>

            {/* Logout */}
            <div className="p-4 border-t border-gray-200">
              <button
                onClick={handleLogout}
                className="w-full flex items-center px-3 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors"
              >
                <ArrowRightOnRectangleIcon className="w-5 h-5 mr-3" />
                Đăng xuất
              </button>
            </div>
          </div>
        </div>

        {/* Main content */}
        <div className="pl-64">
          <main className="py-6">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
              {children}
            </div>
          </main>
        </div>
      </div>
    </>
  );
}
