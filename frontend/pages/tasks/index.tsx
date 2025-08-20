import React, { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import Layout from '@/components/Layout';
import TaskCard from '@/components/TaskCard';
import { useAuth } from '@/contexts/AuthContext';
import { apiClient } from '@/lib/api';
import { Task, TaskFilters } from '@/types';
import { ClipboardDocumentListIcon } from '@heroicons/react/24/outline';

export default function AllTasksPage() {
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
    if (isAuthenticated && user?.account?.is_admin) {
      loadTasks();
    }
  }, [isAuthenticated, authLoading, router, filters, user]);

  const loadTasks = async () => {
    try {
      setLoading(true);
      const response = await apiClient.getTasks(filters);
      setTasks(response.data || []);
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

  if (!user?.account?.is_admin) {
    router.replace('/');
    return null;
  }

  return (
    <Layout title="All Tasks - Task Management">
      <div className="space-y-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">All Tasks</h1>
          <p className="text-gray-600">Admin view of all tasks in the system</p>
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


