// API Response Types
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: Record<string, string[]>;
  error_code?: string;
  pagination?: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
}

// User & Auth Types
export interface User {
  id: number;
  email: string;
  full_name: string;
  phone?: string;
  address?: string;
  user_type: 'lecturer' | 'student';
  account: {
    username: string;
    is_admin?: boolean;
  };
}

export interface LoginCredentials {
  username: string;
  password: string;
  user_type: 'lecturer' | 'student';
}

export interface RegisterStudentData {
  student_code: string;
  full_name: string;
  email: string;
  phone?: string;
  address?: string;
  username: string;
  password: string;
  password_confirmation: string;
}

export interface RegisterLecturerData {
  lecturer_code: string;
  full_name: string;
  email: string;
  phone?: string;
  address?: string;
  username: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  user: User;
  token: string;
  message: string;
}

export interface RegisterResponse {
  message: string;
  data: User;
  account_info: {
    username: string;
    message: string;
  };
}

// Task Types
export interface Task {
  id: number;
  title: string;
  description?: string;
  deadline?: string;
  status: 'pending' | 'in_progress' | 'completed' | 'overdue';
  priority: 'low' | 'medium' | 'high';
  creator_id: number;
  creator_type: 'lecturer' | 'student';
  created_at: string;
  updated_at: string;
  creator?: User;
  receivers?: TaskReceiver[];
  files?: TaskFile[];
}

export interface TaskReceiver {
  id: number;
  receiver_id: number;
  receiver_type: 'student' | 'lecturer' | 'class' | 'all_students';
  task_id: number;
  receiver?: User;
}

export interface TaskFile {
  id: number;
  original_name: string;
  file_path: string;
  file_size: number;
  file_type: string;
  uploaded_by: number;
  task_id: number;
}

export interface CreateTaskData {
  title: string;
  description?: string;
  deadline?: string;
  status?: 'pending' | 'in_progress' | 'completed' | 'overdue';
  priority?: 'low' | 'medium' | 'high';
  creator_id: number;
  creator_type: 'lecturer' | 'student';
  receivers: {
    receiver_id: number;
    receiver_type: 'student' | 'lecturer' | 'class' | 'all_students';
  }[];
}

export interface UpdateTaskData extends Partial<CreateTaskData> {
  // Tất cả fields từ CreateTaskData đều optional cho update
}

// Statistics Types
export interface TaskStatistics {
  total: number;
  pending: number;
  in_progress: number;
  completed: number;
  overdue: number;
}

// Filter Types
export interface TaskFilters {
  receiver_id?: number;
  receiver_type?: string;
  creator_id?: number;
  creator_type?: string;
  search?: string;
  status?: string;
  priority?: string;
  per_page?: number;
  page?: number;
}

// Calendar Event Types
export interface CalendarEvent {
  id: number;
  title: string;
  start: string;
  end?: string;
  type: 'task' | 'deadline' | 'meeting';
  task?: Task;
}
