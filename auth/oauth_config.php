<?php
/**
 * Cấu hình OAuth cho Microsoft và Google
 */

// Sử dụng BASE_URL từ config thay vì tự động phát hiện
$baseUrl = rtrim(BASE_URL, '/') . '/auth';

// Debug log
error_log("OAuth Config - Base URL: " . $baseUrl);
error_log("OAuth Config - BASE_URL constant: " . BASE_URL);

// Microsoft OAuth Configuration
// https://portal.azure.com/ - Đăng ký ứng dụng tại đây
define('MICROSOFT_CLIENT_ID', 'your_microsoft_client_id_here');
define('MICROSOFT_CLIENT_SECRET', 'your_microsoft_client_secret_here');
define('MICROSOFT_REDIRECT_URI', $baseUrl . '/microsoft_callback.php');
define('MICROSOFT_SCOPE', 'openid profile email');

// Google OAuth Configuration
// https://console.cloud.google.com/ - Đăng ký ứng dụng tại đây
define('GOOGLE_CLIENT_ID', 'your_google_client_id_here');
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret_here');
define('GOOGLE_REDIRECT_URI', $baseUrl . '/google_callback.php');
define('GOOGLE_SCOPE', 'openid profile email');

// OAuth URLs
define('MICROSOFT_AUTH_URL', 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize');
define('MICROSOFT_TOKEN_URL', 'https://login.microsoftonline.com/common/oauth2/v2.0/token');
define('MICROSOFT_USER_URL', 'https://graph.microsoft.com/v1.0/me');

define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USER_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');
?>