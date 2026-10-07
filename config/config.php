<?php
/**
 * SYSTEM CONFIGURATION
 * Cấu hình chung của hệ thống
 */

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiếm tra kết nối HTTPS
$isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1))
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

define('IS_HTTPS', $isHttps);

// Session với cấu hình bảo mật (Hỗ trợ HTTPS)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,   // Cờ Secure bật khi dùng HTTPS
        'httponly' => true,     // Chống XSS đọc cookie session
        'samesite' => 'Lax'     // Chống CSRF
    ]);
    session_start();
}

// Dynamic Base URL dựa vào giao thức HTTPS/HTTP
$protocol = $isHttps ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Phát hiện môi trường InfinityFree để chọn basepath đúng
$_isInfinityFreeEnv = (
    strpos($host, 'infinityfreeapp.com') !== false ||
    strpos($host, 'infinityfree.net') !== false ||
    strpos(__DIR__, 'infinityfree.com') !== false
);

// InfinityFree: file nằm ở gốc domain (htdocs/) → basepath = /
// Localhost XAMPP: file nằm trong subfolder → basepath = /WebsiteQuanLyDeTai/
define('IS_INFINITYFREE', $_isInfinityFreeEnv);
define('BASE_URL', $protocol . $host . ($_isInfinityFreeEnv ? '/' : '/WebsiteQuanLyDeTai/'));

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');

// Email Configuration (for OTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'maihongloi2003@gmail.com');
define('SMTP_PASS', 'kbfnlgljqlijqwxi');  // App Password 16 ký tự (bỏ dấu cách)
define('SMTP_FROM', 'maihongloi2003@gmail.com');  // Dùng cùng email với SMTP_USER
define('SMTP_FROM_NAME', 'Hệ thống QLĐT - TVU');

// OTP Settings
define('OTP_EXPIRE_MINUTES', 10);
define('OTP_LENGTH', 6);

// System Settings
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1 hour

// Vai trò
define('ROLE_GIANG_VIEN', 'giang_vien');
define('ROLE_SINH_VIEN', 'sinh_vien');
define('ROLE_LANH_DAO', 'lanh_dao');

// Trạng thái đề tài
define('STATUS_NHAP', 'nhap');
define('STATUS_CHO_DUYET', 'cho_duyet');
define('STATUS_DA_DUYET', 'da_duyet');
define('STATUS_TU_CHOI', 'tu_choi');

// Hệ đào tạo
define('HE_CO_SO_NGANH', 'co_so_nganh');
define('HE_CHUYEN_NGANH', 'chuyen_nganh');

// Số lượng đề tài bắt buộc
define('SO_DE_TAI_CO_SO_NGANH', 10);
define('SO_DE_TAI_CHUYEN_NGANH', 10);
define('SO_SINH_VIEN_CO_SO_NGANH', 10);
define('SO_SINH_VIEN_CHUYEN_NGANH', 10);

// Error reporting & Development mode — tự động theo môi trường
if ($_isInfinityFreeEnv) {
    // Production: tắt hiển thị lỗi, ghi log
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    define('DEVELOPMENT_MODE', false);
} else {
    // Localhost: hiển thị lỗi để debug
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('DEVELOPMENT_MODE', true);
}
