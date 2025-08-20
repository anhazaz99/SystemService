import React from 'react';
import { Task } from '@/types';
import {
  ClockIcon,
  UserIcon,
  CalendarIcon,
  FlagIcon,
} from '@heroicons/react/24/outline';

interface TaskCardProps {
  task: Task;
  onEdit?: (task: Task) => void;
  onDelete?: (id: number) => void;
  onStatusChange?: (id: number, status: string) => void;
  showActions?: boolean;
}

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800',
  in_progress: 'bg-blue-100 text-blue-800',
  completed: 'bg-green-100 text-green-800',
  overdue: 'bg-red-100 text-red-800',
};

const priorityColors = {
  low: 'text-green-600',
  medium: 'text-yellow-600',
  high: 'text-red-600',
};

const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
];

export default function TaskCard({
  task,
  onEdit,
  onDelete,
  onStatusChange,
  showActions = true,
}: TaskCardProps) {
  const handleStatusChange = (newStatus: string) => {
    if (onStatusChange) {
      onStatusChange(task.id, newStatus);
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('vi-VN', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const isOverdue = task.deadline && new Date(task.deadline) < new Date() && task.status !== 'completed';

  return (
    <div className="card hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <h3 className="text-lg font-semibold text-gray-900 mb-2">
            {task.title}
          </h3>
          {task.description && (
            <p className="text-gray-600 text-sm mb-3 line-clamp-2">
              {task.description}
            </p>
          )}
        </div>
        
        {/* Priority indicator */}
        <div className="ml-4">
          <FlagIcon className={`w-5 h-5 ${priorityColors[task.priority]}`} />
        </div>
      </div>

      {/* Task details */}
      <div className="space-y-2 mb-4">
        {/* Status */}
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[task.status]}`}>
              {task.status.replace('_', ' ')}
            </span>
            {isOverdue && (
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                Overdue
              </span>
            )}
          </div>
          <span className="text-xs text-gray-500 capitalize">
            {task.priority} priority
          </span>
        </div>

        {/* Deadline */}
        {task.deadline && (
          <div className="flex items-center space-x-2 text-sm text-gray-600">
            <CalendarIcon className="w-4 h-4" />
            <span>Deadline: {formatDate(task.deadline)}</span>
          </div>
        )}

        {/* Creator */}
        <div className="flex items-center space-x-2 text-sm text-gray-600">
          <UserIcon className="w-4 h-4" />
          <span>Created by: {task.creator?.full_name || `${task.creator_type} ${task.creator_id}`}</span>
        </div>

        {/* Created time */}
        <div className="flex items-center space-x-2 text-sm text-gray-600">
          <ClockIcon className="w-4 h-4" />
          <span>Created: {formatDate(task.created_at)}</span>
        </div>
      </div>

      {/* Actions */}
      {showActions && (
        <div className="flex items-center justify-between pt-4 border-t border-gray-200">
          {/* Status change */}
          <div className="flex items-center space-x-2">
            <label className="text-sm text-gray-600">Status:</label>
            <select
              value={task.status}
              onChange={(e) => handleStatusChange(e.target.value)}
              className="text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
            >
              {statusOptions.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          </div>

          {/* Action buttons */}
          <div className="flex items-center space-x-2">
            {onEdit && (
              <button
                onClick={() => onEdit(task)}
                className="text-sm text-blue-600 hover:text-blue-700 font-medium"
              >
                Edit
              </button>
            )}
            {onDelete && (
              <button
                onClick={() => onDelete(task.id)}
                className="text-sm text-red-600 hover:text-red-700 font-medium"
              >
                Delete
              </button>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
