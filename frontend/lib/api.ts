import axios, { AxiosInstance, AxiosResponse } from 'axios';
import Cookies from 'js-cookie';
import { toast } from 'react-hot-toast';
import {
  ApiResponse,
  AuthResponse,
  LoginCredentials,
  Task,
  CreateTaskData,
  UpdateTaskData,
  TaskStatistics,
  TaskFilters,
  CalendarEvent,
  RegisterStudentData,
  RegisterLecturerData,
  RegisterResponse,
  User,
} from '@/types';

/**
 * API Client cho Task Management System
 * 
 * Client này cung cấp tất cả methods để tương tác với Clean Architecture backend:
 * - Authentication (JWT)
 * - Task CRUD operations
 * - Statistics
 * - Calendar events
 * - File uploads
 */
class ApiClient {
  private client: AxiosInstance;
  private baseURL = process.env.NEXT_PUBLIC_API_BASE_URL || '/api';

  constructor() {
    this.client = axios.create({
      baseURL: this.baseURL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    // Request interceptor để thêm token
    this.client.interceptors.request.use((config) => {
      const token = this.getToken();
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    });

    // Response interceptor để xử lý errors
    this.client.interceptors.response.use(
      (response) => response,
      (error) => {
        // Log tất cả lỗi để debug, không auto logout
        console.error('API Error:', {
          status: error.response?.status,
          url: error.config?.url,
          method: error.config?.method,
          data: error.response?.data,
          message: error.message
        });
        
        // Chỉ hiển thị toast cho lỗi 401, không logout
        if (error.response?.status === 401) {
          toast.error('API Error 401: ' + (error.response?.data?.message || 'Unauthorized'));
        }
        
        return Promise.reject(error);
      }
    );
  }

  // Token management
  private getToken(): string | null {
    return Cookies.get('token') || null;
  }

  public setToken(token: string): void {
    Cookies.set('token', token, { expires: 1 }); // 1 day
  }

  public removeToken(): void {
    Cookies.remove('token');
  }

  public isAuthenticated(): boolean {
    return !!this.getToken();
  }

  // Authentication endpoints
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response: AxiosResponse<AuthResponse> = await this.client.post(
      '/login',
      credentials
    );
    return response.data;
  }

  async registerStudent(data: RegisterStudentData): Promise<RegisterResponse> {
    const response: AxiosResponse<RegisterResponse> = await this.client.post(
      '/register/student',
      data
    );
    return response.data;
  }

  async registerLecturer(data: RegisterLecturerData): Promise<RegisterResponse> {
    const response: AxiosResponse<RegisterResponse> = await this.client.post(
      '/register/lecturer',
      data
    );
    return response.data;
  }

  async logout(): Promise<void> {
    try {
      await this.client.post('/logout');
    } finally {
      this.removeToken();
    }
  }

  async getMe(): Promise<ApiResponse<User>> {
    const response = await this.client.get('/me');
    return response.data;
  }

  // Task endpoints
  async getTasks(filters: TaskFilters = {}): Promise<ApiResponse<Task[]>> {
    const response = await this.client.get('/v1/tasks', { params: filters });
    return response.data;
  }

  async getMyTasks(filters: TaskFilters = {}): Promise<ApiResponse<Task[]>> {
    const response = await this.client.get('/v1/tasks/my-tasks', { params: filters });
    return response.data;
  }

  async getCreatedTasks(filters: TaskFilters = {}): Promise<ApiResponse<Task[]>> {
    const response = await this.client.get('/v1/tasks/lecturer/created', { params: filters });
    return response.data;
  }

  async getTask(id: number): Promise<ApiResponse<Task>> {
    const response = await this.client.get(`/v1/tasks/${id}`);
    return response.data;
  }

  async createTask(data: CreateTaskData): Promise<ApiResponse<Task>> {
    const response = await this.client.post('/v1/tasks', data);
    return response.data;
  }

  async createTaskWithPermissions(data: CreateTaskData): Promise<ApiResponse<Task>> {
    const response = await this.client.post('/v1/tasks/create-with-permissions', data);
    return response.data;
  }

  async updateTask(id: number, data: UpdateTaskData): Promise<ApiResponse<Task>> {
    const response = await this.client.put(`/v1/tasks/${id}`, data);
    return response.data;
  }

  async updateTaskStatus(id: number, status: string): Promise<ApiResponse<Task>> {
    const response = await this.client.patch(`/v1/tasks/${id}/status`, { status });
    return response.data;
  }

  async deleteTask(id: number): Promise<ApiResponse<void>> {
    const response = await this.client.delete(`/v1/tasks/${id}`);
    return response.data;
  }

  async restoreTask(id: number): Promise<ApiResponse<Task>> {
    const response = await this.client.post(`/v1/tasks/${id}/restore`);
    return response.data;
  }

  async forceDeleteTask(id: number): Promise<ApiResponse<void>> {
    const response = await this.client.delete(`/v1/tasks/${id}/force`);
    return response.data;
  }

  // Task assignment
  async assignTask(id: number, receivers: { receiver_id: number; receiver_type: string }[]): Promise<ApiResponse<void>> {
    const response = await this.client.post(`/v1/tasks/${id}/assign`, { receivers });
    return response.data;
  }

  async revokeTask(id: number, receiver_id: number, receiver_type: string): Promise<ApiResponse<void>> {
    const response = await this.client.post(`/v1/tasks/${id}/revoke`, { receiver_id, receiver_type });
    return response.data;
  }

  // File operations
  async uploadTaskFiles(id: number, files: File[]): Promise<ApiResponse<any>> {
    const formData = new FormData();
    files.forEach((file, index) => {
      formData.append(`files[${index}]`, file);
    });

    const response = await this.client.post(`/v1/tasks/${id}/files`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async deleteTaskFile(taskId: number, fileId: number): Promise<ApiResponse<void>> {
    const response = await this.client.delete(`/v1/tasks/${taskId}/files/${fileId}`);
    return response.data;
  }

  // Statistics endpoints
  async getMyStatistics(): Promise<ApiResponse<TaskStatistics>> {
    const response = await this.client.get('/v1/tasks/statistics/my');
    return response.data;
  }

  async getCreatedStatistics(): Promise<ApiResponse<TaskStatistics>> {
    const response = await this.client.get('/v1/tasks/statistics/created');
    return response.data;
  }

  async getOverviewStatistics(): Promise<ApiResponse<TaskStatistics>> {
    const response = await this.client.get('/v1/tasks/statistics/overview');
    return response.data;
  }

  // Calendar endpoints (aligned with backend routes)
  async getCalendarEvents(start?: string, end?: string): Promise<ApiResponse<CalendarEvent[]>> {
    // For general users, use by-range route
    const params: any = {};
    if (start) params.start = start;
    if (end) params.end = end;
    const response = await this.client.get('/v1/calendar/events/by-range', { params });
    return response.data;
  }

  async getEventsByDate(date: string): Promise<ApiResponse<CalendarEvent[]>> {
    const response = await this.client.get('/v1/calendar/events/by-date', {
      params: { date },
    });
    return response.data;
  }

  async getEventsByRange(start: string, end: string): Promise<ApiResponse<CalendarEvent[]>> {
    const response = await this.client.get('/v1/calendar/events/by-range', {
      params: { start, end },
    });
    return response.data;
  }

  async getUpcomingEvents(limit: number = 10): Promise<ApiResponse<CalendarEvent[]>> {
    const response = await this.client.get('/v1/calendar/events/upcoming', {
      params: { days: 7 }, // Backend expects 'days' not 'limit'
    });
    return response.data;
  }

  async getTodayEvents(): Promise<ApiResponse<CalendarEvent[]>> {
    // Not explicitly provided by backend; use by-date with today's date
    const today = new Date();
    const date = today.toISOString().slice(0, 10);
    return this.getEventsByDate(date);
  }

  async getWeekEvents(): Promise<ApiResponse<CalendarEvent[]>> {
    // Derive current week range and call by-range
    const now = new Date();
    const day = now.getDay();
    const diffToMonday = (day + 6) % 7; // 0 (Sun) -> 6, 1 (Mon) -> 0
    const monday = new Date(now);
    monday.setDate(now.getDate() - diffToMonday);
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    const start = new Date(Date.UTC(monday.getFullYear(), monday.getMonth(), monday.getDate())).toISOString();
    const end = new Date(Date.UTC(sunday.getFullYear(), sunday.getMonth(), sunday.getDate(), 23, 59, 59)).toISOString();
    return this.getEventsByRange(start, end);
  }

  async getMonthEvents(year: number, month: number): Promise<ApiResponse<CalendarEvent[]>> {
    // month is 1-12 expected by caller; convert to JS Date month 0-11
    const startDate = new Date(Date.UTC(year, month - 1, 1));
    const endDate = new Date(Date.UTC(year, month, 0, 23, 59, 59));
    const start = startDate.toISOString();
    const end = endDate.toISOString();
    return this.getEventsByRange(start, end);
  }

  // New permission-based endpoints
  async getFaculties(): Promise<ApiResponse<any[]>> {
    const response = await this.client.get('/v1/tasks/faculties');
    return response.data;
  }

  async getClassesByFaculty(facultyId: number): Promise<ApiResponse<any[]>> {
    const response = await this.client.get('/v1/tasks/classes/by-faculty', {
      params: { faculty_id: facultyId }
    });
    return response.data;
  }

  async getStudentsByClass(classId: number): Promise<ApiResponse<any[]>> {
    const response = await this.client.get('/v1/tasks/students/by-class', {
      params: { class_id: classId }
    });
    return response.data;
  }

  async getLecturers(): Promise<ApiResponse<any[]>> {
    const response = await this.client.get('/v1/tasks/lecturers');
    return response.data;
  }
}

// Export singleton instance
export const apiClient = new ApiClient();
export default apiClient;
