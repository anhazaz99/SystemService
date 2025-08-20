import React, { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import Layout from '@/components/Layout';
import TaskCard from '@/components/TaskCard';
import { useAuth } from '@/contexts/AuthContext';
import { apiClient } from '@/lib/api';
import { Task, TaskFilters } from '@/types';
import { ClipboardDocumentListIcon } from '@heroicons/react/24/outline';

export default function CreatedTasksPage() {
  const router = useRouter();
  const { isAuthenticated, loading: authLoading, user } = useAuth();
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<TaskFilters>({ per_page: 12 });

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login');
      return;
    }
    if (isAuthenticated && user?.user_type === 'lecturer') {
      loadTasks();
    }
  }, [isAuthenticated, authLoading, router, filters, user]);

  const loadTasks = async () => {
    try {
      console.log('Loading created tasks with filters:', filters);
      setLoading(true);
      const response = await apiClient.getCreatedTasks(filters);
      console.log('Created tasks response:', response);
      setTasks(response.data || []);
    } catch (error) {
      console.error('Error loading created tasks:', error);
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

  if (user?.user_type !== 'lecturer') {
    router.replace('/');
    return null;
  }

  return (
    <Layout title="Created Tasks - Task Management">
      <div className="space-y-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Tasks I Created</h1>
          <p className="text-gray-600">Tasks created by you</p>
        </div>

        {loading ? (
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        ) : tasks.length ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {tasks.map((task) => (
              <TaskCard key={task.id} task={task} showActions={false} />
            ))}
          </div>
        ) : (
          <div className="text-center py-12">
            <ClipboardDocumentListIcon className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
          </div>
        )}
      </div>
    </Layout>
  );
}


