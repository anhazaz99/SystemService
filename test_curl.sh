#!/bin/bash

echo "=== TEST API TẠO SINH VIÊN VỚI JWT TOKEN ===\n"

BASE_URL="http://localhost:8000"

echo "1. Đăng nhập để lấy JWT token..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "admin",
    "password": "123456",
    "user_type": "lecturer"
  }')

echo "Login Response: $LOGIN_RESPONSE"

# Extract token from response (simple way)
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ -n "$TOKEN" ]; then
    echo "\n✅ Token nhận được: ${TOKEN:0:50}...\n"
    
    echo "2. Test tạo sinh viên với JWT token..."
    CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/students" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -H "Authorization: Bearer $TOKEN" \
      -d '{
        "full_name": "Nguyễn Văn A",
        "birth_date": "2000-01-01",
        "gender": "male",
        "address": "Hà Nội",
        "email": "nguyenvana@test.com",
        "phone": "0123456789",
        "student_code": "SV001",
        "class_id": 1
      }')
    
    echo "Create Student Response: $CREATE_RESPONSE\n"
    
    echo "3. Test lấy danh sách sinh viên..."
    LIST_RESPONSE=$(curl -s -X GET "$BASE_URL/api/auth/students" \
      -H "Accept: application/json" \
      -H "Authorization: Bearer $TOKEN")
    
    echo "List Students Response: $LIST_RESPONSE\n"
    
else
    echo "\n❌ Không nhận được token từ response"
    echo "Response: $LOGIN_RESPONSE"
fi

echo "\n=== KẾT THÚC TEST ==="
