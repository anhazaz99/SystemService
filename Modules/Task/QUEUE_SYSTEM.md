# 🚀 Task Module Queue System

## 📋 Tổng quan

Task module sử dụng Laravel Queue system để xử lý các tác vụ nặng và background processing, giúp cải thiện performance và user experience.

## 🔧 Các Queue Types

### 1. **High Priority Queue** (`high`)
- **Mục đích**: Xử lý các tác vụ quan trọng và khẩn cấp
- **Jobs**: 
  - `ProcessTaskJob` (task_created, task_updated, status_updated)
- **Timeout**: 90 giây
- **Retry**: 3 lần

### 2. **Files Processing Queue** (`files`)
- **Mục đích**: Xử lý upload, validation, compression files
- **Jobs**: 
  - `ProcessTaskFileJob` (upload_processing, file_validation, file_compression)
- **Timeout**: 300 giây
- **Retry**: 3 lần

### 3. **Reports Queue** (`reports`)
- **Mục đích**: Tạo báo cáo và gửi email
- **Jobs**: 
  - `GenerateTaskReportJob` (daily, weekly, monthly, custom reports)
- **Timeout**: 600 giây
- **Retry**: 3 lần

### 4. **Sync Queue** (`sync`)
- **Mục đích**: Đồng bộ dữ liệu với external systems
- **Jobs**: 
  - `SyncTaskDataJob` (database, external_api, calendar sync)
- **Timeout**: 300 giây
- **Retry**: 3 lần

### 5. **Cleanup Queue** (`cleanup`)
- **Mục đích**: Dọn dẹp dữ liệu và files
- **Jobs**: 
  - `ProcessTaskJob` (task_deleted, cleanup operations)
- **Timeout**: 900 giây
- **Retry**: 3 lần

## 🚀 Cách sử dụng

### 1. **Khởi động Queue Workers**

```bash
# Chạy tất cả queue workers
php artisan task:queues

# Chạy ở chế độ daemon (background)
php artisan task:queues --daemon

# Chạy từng queue riêng lẻ
php artisan queue:work database --queue=high
php artisan queue:work database --queue=files
php artisan queue:work database --queue=reports
php artisan queue:work database --queue=sync
php artisan queue:work database --queue=cleanup
```

### 2. **API Endpoints**

#### **Generate Report**
```http
POST /api/v1/tasks/generate-report
Content-Type: application/json

{
    "type": "daily",
    "params": {
        "date": "2024-01-15"
    },
    "recipients": ["admin@example.com"]
}
```

#### **Sync Data**
```http
POST /api/v1/tasks/sync-data
Content-Type: application/json

{
    "type": "database",
    "params": {
        "tables": ["tasks", "task_receivers"]
    }
}
```

#### **Process Task Files**
```http
POST /api/v1/tasks/{task_id}/process-files
Content-Type: multipart/form-data

files[]: [file1.pdf, file2.docx]
```

### 3. **Monitoring Queue**

```bash
# Xem danh sách jobs trong queue
php artisan queue:monitor

# Xem failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

## 📊 Job Processing Flow

### **Task Creation Flow**
```
1. User tạo task → TaskController::store()
2. Dispatch ProcessTaskJob → high queue
3. ProcessTaskJob xử lý:
   - Validate task data
   - Send notifications
   - Update statistics
   - Generate reports (nếu cần)
```

### **File Upload Flow**
```
1. User upload files → TaskController::processTaskFiles()
2. Dispatch ProcessTaskFileJob → files queue
3. ProcessTaskFileJob xử lý:
   - Validate file format
   - Compress files
   - Convert formats
   - Store metadata
```

### **Report Generation Flow**
```
1. User request report → TaskController::generateReport()
2. Dispatch GenerateTaskReportJob → reports queue
3. GenerateTaskReportJob xử lý:
   - Collect data
   - Generate report
   - Send email
   - Store report
```

## 🔍 Debugging

### **Log Files**
```bash
# Xem queue logs
tail -f storage/logs/laravel.log | grep "Queue"

# Xem specific job logs
tail -f storage/logs/laravel.log | grep "ProcessTaskJob"
```

### **Database Queries**
```sql
-- Xem jobs trong queue
SELECT * FROM jobs WHERE queue = 'high' ORDER BY created_at DESC;

-- Xem failed jobs
SELECT * FROM failed_jobs ORDER BY failed_at DESC;
```

## ⚙️ Configuration

### **Queue Configuration** (`config/queue.php`)
```php
'task_high' => [
    'driver' => 'database',
    'table' => 'jobs',
    'queue' => 'high',
    'retry_after' => 90,
    'after_commit' => false,
],
```

### **Environment Variables** (`.env`)
```env
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database-uuids
```

## 🛠️ Troubleshooting

### **Common Issues**

1. **Jobs không được xử lý**
   ```bash
   # Kiểm tra queue worker có đang chạy không
   ps aux | grep "queue:work"
   
   # Restart queue workers
   php artisan queue:restart
   ```

2. **Jobs bị fail**
   ```bash
   # Xem failed jobs
   php artisan queue:failed
   
   # Retry specific job
   php artisan queue:retry {id}
   ```

3. **Queue bị đầy**
   ```bash
   # Clear all jobs
   php artisan queue:clear --queue=high
   
   # Clear specific queue
   php artisan queue:clear --queue=files
   ```

## 📈 Performance Tips

1. **Queue Priority**: Sử dụng `high` queue cho tasks quan trọng
2. **Batch Processing**: Group multiple jobs thành batch
3. **Timeout Settings**: Điều chỉnh timeout phù hợp với job type
4. **Retry Strategy**: Cấu hình retry logic hợp lý
5. **Monitoring**: Theo dõi queue performance thường xuyên

## 🔐 Security

1. **Job Validation**: Validate tất cả input data trong jobs
2. **Access Control**: Kiểm tra permissions trước khi dispatch jobs
3. **Error Handling**: Log errors và handle exceptions properly
4. **Queue Isolation**: Tách biệt queues theo chức năng
