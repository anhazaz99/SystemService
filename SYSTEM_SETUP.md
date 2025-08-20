# 🚀 System Service - Setup & Management

## 📋 Tổng quan

Hệ thống bao gồm 3 file batch để quản lý toàn bộ development environment:

- **`start_system.bat`** - Khởi động toàn bộ hệ thống
- **`stop_system.bat`** - Dừng toàn bộ hệ thống  
- **`monitor_system.bat`** - Monitor và kiểm tra hệ thống

## 🚀 Khởi động hệ thống

### **Chạy file: `start_system.bat`**

File này sẽ tự động:

1. **🔍 Kiểm tra và dừng processes cũ**
   - Dừng PHP processes đang chạy
   - Dừng Node.js processes đang chạy

2. **🗄️ Kiểm tra MySQL**
   - Kiểm tra MySQL service
   - Khởi động MySQL nếu cần

3. **🔍 Kiểm tra database**
   - Test connection đến `system_services` database
   - Báo lỗi nếu không kết nối được

4. **🔄 Chạy migrations**
   - Chạy `php artisan migrate --force`
   - Cập nhật database schema

5. **🚀 Khởi động Backend Server**
   - Chạy Laravel server trên port 8000
   - Mở cửa sổ riêng cho backend

6. **🔄 Khởi động Queue Workers**
   - Chạy `php artisan task:queues --daemon`
   - Xử lý background jobs

7. **📦 Kiểm tra Frontend dependencies**
   - Cài đặt `node_modules` nếu chưa có
   - Chuẩn bị cho frontend

8. **🎨 Khởi động Frontend Server**
   - Chạy Next.js server trên port 3000
   - Mở cửa sổ riêng cho frontend

9. **🌐 Mở browser**
   - Tự động mở `http://localhost:3000`

## 🛑 Dừng hệ thống

### **Chạy file: `stop_system.bat`**

File này sẽ:

1. **⏹️ Dừng tất cả processes**
   - Dừng PHP processes
   - Dừng Node.js processes
   - Đóng các cửa sổ cmd liên quan

2. **🧹 Dọn dẹp**
   - Xóa cache files
   - Xóa log files

## 📊 Monitor hệ thống

### **Chạy file: `monitor_system.bat`**

Menu với 8 tùy chọn:

#### **[1] 🔍 Kiểm tra tình trạng hệ thống**
- Hiển thị PHP processes đang chạy
- Hiển thị Node.js processes đang chạy
- Kiểm tra ports 8000 và 3000
- Kiểm tra MySQL service

#### **[2] 📊 Xem queue status**
- Hiển thị queue monitor
- Số lượng jobs trong từng queue
- Kiểm tra queue workers

#### **[3] ❌ Xem failed jobs**
- Danh sách failed jobs
- Thống kê failed jobs
- Hướng dẫn retry

#### **[4] 🗄️ Kiểm tra database**
- Test database connection
- Thống kê các bảng
- Kiểm tra migrations

#### **[5] 🌐 Test API endpoints**
- Test Backend API (port 8000)
- Test Frontend (port 3000)
- Test Database API

#### **[6] 📝 Xem logs**
- Laravel logs (10 dòng cuối)
- Queue related logs
- Error logs

#### **[7] 🔄 Refresh**
- Làm mới menu

#### **[8] ❌ Thoát**
- Thoát khỏi monitor

## 🔧 Cấu hình hệ thống

### **Yêu cầu hệ thống:**
- Windows 10/11
- PHP 8.0+
- Node.js 16+
- MySQL 5.7+
- Composer
- npm/yarn

### **Database configuration:**
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=system_services
DB_USERNAME=root
DB_PASSWORD=
```

### **Queue configuration:**
```env
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database-uuids
```

## 🚀 Cách sử dụng

### **Khởi động lần đầu:**
```bash
# 1. Đảm bảo MySQL đang chạy
# 2. Tạo database 'system_services'
# 3. Chạy file batch
start_system.bat
```

### **Khởi động hàng ngày:**
```bash
# Chỉ cần chạy
start_system.bat
```

### **Dừng hệ thống:**
```bash
# Dừng tất cả
stop_system.bat
```

### **Monitor hệ thống:**
```bash
# Kiểm tra tình trạng
monitor_system.bat
```

## 📊 Các ports được sử dụng

| Service | Port | URL | Mô tả |
|---------|------|-----|-------|
| Backend | 8000 | http://localhost:8000 | Laravel API |
| Frontend | 3000 | http://localhost:3000 | Next.js App |
| MySQL | 3306 | localhost:3306 | Database |

## 🔄 Queue System

### **Các queues:**
- **`high`** - Priority tasks (90s timeout)
- **`files`** - File processing (300s timeout)
- **`reports`** - Report generation (600s timeout)
- **`sync`** - Data synchronization (300s timeout)
- **`cleanup`** - Cleanup operations (900s timeout)

### **Monitor queues:**
```bash
# Xem queue status
php artisan queue:monitor

# Xem failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## 🛠️ Troubleshooting

### **Lỗi thường gặp:**

1. **MySQL không kết nối được**
   ```bash
   # Kiểm tra MySQL service
   sc query mysql
   
   # Khởi động MySQL
   net start mysql
   ```

2. **Port đã được sử dụng**
   ```bash
   # Kiểm tra ports
   netstat -an | findstr ":8000"
   netstat -an | findstr ":3000"
   
   # Dừng processes
   stop_system.bat
   ```

3. **Queue workers không chạy**
   ```bash
   # Kiểm tra queue status
   monitor_system.bat
   
   # Restart queue workers
   php artisan queue:restart
   ```

4. **Frontend dependencies lỗi**
   ```bash
   # Cài đặt lại dependencies
   cd frontend
   npm install
   ```

### **Log files:**
- **Laravel logs**: `storage/logs/laravel.log`
- **Queue logs**: Trong Laravel logs
- **Frontend logs**: Trong terminal frontend

## 📈 Performance Tips

1. **Queue Management**
   - Monitor queue performance thường xuyên
   - Retry failed jobs kịp thời
   - Clear old jobs định kỳ

2. **Database Optimization**
   - Index các cột thường query
   - Optimize queries
   - Monitor slow queries

3. **System Resources**
   - Monitor CPU và RAM usage
   - Restart services định kỳ
   - Clear cache files

## 🔐 Security

1. **Environment Variables**
   - Không commit `.env` file
   - Sử dụng strong passwords
   - Restrict database access

2. **API Security**
   - Validate tất cả inputs
   - Use JWT authentication
   - Implement rate limiting

3. **File Permissions**
   - Restrict file access
   - Secure upload directories
   - Validate file types

## 📞 Support

Nếu gặp vấn đề:

1. Chạy `monitor_system.bat` để kiểm tra
2. Xem logs trong `storage/logs/`
3. Kiểm tra database connection
4. Restart hệ thống với `stop_system.bat` và `start_system.bat`

---

**🎉 Chúc bạn coding vui vẻ!**
