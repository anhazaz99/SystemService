# 🚀 System Service - Laragon Setup

## 📋 Yêu cầu

- **Laragon** đã được cài đặt
- **PHP 8.0+** (có sẵn trong Laragon)
- **Node.js 16+** (cài đặt riêng)
- **Composer** (có sẵn trong Laragon)

## 🚀 Cách sử dụng

### **Bước 1: Khởi động Laragon**
1. Mở **Laragon**
2. Click **"Start All"** hoặc chỉ **Start MySQL**
3. Đảm bảo MySQL đang chạy (port 3306)

### **Bước 2: Setup Database**
```bash
# Chạy file setup database
setup_database.bat
```

File này sẽ:
- ✅ Kiểm tra MySQL connection
- ✅ Tạo database `system_services` nếu chưa có
- ✅ Chạy Laravel migrations

### **Bước 3: Khởi động hệ thống**
```bash
# Chạy file khởi động đơn giản
simple_start.bat
```

File này sẽ:
- ✅ Kiểm tra database connection
- ✅ Khởi động Backend (Port 8000)
- ✅ Khởi động Frontend (Port 3000)
- ✅ Mở browser tự động

## 📊 Các file batch có sẵn

| File | Mô tả |
|------|-------|
| `setup_database.bat` | Setup database cho Laragon |
| `simple_start.bat` | Khởi động hệ thống đơn giản |
| `start_system.bat` | Khởi động đầy đủ (có queue workers) |
| `stop_system.bat` | Dừng tất cả services |
| `monitor_system.bat` | Monitor hệ thống |

## 🛠️ Troubleshooting

### **Lỗi MySQL không kết nối được**
1. Kiểm tra Laragon đang chạy
2. Click **"Start All"** trong Laragon
3. Kiểm tra MySQL đã Start (màu xanh)

### **Lỗi Database không tồn tại**
1. Chạy `setup_database.bat`
2. Hoặc tạo thủ công trong phpMyAdmin:
   - Mở http://localhost/phpmyadmin
   - Tạo database `system_services`

### **Lỗi Port đã được sử dụng**
1. Chạy `stop_system.bat`
2. Hoặc đóng các cửa sổ cmd đang chạy
3. Chạy lại `simple_start.bat`

## 🌐 Truy cập hệ thống

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000
- **phpMyAdmin**: http://localhost/phpmyadmin

## 💡 Tips

1. **Lần đầu sử dụng**: Chạy `setup_database.bat` trước
2. **Hàng ngày**: Chỉ cần chạy `simple_start.bat`
3. **Dừng hệ thống**: Đóng các cửa sổ cmd hoặc chạy `stop_system.bat`
4. **Monitor**: Chạy `monitor_system.bat` để kiểm tra tình trạng

---

**🎉 Chúc bạn coding vui vẻ với Laragon!**
