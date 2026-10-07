<?php
/**
 * MICROSOFT OAUTH CONFIGURATION
 * Cấu hình đăng nhập bằng Microsoft (Azure AD)
 */

// Microsoft OAuth Credentials
// QUAN TRỌNG: Thay thế các giá trị này bằng thông tin thực từ Azure AD
define('MICROSOFT_CLIENT_ID', getenv('MICROSOFT_CLIENT_ID') ?: 'your-application-client-id');
define('MICROSOFT_CLIENT_SECRET', getenv('MICROSOFT_CLIENT_SECRET') ?: 'your-client-secret');
define('MICROSOFT_REDIRECT_URI', BASE_URL . '/auth/microsoft_callback.php');
define('MICROSOFT_TENANT', getenv('MICROSOFT_TENANT') ?: 'common'); // 'common', 'organizations', 'consumers', hoặc tenant ID cụ thể

// Microsoft OAuth URLs
define('MICROSOFT_OAUTH_URL', 'https://login.microsoftonline.com/' . MICROSOFT_TENANT . '/oauth2/v2.0/authorize');
define('MICROSOFT_TOKEN_URL', 'https://login.microsoftonline.com/' . MICROSOFT_TENANT . '/oauth2/v2.0/token');
define('MICROSOFT_USERINFO_URL', 'https://graph.microsoft.com/v1.0/me');

// Kiểm tra cấu hình
function checkMicrosoftOAuthConfig() {
    $errors = [];
    
    if (MICROSOFT_CLIENT_ID === 'your-application-client-id') {
        $errors[] = 'MICROSOFT_CLIENT_ID chưa được cấu hình';
    }
    
    if (MICROSOFT_CLIENT_SECRET === 'your-client-secret') {
        $errors[] = 'MICROSOFT_CLIENT_SECRET chưa được cấu hình';
    }
    
    return $errors;
}
