import React, { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'react-hot-toast';
import { Task, CreateTaskData, UpdateTaskData } from '@/types';
import { useAuth } from '@/contexts/AuthContext';
import { XMarkIcon } from '@heroicons/react/24/outline';
import ReceiverSelector from './ReceiverSelector';

interface TaskFormProps {
  task?: Task;
  onSubmit: (data: CreateTaskData | UpdateTaskData) => Promise<void>;
  onClose: () => void;
  isOpen: boolean;
}

interface FormData {
  title: string;
  description: string;
  deadline: string;
  status: 'pending' | 'in_progress' | 'completed' | 'overdue';
  priority: 'low' | 'medium' | 'high';
}

export default function TaskForm({ task, onSubmit, onClose, isOpen }: TaskFormProps) {
  const { user } = useAuth();
  const [loading, setLoading] = useState(false);
  const [selectedReceiver, setSelectedReceiver] = useState<{ receiver_id: number; receiver_type: 'student' | 'lecturer' | 'class' | 'all_students' } | null>(null);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<FormData>({
    defaultValues: {
      title: '',
      description: '',
      deadline: '',
      status: 'pending',
      priority: 'medium',
    },
  });

  useEffect(() => {
    if (task) {
      reset({
        title: task.title,
        description: task.description || '',
        deadline: task.deadline ? new Date(task.deadline).toISOString().slice(0, 16) : '',
        status: task.status,
        priority: task.priority,
      });
      
      if (task.receivers && task.receivers.length > 0) {
        setSelectedReceiver({
          receiver_id: task.receivers[0].receiver_id,
          receiver_type: task.receivers[0].receiver_type as 'student' | 'lecturer' | 'class' | 'all_students'
        });
      }
    } else {
      reset({
        title: '',
        description: '',
        deadline: '',
        status: 'pending',
        priority: 'medium',
      });
      setSelectedReceiver(null);
    }
  }, [task, reset]);

  const handleFormSubmit = async (data: FormData) => {
    if (!user || !selectedReceiver) {
      toast.error('Please select a receiver');
      return;
    }

    setLoading(true);
    try {
      const submitData: CreateTaskData | UpdateTaskData = {
        ...data,
        creator_id: user.id,
        creator_type: user.user_type,
        receivers: [selectedReceiver],
      };

      await onSubmit(submitData);
      onClose();
      toast.success(task ? 'Task updated successfully!' : 'Task created successfully!');
    } catch (error) {
      toast.error('An error occurred. Please try again.');
    } finally {
      setLoading(false);
    }
  };



  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div className="flex items-center justify-between p-6 border-b border-gray-200">
          <h2 className="text-xl font-semibold text-gray-900">
            {task ? 'Edit Task' : 'Create New Task'}
          </h2>
          <button
            onClick={onClose}
            className="text-gray-400 hover:text-gray-600"
          >
            <XMarkIcon className="w-6 h-6" />
          </button>
        </div>

        <form onSubmit={handleSubmit(handleFormSubmit)} className="p-6 space-y-6">
          {/* Title */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Title *
            </label>
            <input
              type="text"
              {...register('title', { required: 'Title is required' })}
              className="form-input"
              placeholder="Enter task title"
            />
            {errors.title && (
              <p className="mt-1 text-sm text-red-600">{errors.title.message}</p>
            )}
          </div>

          {/* Description */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Description
            </label>
            <textarea
              {...register('description')}
              rows={4}
              className="form-input"
              placeholder="Enter task description"
            />
          </div>

          {/* Deadline */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Deadline
            </label>
            <input
              type="datetime-local"
              {...register('deadline')}
              className="form-input"
            />
          </div>

          {/* Status and Priority */}
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <select {...register('status')} className="form-select">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Priority
              </label>
              <select {...register('priority')} className="form-select">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>

          {/* Receiver Selector */}
          <ReceiverSelector
            selectedReceiver={selectedReceiver}
            onReceiverChange={setSelectedReceiver}
            userType={user?.user_type || 'student'}
          />

          {/* Actions */}
          <div className="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
            <button
              type="button"
              onClick={onClose}
              className="btn-secondary"
              disabled={loading}
            >
              Cancel
            </button>
            <button
              type="submit"
              className="btn-primary"
              disabled={loading}
            >
              {loading ? 'Saving...' : task ? 'Update Task' : 'Create Task'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
