<?php

/**
 * Test Notification System
 * 
 * Chạy file này để test notification:
 * php test_notification.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Notification System...\n\n";

try {
    // Test 1: Kiểm tra notification service có tồn tại không
    echo "1️⃣ Checking Notification Service...\n";
    
    if (class_exists('\Modules\Notifications\app\Services\NotificationService\NotificationService')) {
        echo "✅ Notification Service exists\n";
        
        $notificationService = app('\Modules\Notifications\app\Services\NotificationService\NotificationService');
        echo "✅ Notification Service instantiated\n";
        
        // Test 2: Gửi test notification
        echo "\n2️⃣ Sending Test Notification...\n";
        
        $result = $notificationService->sendNotification(
            'user_registered',
            [['user_id' => 999, 'user_type' => 'test']],
            [
                'user_name' => 'Test User',
                'username' => 'testuser123',
                'password' => 'testpass123',
                'user_email' => 'test@example.com'
            ]
        );
        
        echo "✅ Test notification sent successfully\n";
        echo "📧 Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
        
    } else {
        echo "❌ Notification Service not found\n";
        echo "💡 Make sure you've run: php artisan module:seed Notifications\n";
    }
    
    // Test 3: Kiểm tra templates
    echo "\n3️⃣ Checking Notification Templates...\n";
    
    $templates = \Illuminate\Support\Facades\DB::table('notification_templates')->get();
    echo "✅ Found " . $templates->count() . " templates\n";
    
    foreach ($templates as $template) {
        echo "   - {$template->name}: {$template->title}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🎯 Test completed!\n";

