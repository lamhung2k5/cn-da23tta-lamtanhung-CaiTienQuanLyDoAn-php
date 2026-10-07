<?php
/**
 * Khởi tạo đăng nhập Google
 */

// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../bootstrap.php';
require_once 'oauth_config.php';

// debug logging
error_log('google_login.php executed');


// Debug log
error_log("Google Login initiated from: " . $_SERVER['REQUEST_URI']);

// Kiểm tra các constants cần thiết
if (!defined('GOOGLE_CLIENT_ID') || !defined('GOOGLE_REDIRECT_URI') || !defined('GOOGLE_SCOPE')) {
    error_log("Google OAuth constants not defined properly");
    setFlashMessage('error', 'Cấu hình Google OAuth chưa đúng. Vui lòng liên hệ quản trị viên.');
    header('Location: login.php');
    exit;
}

// Debug log constants
error_log("Google OAuth Config - Client ID: " . (defined('GOOGLE_CLIENT_ID') ? 'OK' : 'MISSING'));
error_log("Google OAuth Config - Redirect URI: " . GOOGLE_REDIRECT_URI);

// Tạo state để bảo mật
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Tạo URL đăng nhập Google
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'response_type' => 'code',
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'scope' => GOOGLE_SCOPE,
    'state' => $state,
    'access_type' => 'offline',
    'prompt' => 'select_account'  // Luôn hiển thị account chooser
];

$auth_url = GOOGLE_AUTH_URL . '?' . http_build_query($params);

// Debug log
error_log("Google Auth URL: " . $auth_url);

// Chuyển hướng đến Google
header('Location: ' . $auth_url);
exit;