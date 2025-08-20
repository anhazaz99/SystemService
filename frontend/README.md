# Task Management Frontend

Frontend Next.js cho hệ thống quản lý task với Clean Architecture backend.

## 🚀 Tính năng

### ✅ Hoàn thành
- **Authentication System**: Đăng nhập JWT với phân quyền lecturer/student
- **Dashboard**: Tổng quan về tasks và thống kê
- **Task Management**: CRUD operations đầy đủ cho tasks
- **My Tasks**: Quản lý tasks được assign cho user
- **Statistics**: Thống kê chi tiết về tasks (my tasks, created tasks, system overview)
- **Responsive Design**: Giao diện responsive với Tailwind CSS
- **API Client**: Client hoàn chỉnh để tương tác với backend Clean Architecture
- **Type Safety**: TypeScript cho type safety
- **Error Handling**: Xử lý lỗi toàn diện với toast notifications

### 🔄 Test API Coverage
Frontend này test đầy đủ tất cả API endpoints của backend:

**Authentication APIs:**
- ✅ `/login/lecturer` - Đăng nhập JWT
- ✅ `/logout` - Đăng xuất

**Task APIs:**
- ✅ `/v1/tasks` - Lấy danh sách tasks (GET)
- ✅ `/v1/tasks` - Tạo task mới (POST)
- ✅ `/v1/tasks/{id}` - Lấy chi tiết task (GET)
- ✅ `/v1/tasks/{id}` - Cập nhật task (PUT)
- ✅ `/v1/tasks/{id}` - Xóa task (DELETE)
- ✅ `/v1/tasks/my-tasks` - Tasks của user hiện tại
- ✅ `/v1/tasks/created` - Tasks do user tạo
- ✅ `/v1/tasks/{id}/status` - Cập nhật status (PATCH)
- ✅ `/v1/tasks/{id}/assign` - Assign task (POST)
- ✅ `/v1/tasks/{id}/revoke` - Revoke task (POST)
- ✅ `/v1/tasks/{id}/restore` - Khôi phục task (POST)
- ✅ `/v1/tasks/{id}/force` - Xóa vĩnh viễn (DELETE)

**Statistics APIs:**
- ✅ `/v1/tasks/statistics/my` - Thống kê tasks của user
- ✅ `/v1/tasks/statistics/created` - Thống kê tasks đã tạo
- ✅ `/v1/tasks/statistics/overview` - Tổng quan hệ thống

**Calendar APIs:**
- ✅ `/v1/calendar/events` - Events calendar
- ✅ `/v1/calendar/events/upcoming` - Sự kiện sắp tới
- ✅ `/v1/calendar/events/today` - Sự kiện hôm nay

**File APIs:**
- ✅ `/v1/tasks/{id}/files` - Upload files (POST)
- ✅ `/v1/tasks/{id}/files/{fileId}` - Xóa file (DELETE)

## 🛠️ Cài đặt

### Yêu cầu
- Node.js 18+
- npm hoặc yarn
- Backend Laravel đang chạy tại `http://localhost:8000`

### Bước 1: Cài đặt dependencies
```bash
cd frontend
npm install
```

### Bước 2: Chạy development server
```bash
npm run dev
```

Frontend sẽ chạy tại: `http://localhost:3000`

## 📁 Cấu trúc Project

```
frontend/
├── components/           # React components
│   ├── Layout.tsx       # Layout chính với sidebar
│   ├── TaskCard.tsx     # Component hiển thị task
│   └── TaskForm.tsx     # Form tạo/sửa task
├── contexts/            # React contexts
│   └── AuthContext.tsx  # Authentication context
├── lib/                 # Utilities
│   └── api.ts          # API client
├── pages/              # Next.js pages
│   ├── _app.tsx        # App wrapper
│   ├── index.tsx       # Dashboard
│   ├── login.tsx       # Trang đăng nhập
│   ├── statistics.tsx  # Trang thống kê
│   └── tasks/          # Task pages
├── styles/             # CSS styles
├── types/              # TypeScript types
└── README.md
```

## 🔑 Tài khoản Demo

### Giảng viên
- **Username**: `lecturer001`
- **Password**: `password123`
- **User Type**: `lecturer`

### Sinh viên
- **Username**: `student001`
- **Password**: `password123`
- **User Type**: `student`

## 🎯 Cách sử dụng

### 1. Đăng nhập
- Truy cập `http://localhost:3000/login`
- Chọn loại người dùng (Giảng viên/Sinh viên)
- Nhập username/password
- Hoặc sử dụng tài khoản demo có sẵn

### 2. Dashboard
- Xem tổng quan về tasks
- Thống kê nhanh
- Tasks gần đây
- Upcoming events
- Quick actions

### 3. Quản lý Tasks
- **My Tasks**: Xem tasks được assign
- **Created Tasks**: Tasks bạn đã tạo (lecturer)
- **All Tasks**: Tất cả tasks trong hệ thống
- **Tạo task mới**: Form đầy đủ với validation
- **Cập nhật status**: Thay đổi trạng thái task
- **Edit/Delete**: Sửa hoặc xóa tasks

### 4. Statistics
- **My Tasks Stats**: Thống kê tasks của bạn
- **Created Tasks Stats**: Tasks bạn đã tạo (lecturer)
- **System Overview**: Tổng quan hệ thống (admin)
- Biểu đồ progress bar
- Refresh real-time

### 5. Role-based Features
**Lecturer:**
- Tạo tasks cho students
- Xem thống kê created tasks
- Xem system overview
- Assign/revoke tasks

**Student:**
- Xem tasks được assign
- Cập nhật status tasks
- Xem thống kê cá nhân

## 🔧 API Integration

### API Client (`lib/api.ts`)
```typescript
// Authentication
await apiClient.login(credentials);
await apiClient.logout();

// Tasks
await apiClient.getTasks(filters);
await apiClient.createTask(data);
await apiClient.updateTask(id, data);
await apiClient.deleteTask(id);

// Statistics
await apiClient.getMyStatistics();
await apiClient.getCreatedStatistics();
```

### Error Handling
- Automatic token refresh
- Centralized error handling
- Toast notifications
- Redirect khi unauthorized

### Type Safety
```typescript
interface Task {
  id: number;
  title: string;
  status: 'pending' | 'in_progress' | 'completed' | 'overdue';
  // ... more fields
}
```

## 🎨 UI/UX Features

### Design System
- **Tailwind CSS**: Utility-first CSS framework
- **Heroicons**: Beautiful SVG icons
- **Responsive**: Mobile-first design
- **Dark mode ready**: Color scheme prepared

### Components
- **TaskCard**: Hiển thị task với actions
- **TaskForm**: Form đầy đủ với validation
- **Layout**: Sidebar navigation
- **Filters**: Search và filter tasks
- **Pagination**: Phân trang cho lists

### Notifications
- **React Hot Toast**: Toast notifications
- Success/Error/Warning messages
- Auto-dismiss timers

## 🚀 Production Build

```bash
npm run build
npm start
```

## 🔄 Testing với Backend

1. **Đảm bảo backend chạy**: `http://localhost:8000`
2. **Kiểm tra API**: Backend phải trả về JSON cho tất cả endpoints
3. **Test accounts**: Sử dụng accounts demo hoặc tạo accounts mới
4. **CORS**: Backend phải allow origin `http://localhost:3000`

## 📊 Clean Architecture Integration

Frontend này được thiết kế để test đầy đủ Clean Architecture backend:

### Layers Testing
- ✅ **Presentation Layer**: All controller endpoints
- ✅ **Application Layer**: Use Cases và Services
- ✅ **Domain Layer**: DTOs và Business Rules
- ✅ **Infrastructure Layer**: Repository patterns

### Patterns Testing
- ✅ **Dependency Injection**: Interface-based dependencies
- ✅ **Use Case Pattern**: CreateTaskUseCase testing
- ✅ **Repository Pattern**: Data access testing
- ✅ **DTO Pattern**: Type-safe data transfer
- ✅ **Custom Exceptions**: Structured error handling

## 🎉 Kết luận

Frontend này cung cấp một giao diện hoàn chỉnh để test tất cả tính năng của backend Clean Architecture, bao gồm:

- ✅ **100% API Coverage**: Test tất cả endpoints
- ✅ **Authentication**: JWT với role-based access
- ✅ **CRUD Operations**: Đầy đủ cho tasks
- ✅ **Real-time Updates**: Refresh data tức thời
- ✅ **Error Handling**: Xử lý lỗi toàn diện
- ✅ **Type Safety**: TypeScript cho development
- ✅ **Production Ready**: Build optimization

**🎊 Frontend sẵn sàng để demo và test Clean Architecture backend!**



cài thêm file env và viết 
NEXT_PUBLIC_API_BASE_URL=/api