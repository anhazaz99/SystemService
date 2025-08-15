<?php

// Test API tạo sinh viên với JWT token
// Sử dụng: php test_api.php

echo "=== TEST API TẠO SINH VIÊN ===\n\n";

// 1. Test đăng nhập để lấy token
echo "1. Đăng nhập để lấy JWT token...\n";

$loginData = [
    'username' => 'admin', // Tài khoản admin từ seeder
    'password' => '123456', // Password từ seeder
    'user_type' => 'lecturer' // Admin là lecturer
];

$loginResponse = makeRequest('POST', '/api/auth/login', $loginData);
echo "Response: " . $loginResponse . "\n\n";

if ($loginResponse) {
    $loginData = json_decode($loginResponse, true);
    
    if (isset($loginData['data']['token'])) {
        $token = $loginData['data']['token'];
        echo "✅ Token nhận được: " . substr($token, 0, 50) . "...\n\n";
        
        // 2. Test tạo sinh viên với token
        echo "2. Test tạo sinh viên với JWT token...\n";
        
        $studentData = [
            'full_name' => 'Nguyễn Văn B',
            'birth_date' => '2000-02-02',
            'gender' => 'female',
            'address' => 'TP.HCM',
            'email' => 'nguyenvanb@test.com',
            'phone' => '0123456788',
            'student_code' => 'SV002',
            'class_id' => 1 // ID lớp từ seeder
        ];
        
        $createResponse = makeRequest('POST', '/api/auth/students', $studentData, $token);
        echo "Response tạo sinh viên: " . $createResponse . "\n\n";
        
        // 3. Test lấy danh sách sinh viên
        echo "3. Test lấy danh sách sinh viên...\n";
        
        $listResponse = makeRequest('GET', '/api/auth/students', [], $token);
        echo "Response danh sách: " . $listResponse . "\n\n";
        
        // 4. Test đăng nhập sinh viên
        echo "4. Test đăng nhập sinh viên...\n";
        
        $studentLoginData = [
            'username' => 'sv_sv001', // Tài khoản sinh viên từ seeder
            'password' => '123456',
            'user_type' => 'student'
        ];
        
        $studentLoginResponse = makeRequest('POST', '/api/auth/login', $studentLoginData);
        echo "Response đăng nhập sinh viên: " . $studentLoginResponse . "\n\n";
        
    } else {
        echo "❌ Không nhận được token từ response\n";
        echo "Response: " . $loginResponse . "\n";
    }
} else {
    echo "❌ Không thể kết nối đến API\n";
}

function makeRequest($method, $endpoint, $data = [], $token = null) {
    $baseUrl = 'http://localhost:8000'; // Thay đổi port nếu cần
    
    $url = $baseUrl . $endpoint;
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        echo "CURL Error: " . curl_error($ch) . "\n";
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    echo "HTTP Code: " . $httpCode . "\n";
    echo "URL: " . $url . "\n";
    
    return $response;
}

echo "\n=== KẾT THÚC TEST ===\n";
