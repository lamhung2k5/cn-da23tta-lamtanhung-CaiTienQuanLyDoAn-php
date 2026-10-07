<?php
/**
 * MICROSOFT LOGIN - Redirect to Microsoft OAuth
 */

require_once '../bootstrap.php';
require_once '../config/microsoft_oauth.php';

// Kiểm tra cấu hình Microsoft OAuth
$configErrors = checkMicrosoftOAuthConfig();
if (!empty($configErrors)) {
    setFlashMessage('error', 'Microsoft OAuth chưa được cấu hình: ' . implode(', ', $configErrors));
    redirect('login.php');
}

// Tạo state token để bảo mật (thống nhất với Google)
$_SESSION['oauth_state'] = bin2hex(random_bytes(32));

// Tham số OAuth
$params = [
    'client_id' => MICROSOFT_CLIENT_ID,
    'redirect_uri' => MICROSOFT_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid profile email User.Read',
    'state' => $_SESSION['oauth_state'],
    'response_mode' => 'query',
    'prompt' => 'select_account'
];

// Redirect đến Microsoft
$auth_url = MICROSOFT_OAUTH_URL . '?' . http_build_query($params);
header('Location: ' . $auth_url);
exit;
