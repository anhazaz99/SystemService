import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import Layout from '@/components/Layout';
import { useAuth } from '@/contexts/AuthContext';
import { apiClient } from '@/lib/api';
import { TaskStatistics } from '@/types';
import { toast } from 'react-hot-toast';
import {
  ChartBarIcon,
  ClipboardDocumentListIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  PlayIcon,
} from '@heroicons/react/24/outline';

export default function StatisticsPage() {
  const router = useRouter();
  const { isAuthenticated, loading: authLoading, user } = useAuth();
  const [myStats, setMyStats] = useState<TaskStatistics | null>(null);
  const [createdStats, setCreatedStats] = useState<TaskStatistics | null>(null);
  const [overviewStats, setOverviewStats] = useState<TaskStatistics | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login');
      return;
    }

    if (isAuthenticated) {
      loadStatistics();
    }
  }, [isAuthenticated, authLoading, router]);

  const loadStatistics = async () => {
    try {
      setLoading(true);
      
      // Load my statistics (always available)
      const myStatsPromise = apiClient.getMyStatistics();
      
      // Load created statistics (for lecturers)
      const createdStatsPromise = user?.user_type === 'lecturer' 
        ? apiClient.getCreatedStatistics()
        : Promise.resolve({ data: null });
      
      // Load overview statistics (admin only)
      const overviewStatsPromise = user?.account?.is_admin
        ? apiClient.getOverviewStatistics()
        : Promise.resolve({ data: null });

      const [myStatsRes, createdStatsRes, overviewStatsRes] = await Promise.allSettled([
        myStatsPromise,
        createdStatsPromise,
        overviewStatsPromise,
      ]);

      if (myStatsRes.status === 'fulfilled') {
        setMyStats(myStatsRes.value.data || null);
      } else {
        console.error('Failed to load my statistics:', myStatsRes.reason);
      }

      if (createdStatsRes.status === 'fulfilled' && createdStatsRes.value.data) {
        setCreatedStats(createdStatsRes.value.data);
      }

      if (overviewStatsRes.status === 'fulfilled' && overviewStatsRes.value.data) {
        setOverviewStats(overviewStatsRes.value.data);
      } else if (overviewStatsRes.status === 'rejected') {
        console.log('Overview statistics not available (admin access required)');
      }

    } catch (error) {
      toast.error('Failed to load statistics');
      console.error('Error loading statistics:', error);
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

  const StatCard = ({ 
    title, 
    value, 
    icon: Icon, 
    color, 
    description 
  }: { 
    title: string; 
    value: number; 
    icon: any; 
    color: string; 
    description?: string;
  }) => (
    <div className="card">
      <div className="flex items-center">
        <div className="flex-shrink-0">
          <Icon className={`h-8 w-8 ${color}`} />
        </div>
        <div className="ml-4 flex-1">
          <p className="text-sm font-medium text-gray-500">{title}</p>
          <p className="text-2xl font-semibold text-gray-900">{value}</p>
          {description && (
            <p className="text-xs text-gray-400 mt-1">{description}</p>
          )}
        </div>
      </div>
    </div>
  );

  const ProgressBar = ({ label, value, total, color }: { label: string; value: number; total: number; color: string }) => {
    const percentage = total > 0 ? (value / total) * 100 : 0;
    
    return (
      <div className="mb-4">
        <div className="flex justify-between text-sm text-gray-600 mb-1">
          <span>{label}</span>
          <span>{value} ({percentage.toFixed(1)}%)</span>
        </div>
        <div className="w-full bg-gray-200 rounded-full h-2">
          <div
            className={`h-2 rounded-full ${color}`}
            style={{ width: `${percentage}%` }}
          />
        </div>
      </div>
    );
  };

  return (
    <Layout title="Statistics - Task Management">
      <div className="space-y-6">
        {/* Page Header */}
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Statistics</h1>
          <p className="text-gray-600">View detailed statistics about your tasks</p>
        </div>

        {loading ? (
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        ) : (
          <div className="space-y-8">
            {/* My Tasks Statistics */}
            {myStats && (
              <div>
                <h2 className="text-lg font-semibold text-gray-900 mb-4">My Tasks</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                  <StatCard
                    title="Total Tasks"
                    value={myStats.total}
                    icon={ClipboardDocumentListIcon}
                    color="text-blue-600"
                    description="All assigned tasks"
                  />
                  <StatCard
                    title="Pending"
                    value={myStats.pending}
                    icon={ClockIcon}
                    color="text-yellow-600"
                    description="Not started yet"
                  />
                  <StatCard
                    title="In Progress"
                    value={myStats.in_progress}
                    icon={PlayIcon}
                    color="text-blue-600"
                    description="Currently working on"
                  />
                  <StatCard
                    title="Completed"
                    value={myStats.completed}
                    icon={CheckCircleIcon}
                    color="text-green-600"
                    description="Successfully finished"
                  />
                  <StatCard
                    title="Overdue"
                    value={myStats.overdue}
                    icon={ExclamationTriangleIcon}
                    color="text-red-600"
                    description="Past deadline"
                  />
                </div>

                {/* Progress visualization */}
                <div className="card">
                  <h3 className="text-md font-medium text-gray-900 mb-4">Task Progress</h3>
                  <ProgressBar 
                    label="Pending" 
                    value={myStats.pending} 
                    total={myStats.total} 
                    color="bg-yellow-500" 
                  />
                  <ProgressBar 
                    label="In Progress" 
                    value={myStats.in_progress} 
                    total={myStats.total} 
                    color="bg-blue-500" 
                  />
                  <ProgressBar 
                    label="Completed" 
                    value={myStats.completed} 
                    total={myStats.total} 
                    color="bg-green-500" 
                  />
                  <ProgressBar 
                    label="Overdue" 
                    value={myStats.overdue} 
                    total={myStats.total} 
                    color="bg-red-500" 
                  />
                </div>
              </div>
            )}

            {/* Created Tasks Statistics (Lecturer only) */}
            {user?.user_type === 'lecturer' && createdStats && (
              <div>
                <h2 className="text-lg font-semibold text-gray-900 mb-4">Tasks I Created</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                  <StatCard
                    title="Total Created"
                    value={createdStats.total}
                    icon={ClipboardDocumentListIcon}
                    color="text-purple-600"
                    description="Tasks you created"
                  />
                  <StatCard
                    title="Pending"
                    value={createdStats.pending}
                    icon={ClockIcon}
                    color="text-yellow-600"
                  />
                  <StatCard
                    title="In Progress"
                    value={createdStats.in_progress}
                    icon={PlayIcon}
                    color="text-blue-600"
                  />
                  <StatCard
                    title="Completed"
                    value={createdStats.completed}
                    icon={CheckCircleIcon}
                    color="text-green-600"
                  />
                  <StatCard
                    title="Overdue"
                    value={createdStats.overdue}
                    icon={ExclamationTriangleIcon}
                    color="text-red-600"
                  />
                </div>

                <div className="card">
                  <h3 className="text-md font-medium text-gray-900 mb-4">Created Tasks Progress</h3>
                  <ProgressBar 
                    label="Pending" 
                    value={createdStats.pending} 
                    total={createdStats.total} 
                    color="bg-yellow-500" 
                  />
                  <ProgressBar 
                    label="In Progress" 
                    value={createdStats.in_progress} 
                    total={createdStats.total} 
                    color="bg-blue-500" 
                  />
                  <ProgressBar 
                    label="Completed" 
                    value={createdStats.completed} 
                    total={createdStats.total} 
                    color="bg-green-500" 
                  />
                  <ProgressBar 
                    label="Overdue" 
                    value={createdStats.overdue} 
                    total={createdStats.total} 
                    color="bg-red-500" 
                  />
                </div>
              </div>
            )}

            {/* System Overview (Admin only) */}
            {user?.account?.is_admin && overviewStats && (
              <div>
                <h2 className="text-lg font-semibold text-gray-900 mb-4">System Overview</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                  <StatCard
                    title="System Total"
                    value={overviewStats.total}
                    icon={ChartBarIcon}
                    color="text-indigo-600"
                    description="All tasks in system"
                  />
                  <StatCard
                    title="Pending"
                    value={overviewStats.pending}
                    icon={ClockIcon}
                    color="text-yellow-600"
                  />
                  <StatCard
                    title="In Progress"
                    value={overviewStats.in_progress}
                    icon={PlayIcon}
                    color="text-blue-600"
                  />
                  <StatCard
                    title="Completed"
                    value={overviewStats.completed}
                    icon={CheckCircleIcon}
                    color="text-green-600"
                  />
                  <StatCard
                    title="Overdue"
                    value={overviewStats.overdue}
                    icon={ExclamationTriangleIcon}
                    color="text-red-600"
                  />
                </div>
              </div>
            )}

            {/* Refresh Button */}
            <div className="flex justify-center">
              <button
                onClick={loadStatistics}
                className="btn-primary"
                disabled={loading}
              >
                {loading ? 'Loading...' : 'Refresh Statistics'}
              </button>
            </div>
          </div>
        )}
      </div>
    </Layout>
  );
}
