import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import Layout from '@/components/Layout';
import { useAuth } from '@/contexts/AuthContext';
import { apiClient } from '@/lib/api';
import { Task, TaskStatistics, CalendarEvent } from '@/types';
import {
  ClipboardDocumentListIcon,
  ChartBarIcon,
  CalendarIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';

export default function Dashboard() {
  const router = useRouter();
  const { isAuthenticated, loading: authLoading } = useAuth();
  const [stats, setStats] = useState<TaskStatistics | null>(null);
  const [recentTasks, setRecentTasks] = useState<Task[]>([]);
  const [upcomingEvents, setUpcomingEvents] = useState<CalendarEvent[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login');
      return;
    }

    if (isAuthenticated) {
      loadDashboardData();
    }
  }, [isAuthenticated, authLoading, router]);

  const loadDashboardData = async () => {
    try {
      console.log('Loading dashboard data...');
      setLoading(true);
      
      // Set timeout to prevent infinite loading
      const timeout = setTimeout(() => {
        console.log('Dashboard data loading timeout');
        setLoading(false);
      }, 10000); // 10 seconds timeout
      
      // Load statistics with error handling
      try {
        console.log('Loading statistics...');
        const myStatsRes = await apiClient.getMyStatistics();
        console.log('Statistics loaded:', myStatsRes);
        setStats(myStatsRes.data || null);
      } catch (error) {
        console.error('Error loading statistics:', error);
        setStats(null);
      }

      // Load tasks with error handling
      try {
        console.log('Loading tasks...');
        const tasksRes = await apiClient.getMyTasks({ per_page: 5 });
        console.log('Tasks loaded:', tasksRes);
        setRecentTasks(tasksRes.data || []);
      } catch (error) {
        console.error('Error loading tasks:', error);
        setRecentTasks([]);
      }

      // Load events with error handling
      try {
        console.log('Loading events...');
        const eventsRes = await apiClient.getUpcomingEvents(5);
        console.log('Events loaded:', eventsRes);
        setUpcomingEvents(eventsRes.data || []);
      } catch (error) {
        console.error('Error loading events:', error);
        setUpcomingEvents([]);
      }
      
      clearTimeout(timeout);
      console.log('Dashboard data loading completed');
    } catch (error) {
      console.error('Error loading dashboard data:', error);
    } finally {
      setLoading(false);
    }
  };

  if (authLoading || !isAuthenticated) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  const formatDate = (dateString: string) => {
    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) {
        return 'Invalid Date';
      }
      return date.toLocaleDateString('vi-VN', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    } catch (error) {
      return 'Invalid Date';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'text-green-600';
      case 'in_progress':
        return 'text-blue-600';
      case 'overdue':
        return 'text-red-600';
      default:
        return 'text-yellow-600';
    }
  };

  return (
    <Layout title="Dashboard - Task Management">
      <div className="space-y-6">
        {/* Page Header */}
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
          <p className="text-gray-600">Welcome to your task management dashboard</p>
        </div>

        {loading ? (
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        ) : (
          <>
            {/* Statistics Cards */}
            {stats && (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div className="card">
                  <div className="flex items-center">
                    <div className="flex-shrink-0">
                      <ClipboardDocumentListIcon className="h-8 w-8 text-blue-600" />
                    </div>
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-500">Total Tasks</p>
                      <p className="text-2xl font-semibold text-gray-900">{stats.total}</p>
                    </div>
                  </div>
                </div>

                <div className="card">
                  <div className="flex items-center">
                    <div className="flex-shrink-0">
                      <ClockIcon className="h-8 w-8 text-yellow-600" />
                    </div>
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-500">Pending</p>
                      <p className="text-2xl font-semibold text-gray-900">{stats.pending}</p>
                    </div>
                  </div>
                </div>

                <div className="card">
                  <div className="flex items-center">
                    <div className="flex-shrink-0">
                      <CheckCircleIcon className="h-8 w-8 text-green-600" />
                    </div>
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-500">Completed</p>
                      <p className="text-2xl font-semibold text-gray-900">{stats.completed}</p>
                    </div>
                  </div>
                </div>

                <div className="card">
                  <div className="flex items-center">
                    <div className="flex-shrink-0">
                      <ExclamationTriangleIcon className="h-8 w-8 text-red-600" />
                    </div>
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-500">Overdue</p>
                      <p className="text-2xl font-semibold text-gray-900">{stats.overdue}</p>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Content Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {/* Recent Tasks */}
              <div className="card">
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-lg font-semibold text-gray-900">Recent Tasks</h2>
                  <button
                    onClick={() => router.push('/tasks/my-tasks')}
                    className="text-sm text-blue-600 hover:text-blue-700 font-medium"
                  >
                    View all
                  </button>
                </div>

                {recentTasks.length > 0 ? (
                  <div className="space-y-3">
                    {recentTasks.map((task) => (
                      <div key={task.id} className="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                        <div className="flex-1">
                          <p className="text-sm font-medium text-gray-900 truncate">
                            {task.title}
                          </p>
                          <p className="text-xs text-gray-500">
                            {task.deadline && `Due: ${formatDate(task.deadline)}`}
                          </p>
                        </div>
                        <span className={`text-xs font-medium ${getStatusColor(task.status)}`}>
                          {task.status.replace('_', ' ')}
                        </span>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-gray-500 text-center py-4">No tasks found</p>
                )}
              </div>

              {/* Upcoming Events */}
              <div className="card">
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-lg font-semibold text-gray-900">Upcoming Events</h2>
                  <button
                    onClick={() => router.push('/calendar')}
                    className="text-sm text-blue-600 hover:text-blue-700 font-medium"
                  >
                    View calendar
                  </button>
                </div>

                {upcomingEvents.length > 0 ? (
                  <div className="space-y-3">
                    {upcomingEvents.map((event) => (
                      <div key={event.id} className="flex items-center space-x-3 py-2 border-b border-gray-100 last:border-b-0">
                        <CalendarIcon className="h-5 w-5 text-blue-600 flex-shrink-0" />
                        <div className="flex-1">
                          <p className="text-sm font-medium text-gray-900">
                            {event.title}
                          </p>
                          <p className="text-xs text-gray-500">
                            {formatDate(event.start)}
                          </p>
                        </div>
                        <span className="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
                          {event.type}
                        </span>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-gray-500 text-center py-4">No upcoming events</p>
                )}
              </div>
            </div>

            {/* Quick Actions */}
            <div className="card">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <button
                  onClick={() => router.push('/tasks/create')}
                  className="flex items-center justify-center py-3 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  <ClipboardDocumentListIcon className="h-5 w-5 mr-2 text-blue-600" />
                  Create Task
                </button>
                <button
                  onClick={() => router.push('/statistics')}
                  className="flex items-center justify-center py-3 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  <ChartBarIcon className="h-5 w-5 mr-2 text-green-600" />
                  View Statistics
                </button>
                <button
                  onClick={() => router.push('/calendar')}
                  className="flex items-center justify-center py-3 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  <CalendarIcon className="h-5 w-5 mr-2 text-blue-600" />
                  View Calendar
                </button>
              </div>
            </div>
          </>
        )}
      </div>
    </Layout>
  );
}
