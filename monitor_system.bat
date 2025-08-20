@echo off
chcp 65001 >nul
title System Service - Monitor

echo.
echo ========================================
echo    📊 SYSTEM SERVICE - MONITOR
echo ========================================
echo.

:menu
echo 📋 CHỌN TÙY CHỌN MONITOR:
echo.
echo [1] 🔍 Kiểm tra tình trạng hệ thống
echo [2] 📊 Xem queue status
echo [3] ❌ Xem failed jobs
echo [4] 🗄️  Kiểm tra database
echo [5] 🌐 Test API endpoints
echo [6] 📝 Xem logs
echo [7] 🔄 Refresh
echo [8] ❌ Thoát
echo.
set /p choice="Nhập lựa chọn (1-8): "

if "%choice%"=="1" goto status
if "%choice%"=="2" goto queue
if "%choice%"=="3" goto failed
if "%choice%"=="4" goto database
if "%choice%"=="5" goto api
if "%choice%"=="6" goto logs
if "%choice%"=="7" goto refresh
if "%choice%"=="8" goto exit
goto menu

:status
cls
echo.
echo ========================================
echo    🔍 TÌNH TRẠNG HỆ THỐNG
echo ========================================
echo.

echo [1/4] 🔍 Kiểm tra PHP processes...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    ✅ PHP processes đang chạy
    tasklist /FI "IMAGENAME eq php.exe" /FO TABLE
) else (
    echo    ❌ Không có PHP processes nào đang chạy
)

echo.
echo [2/4] 🔍 Kiểm tra Node.js processes...
tasklist /FI "IMAGENAME eq node.exe" 2>NUL | find /I /N "node.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    ✅ Node.js processes đang chạy
    tasklist /FI "IMAGENAME eq node.exe" /FO TABLE
) else (
    echo    ❌ Không có Node.js processes nào đang chạy
)

echo.
echo [3/4] 🔍 Kiểm tra ports...
netstat -an | findstr ":8000" >nul
if "%ERRORLEVEL%"=="0" (
    echo    ✅ Port 8000 (Backend) đang được sử dụng
) else (
    echo    ❌ Port 8000 (Backend) không hoạt động
)

netstat -an | findstr ":3000" >nul
if "%ERRORLEVEL%"=="0" (
    echo    ✅ Port 3000 (Frontend) đang được sử dụng
) else (
    echo    ❌ Port 3000 (Frontend) không hoạt động
)

echo.
echo [4/4] 🔍 Kiểm tra MySQL...
sc query mysql | findstr "RUNNING" >nul
if "%ERRORLEVEL%"=="0" (
    echo    ✅ MySQL service đang chạy
) else (
    echo    ❌ MySQL service không chạy
)

echo.
pause
goto menu

:queue
cls
echo.
echo ========================================
echo    📊 QUEUE STATUS
echo ========================================
echo.

echo [1/3] 📊 Kiểm tra jobs trong queue...
php artisan queue:monitor 2>nul
if "%ERRORLEVEL%"=="1" (
    echo    ❌ Không thể kiểm tra queue status
)

echo.
echo [2/3] 📋 Số lượng jobs trong từng queue...
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=system_services', 'root', ''); \$stmt = \$pdo->query('SELECT queue, COUNT(*) as count FROM jobs GROUP BY queue'); echo 'Queue Status:\n'; while (\$row = \$stmt->fetch()) { echo '  - ' . \$row['queue'] . ': ' . \$row['count'] . ' jobs\n'; } } catch (Exception \$e) { echo 'Không thể kết nối database\n'; }"

echo.
echo [3/3] ⏱️  Queue workers status...
php artisan queue:work --once --queue=high >nul 2>&1
if "%ERRORLEVEL%"=="0" (
    echo    ✅ Queue workers hoạt động bình thường
) else (
    echo    ❌ Queue workers có vấn đề
)

echo.
pause
goto menu

:failed
cls
echo.
echo ========================================
echo    ❌ FAILED JOBS
echo ========================================
echo.

echo [1/2] 📋 Danh sách failed jobs...
php artisan queue:failed 2>nul
if "%ERRORLEVEL%"=="1" (
    echo    ℹ️  Không có failed jobs nào
)

echo.
echo [2/2] 📊 Thống kê failed jobs...
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=system_services', 'root', ''); \$stmt = \$pdo->query('SELECT COUNT(*) as count FROM failed_jobs'); \$count = \$stmt->fetch()['count']; echo 'Tổng số failed jobs: ' . \$count . '\n'; } catch (Exception \$e) { echo 'Không thể kết nối database\n'; }"

echo.
echo 💡 Để retry failed jobs:
echo    php artisan queue:retry all
echo.
pause
goto menu

:database
cls
echo.
echo ========================================
echo    🗄️  DATABASE STATUS
echo ========================================
echo.

echo [1/3] 🔍 Kiểm tra database connection...
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=system_services', 'root', ''); echo '✅ Database connection thành công\n'; } catch (PDOException \$e) { echo '❌ Database connection thất bại: ' . \$e->getMessage() . '\n'; }"

echo.
echo [2/3] 📊 Thống kê database...
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=system_services', 'root', ''); \$tables = ['tasks', 'task_receivers', 'jobs', 'failed_jobs']; echo 'Database Statistics:\n'; foreach (\$tables as \$table) { try { \$stmt = \$pdo->query('SELECT COUNT(*) as count FROM ' . \$table); \$count = \$stmt->fetch()['count']; echo '  - ' . \$table . ': ' . \$count . ' records\n'; } catch (Exception \$e) { echo '  - ' . \$table . ': Table không tồn tại\n'; } } } catch (Exception \$e) { echo 'Không thể kết nối database\n'; }"

echo.
echo [3/3] 🔄 Kiểm tra migrations...
php artisan migrate:status 2>nul
if "%ERRORLEVEL%"=="1" (
    echo    ❌ Có lỗi với migrations
)

echo.
pause
goto menu

:api
cls
echo.
echo ========================================
echo    🌐 API ENDPOINTS TEST
echo ========================================
echo.

echo [1/3] 🔍 Test Backend API...
curl -s http://localhost:8000/api/v1/tasks >nul 2>&1
if "%ERRORLEVEL%"=="0" (
    echo    ✅ Backend API (Port 8000) hoạt động
) else (
    echo    ❌ Backend API (Port 8000) không phản hồi
)

echo.
echo [2/3] 🔍 Test Frontend...
curl -s http://localhost:3000 >nul 2>&1
if "%ERRORLEVEL%"=="0" (
    echo    ✅ Frontend (Port 3000) hoạt động
) else (
    echo    ❌ Frontend (Port 3000) không phản hồi
)

echo.
echo [3/3] 🔍 Test Database API...
curl -s "http://localhost:8000/api/v1/tasks/faculties" >nul 2>&1
if "%ERRORLEVEL%"=="0" (
    echo    ✅ Database API hoạt động
) else (
    echo    ❌ Database API có vấn đề
)

echo.
echo 💡 Để test chi tiết hơn:
echo    curl http://localhost:8000/api/v1/tasks
echo    curl http://localhost:3000
echo.
pause
goto menu

:logs
cls
echo.
echo ========================================
echo    📝 SYSTEM LOGS
echo ========================================
echo.

echo [1/3] 📋 Laravel logs (10 dòng cuối)...
if exist "storage\logs\laravel.log" (
    echo    📄 Laravel log file:
    powershell "Get-Content 'storage\logs\laravel.log' | Select-Object -Last 10"
) else (
    echo    ℹ️  Không có Laravel log file
)

echo.
echo [2/3] 📋 Queue logs...
if exist "storage\logs\laravel.log" (
    echo    📄 Queue related logs:
    powershell "Get-Content 'storage\logs\laravel.log' | Select-String 'Queue|Job' | Select-Object -Last 5"
) else (
    echo    ℹ️  Không có queue logs
)

echo.
echo [3/3] 📋 Error logs...
if exist "storage\logs\laravel.log" (
    echo    📄 Error logs:
    powershell "Get-Content 'storage\logs\laravel.log' | Select-String 'ERROR|Exception' | Select-Object -Last 5"
) else (
    echo    ℹ️  Không có error logs
)

echo.
pause
goto menu

:refresh
cls
goto menu

:exit
echo.
echo 👋 Tạm biệt!
timeout /t 2 /nobreak >nul
exit
