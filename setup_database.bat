@echo off
chcp 65001 >nul
title Database Setup

echo.
echo ========================================
echo    🗄️  DATABASE SETUP FOR LARAGON
echo ========================================
echo.

echo [1/3] 🔍 Kiểm tra MySQL connection...
php -r "try { \$pdo = new PDO('mysql:host=localhost', 'root', ''); echo '✅ MySQL connection OK\n'; } catch (Exception \$e) { echo '❌ MySQL Error: ' . \$e->getMessage() . '\n'; echo '💡 Vui lòng Start MySQL trong Laragon\n'; exit(1); }"
if "%ERRORLEVEL%"=="1" (
    pause
    exit /b 1
)

echo.
echo [2/3] 🔍 Kiểm tra database system_services...
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=system_services', 'root', ''); echo '✅ Database system_services đã tồn tại\n'; } catch (Exception \$e) { echo '📝 Database chưa tồn tại, đang tạo...\n'; try { \$pdo = new PDO('mysql:host=localhost', 'root', ''); \$pdo->exec('CREATE DATABASE system_services'); echo '✅ Database system_services đã được tạo\n'; } catch (Exception \$e2) { echo '❌ Không thể tạo database: ' . \$e2->getMessage() . '\n'; exit(1); } }"
if "%ERRORLEVEL%"=="1" (
    pause
    exit /b 1
)

echo.
echo [3/3] 🔄 Chạy migrations...
php artisan migrate --force
if "%ERRORLEVEL%"=="0" (
    echo    ✅ Migrations đã được chạy thành công
) else (
    echo    ⚠️  Có lỗi khi chạy migrations
)

echo.
echo ========================================
echo    ✅ DATABASE SETUP HOÀN TẤT!
echo ========================================
echo.
echo 💡 Bây giờ bạn có thể chạy:
echo    simple_start.bat
echo.
pause
