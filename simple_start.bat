@echo off
chcp 65001 >nul
title System Service - Simple Start

echo.
echo ========================================
echo    🚀 SYSTEM SERVICE - SIMPLE START
echo ========================================
echo.

echo [1/5] 🔍 Kiểm tra Laragon MySQL...
echo    ℹ️  Vui lòng đảm bảo Laragon đang chạy và MySQL đã Start
echo    💡 Nếu chưa: Mở Laragon → Start All
pause

echo.
echo [2/5] 🔍 Test database connection...
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=system_services', 'root', ''); echo '✅ Database OK\n'; } catch (Exception \$e) { echo '❌ Database Error: ' . \$e->getMessage() . '\n'; exit(1); }"
if "%ERRORLEVEL%"=="1" (
    echo    ❌ Database lỗi - Vui lòng kiểm tra Laragon MySQL
    pause
    exit /b 1
)

echo.
echo [3/5] 🚀 Khởi động Backend (Port 8000)...
start "Backend" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"

echo.
echo [4/5] 🔄 Khởi động Queue Workers...
start "Queue Workers" cmd /k "echo 🔄 QUEUE WORKERS && echo Processing background jobs... && echo. && php artisan task:queues --daemon"

echo.
echo [5/5] 🎨 Khởi động Frontend (Port 3000)...
start "Frontend" cmd /k "cd frontend && npm run dev"

echo.
echo ========================================
echo    ✅ HỆ THỐNG ĐÃ KHỞI ĐỘNG!
echo ========================================
echo.
echo 🌐 Frontend: http://localhost:3000
echo 🔧 Backend: http://localhost:8000
echo 📊 Queue Workers: Đang chạy (background)
echo.
echo 💡 Để dừng: Đóng các cửa sổ cmd
echo.
timeout /t 3 /nobreak >nul
start http://localhost:3000
