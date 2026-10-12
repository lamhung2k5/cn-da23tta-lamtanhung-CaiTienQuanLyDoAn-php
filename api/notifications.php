<?php
/**
 * API Notifications
 * Trả về danh sách thông báo cho người dùng
 */

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Chưa đăng nhập'
    ]);
    exit;
}

try {
    $user = getCurrentUser();
    
    // TODO: Implement notification system
    // Hiện tại trả về dữ liệu mẫu
    
    $notifications = [];
    $unreadCount = 0;
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unreadCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
