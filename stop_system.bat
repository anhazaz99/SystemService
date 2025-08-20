@echo off
chcp 65001 >nul
title System Service - Stop All Services

echo.
echo ========================================
echo    🛑 SYSTEM SERVICE - STOP ALL
echo ========================================
echo.

echo [1/4] 🔍 Đang tìm và dừng PHP processes...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    ⏹️  Dừng PHP processes...
    taskkill /F /IM php.exe >nul 2>&1
    echo    ✅ PHP processes đã được dừng
) else (
    echo    ℹ️  Không có PHP processes nào đang chạy
)

echo [2/4] 🔍 Đang tìm và dừng Node.js processes...
tasklist /FI "IMAGENAME eq node.exe" 2>NUL | find /I /N "node.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    ⏹️  Dừng Node.js processes...
    taskkill /F /IM node.exe >nul 2>&1
    echo    ✅ Node.js processes đã được dừng
) else (
    echo    ℹ️  Không có Node.js processes nào đang chạy
)

echo [3/4] 🔍 Đang tìm và dừng cmd windows liên quan...
tasklist /FI "WINDOWTITLE eq Backend Server*" 2>NUL | find /I /N "cmd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    ⏹️  Dừng Backend Server window...
    taskkill /F /FI "WINDOWTITLE eq Backend Server*" >nul 2>&1
)

tasklist /FI "WINDOWTITLE eq Frontend Server*" 2>NUL | find /I /N "cmd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    ⏹️  Dừng Frontend Server window...
    taskkill /F /FI "WINDOWTITLE eq Frontend Server*" >nul 2>&1
)

tasklist /FI "WINDOWTITLE eq Queue Workers*" 2>NUL | find /I /N "cmd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    ⏹️  Dừng Queue Workers window...
    taskkill /F /FI "WINDOWTITLE eq Queue Workers*" >nul 2>&1
)

echo [4/4] 🧹 Dọn dẹp cache và temp files...
if exist "storage\framework\cache" (
    rmdir /s /q "storage\framework\cache" >nul 2>&1
    echo    ✅ Cache đã được dọn dẹp
)

if exist "storage\logs\*.log" (
    del /q "storage\logs\*.log" >nul 2>&1
    echo    ✅ Log files đã được dọn dẹp
)

echo.
echo ========================================
echo    ✅ TẤT CẢ DỊCH VỤ ĐÃ ĐƯỢC DỪNG!
echo ========================================
echo.
echo 🛑 Đã dừng:
echo    - Backend Server (Port 8000)
echo    - Frontend Server (Port 3000)
echo    - Queue Workers
echo    - PHP Processes
echo    - Node.js Processes
echo.
echo 🧹 Đã dọn dẹp:
echo    - Cache files
echo    - Log files
echo.
echo 💡 Để khởi động lại hệ thống:
echo    Chạy: start_system.bat
echo.
echo ========================================
echo    👋 Tạm biệt!
echo ========================================
echo.

timeout /t 3 /nobreak >nul
