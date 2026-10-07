<?php
/**
 * Cấu hình OTP - Hằng số gửi mã xác thực qua Email
 * WebsiteQuanLyDeTai - Hệ thống quản lý đề tài
 */

// OTP Settings
if (!defined('OTP_LENGTH')) {
    define('OTP_LENGTH', 6);
}
if (!defined('OTP_EXPIRY_MINUTES')) {
    define('OTP_EXPIRY_MINUTES', 5);
}
if (!defined('OTP_MAX_ATTEMPTS')) {
    define('OTP_MAX_ATTEMPTS', 3);
}
