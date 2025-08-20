import React, { useState } from 'react';
import { useRouter } from 'next/router';
import Layout from '@/components/Layout';
import TaskForm from '@/components/TaskForm';
import { useAuth } from '@/contexts/AuthContext';
import { apiClient } from '@/lib/api';
import { CreateTaskData, UpdateTaskData } from '@/types';
import { toast } from 'react-hot-toast';

export default function CreateTaskPage() {
  const router = useRouter();
  const { isAuthenticated, loading: authLoading, user } = useAuth();
  const [open, setOpen] = useState(true);

  const handleCreate = async (data: CreateTaskData | UpdateTaskData) => {
    try {
      // Sử dụng API mới với phân quyền
      await apiClient.createTaskWithPermissions(data as CreateTaskData);
      toast.success('Task created successfully');
      router.push('/tasks/my-tasks');
    } catch (error: any) {
      console.error('Error creating task:', error);
      toast.error(error.response?.data?.message || 'Failed to create task');
    }
  };

  if (authLoading || !isAuthenticated) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <Layout title="Create Task - Task Management">
      <TaskForm isOpen={open} onClose={() => setOpen(false)} onSubmit={handleCreate} />
    </Layout>
  );
}


