# Test API tạo sinh viên với JWT token
# Sử dụng: .\test_api.ps1

Write-Host "=== TEST API TẠO SINH VIÊN VỚI JWT TOKEN ===" -ForegroundColor Green
Write-Host ""

$BaseUrl = "http://localhost:8000"

# 1. Test đăng nhập để lấy token
Write-Host "1. Đăng nhập để lấy JWT token..." -ForegroundColor Yellow

$LoginData = @{
    username = "admin"
    password = "123456"
    user_type = "lecturer"
} | ConvertTo-Json

try {
    $LoginResponse = Invoke-RestMethod -Uri "$BaseUrl/api/auth/login" -Method POST -Body $LoginData -ContentType "application/json"
    Write-Host "Login Response: $($LoginResponse | ConvertTo-Json -Depth 3)" -ForegroundColor Cyan
    
    if ($LoginResponse.data.token) {
        $Token = $LoginResponse.data.token
        Write-Host "✅ Token nhận được: $($Token.Substring(0, [Math]::Min(50, $Token.Length)))..." -ForegroundColor Green
        Write-Host ""
        
        # 2. Test tạo sinh viên với token
        Write-Host "2. Test tạo sinh viên với JWT token..." -ForegroundColor Yellow
        
        $StudentData = @{
            full_name = "Nguyễn Văn A"
            birth_date = "2000-01-01"
            gender = "male"
            address = "Hà Nội"
            email = "nguyenvana@test.com"
            phone = "0123456789"
            student_code = "SV001"
            class_id = 1
        } | ConvertTo-Json
        
        $Headers = @{
            "Authorization" = "Bearer $Token"
            "Content-Type" = "application/json"
        }
        
        $CreateResponse = Invoke-RestMethod -Uri "$BaseUrl/api/auth/students" -Method POST -Body $StudentData -Headers $Headers
        Write-Host "Create Student Response: $($CreateResponse | ConvertTo-Json -Depth 3)" -ForegroundColor Cyan
        Write-Host ""
        
        # 3. Test lấy danh sách sinh viên
        Write-Host "3. Test lấy danh sách sinh viên..." -ForegroundColor Yellow
        
        $ListResponse = Invoke-RestMethod -Uri "$BaseUrl/api/auth/students" -Method GET -Headers $Headers
        Write-Host "List Students Response: $($ListResponse | ConvertTo-Json -Depth 3)" -ForegroundColor Cyan
        Write-Host ""
        
    } else {
        Write-Host "❌ Không nhận được token từ response" -ForegroundColor Red
        Write-Host "Response: $($LoginResponse | ConvertTo-Json -Depth 3)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ Lỗi khi gọi API: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Response: $($_.Exception.Response)" -ForegroundColor Red
}

Write-Host "=== KẾT THÚC TEST ===" -ForegroundColor Green
