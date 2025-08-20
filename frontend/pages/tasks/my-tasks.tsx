import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import Layout from '@/components/Layout';
import TaskCard from '@/components/TaskCard';
import TaskForm from '@/components/TaskForm';
import { useAuth } from '@/contexts/AuthContext';
import { apiClient } from '@/lib/api';
import { Task, TaskFilters, CreateTaskData, UpdateTaskData } from '@/types';
import { toast } from 'react-hot-toast';
import {
  PlusIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  ClipboardDocumentListIcon,
} from '@heroicons/react/24/outline';

export default function MyTasksPage() {
  const router = useRouter();
  const { isAuthenticated, loading: authLoading, user } = useAuth();
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  const [showTaskForm, setShowTaskForm] = useState(false);
  const [editingTask, setEditingTask] = useState<Task | undefined>();
  const [filters, setFilters] = useState<TaskFilters>({
    search: '',
    status: '',
    priority: '',
    per_page: 12,
  });
  const [pagination, setPagination] = useState({
    current_page: 1,
    total: 0,
    last_page: 1,
  });

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login');
      return;
    }

    if (isAuthenticated) {
      loadTasks();
    }
  }, [isAuthenticated, authLoading, router, filters]);

  const loadTasks = async () => {
    try {
      console.log('Loading my tasks with filters:', filters);
      setLoading(true);
      const response = await apiClient.getMyTasks(filters);
      console.log('My tasks response:', response);
      setTasks(response.data || []);
      if (response.pagination) {
        setPagination(response.pagination);
      }
    } catch (error) {
      console.error('Error loading my tasks:', error);
      toast.error('Failed to load tasks');
    } finally {
      setLoading(false);
    }
  };

  const handleCreateTask = async (data: CreateTaskData | UpdateTaskData) => {
    try {
      await apiClient.createTask(data as CreateTaskData);
      toast.success('Task created successfully!');
      loadTasks();
    } catch (error: any) {
      const message = error.response?.data?.message || 'Failed to create task';
      toast.error(message);
      throw error;
    }
  };

  const handleUpdateTask = async (data: CreateTaskData | UpdateTaskData) => {
    if (!editingTask) return;
    
    try {
      await apiClient.updateTask(editingTask.id, data as UpdateTaskData);
      toast.success('Task updated successfully!');
      loadTasks();
    } catch (error: any) {
      const message = error.response?.data?.message || 'Failed to update task';
      toast.error(message);
      throw error;
    }
  };

  const handleStatusChange = async (id: number, status: string) => {
    try {
      await apiClient.updateTaskStatus(id, status);
      toast.success('Task status updated!');
      loadTasks();
    } catch (error: any) {
      const message = error.response?.data?.message || 'Failed to update task status';
      toast.error(message);
    }
  };

  const handleDeleteTask = async (id: number) => {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    try {
      await apiClient.deleteTask(id);
      toast.success('Task deleted successfully!');
      loadTasks();
    } catch (error: any) {
      const message = error.response?.data?.message || 'Failed to delete task';
      toast.error(message);
    }
  };

  const handleFilterChange = (key: string, value: string) => {
    setFilters(prev => ({ ...prev, [key]: value }));
  };

  const clearFilters = () => {
    setFilters({
      search: '',
      status: '',
      priority: '',
      per_page: 12,
    });
  };

  if (authLoading || !isAuthenticated) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <Layout title="My Tasks - Task Management">
      <div className="space-y-6">
        {/* Page Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">My Tasks</h1>
            <p className="text-gray-600">Manage tasks assigned to you</p>
          </div>
          {user?.user_type === 'lecturer' && (
            <button
              onClick={() => {
                setEditingTask(undefined);
                setShowTaskForm(true);
              }}
              className="btn-primary flex items-center"
            >
              <PlusIcon className="w-5 h-5 mr-2" />
              Create Task
            </button>
          )}
        </div>

        {/* Filters */}
        <div className="card">
          <div className="flex items-center space-x-4 mb-4">
            <FunnelIcon className="w-5 h-5 text-gray-400" />
            <span className="text-sm font-medium text-gray-700">Filters</span>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            {/* Search */}
            <div className="relative">
              <MagnifyingGlassIcon className="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
              <input
                type="text"
                placeholder="Search tasks..."
                value={filters.search}
                onChange={(e) => handleFilterChange('search', e.target.value)}
                className="pl-10 form-input"
              />
            </div>

            {/* Status Filter */}
            <select
              value={filters.status}
              onChange={(e) => handleFilterChange('status', e.target.value)}
              className="form-select"
            >
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="overdue">Overdue</option>
            </select>

            {/* Priority Filter */}
            <select
              value={filters.priority}
              onChange={(e) => handleFilterChange('priority', e.target.value)}
              className="form-select"
            >
              <option value="">All Priority</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>

            {/* Clear Filters */}
            <button
              onClick={clearFilters}
              className="btn-secondary"
            >
              Clear Filters
            </button>
          </div>
        </div>

        {/* Tasks Grid */}
        {loading ? (
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        ) : (
          <>
            {tasks.length > 0 ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {tasks.map((task) => (
                  <TaskCard
                    key={task.id}
                    task={task}
                    onEdit={user?.user_type === 'lecturer' ? (task) => {
                      setEditingTask(task);
                      setShowTaskForm(true);
                    } : undefined}
                    onDelete={user?.user_type === 'lecturer' ? handleDeleteTask : undefined}
                    onStatusChange={handleStatusChange}
                  />
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <ClipboardDocumentListIcon className="mx-auto h-12 w-12 text-gray-400" />
                <h3 className="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
                <p className="mt-1 text-sm text-gray-500">
                  {filters.search || filters.status || filters.priority
                    ? 'Try adjusting your filters'
                    : 'Get started by creating a new task'}
                </p>
                {user?.user_type === 'lecturer' && !filters.search && !filters.status && !filters.priority && (
                  <div className="mt-6">
                    <button
                      onClick={() => {
                        setEditingTask(undefined);
                        setShowTaskForm(true);
                      }}
                      className="btn-primary"
                    >
                      <PlusIcon className="w-5 h-5 mr-2" />
                      Create your first task
                    </button>
                  </div>
                )}
              </div>
            )}

            {/* Pagination */}
            {pagination.last_page > 1 && (
              <div className="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6 rounded-lg">
                <div className="flex justify-between flex-1 sm:hidden">
                  <button
                    onClick={() => handleFilterChange('page', String(pagination.current_page - 1))}
                    disabled={pagination.current_page <= 1}
                    className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                  >
                    Previous
                  </button>
                  <button
                    onClick={() => handleFilterChange('page', String(pagination.current_page + 1))}
                    disabled={pagination.current_page >= pagination.last_page}
                    className="relative ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                  >
                    Next
                  </button>
                </div>
                <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                  <div>
                    <p className="text-sm text-gray-700">
                      Showing page <span className="font-medium">{pagination.current_page}</span> of{' '}
                      <span className="font-medium">{pagination.last_page}</span>
                    </p>
                  </div>
                </div>
              </div>
            )}
          </>
        )}

        {/* Task Form Modal */}
        <TaskForm
          task={editingTask}
          isOpen={showTaskForm}
          onClose={() => {
            setShowTaskForm(false);
            setEditingTask(undefined);
          }}
          onSubmit={editingTask ? handleUpdateTask : handleCreateTask}
        />
      </div>
    </Layout>
  );
}
