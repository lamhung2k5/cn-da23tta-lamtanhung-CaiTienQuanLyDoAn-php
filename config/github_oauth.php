<?php
/**
 * GITHUB OAUTH CONFIGURATION
 * Cấu hình đăng nhập bằng GitHub
 */

// GitHub OAuth Credentials
define('GITHUB_CLIENT_ID', 'YOUR_GITHUB_CLIENT_ID');
define('GITHUB_CLIENT_SECRET', 'YOUR_GITHUB_CLIENT_SECRET');
define('GITHUB_REDIRECT_URI', BASE_URL . 'auth/github_callback.php');

// GitHub OAuth URLs
define('GITHUB_OAUTH_URL', 'https://github.com/login/oauth/authorize');
define('GITHUB_TOKEN_URL', 'https://github.com/login/oauth/access_token');
define('GITHUB_USERINFO_URL', 'https://api.github.com/user');

// GitHub OAuth Scopes
define('GITHUB_SCOPES', 'user:email');

/**
 * Tạo URL đăng nhập GitHub
 */
function getGitHubLoginUrl() {
    $state = bin2hex(random_bytes(16));
    $_SESSION['github_oauth_state'] = $state;
    
    $params = [
        'client_id' => GITHUB_CLIENT_ID,
        'redirect_uri' => GITHUB_REDIRECT_URI,
        'scope' => GITHUB_SCOPES,
        'state' => $state,
        'allow_signup' => 'true'
    ];
    
    return GITHUB_OAUTH_URL . '?' . http_build_query($params);
}

/**
 * Kiểm tra cấu hình GitHub OAuth
 */
function checkGitHubOAuthConfig() {
    $errors = [];
    
    if (GITHUB_CLIENT_ID === 'your-github-client-id') {
        $errors[] = 'GITHUB_CLIENT_ID chưa được cấu hình';
    }
    
    if (GITHUB_CLIENT_SECRET === 'your-github-client-secret') {
        $errors[] = 'GITHUB_CLIENT_SECRET chưa được cấu hình';
    }
    
    if (!filter_var(GITHUB_REDIRECT_URI, FILTER_VALIDATE_URL)) {
        $errors[] = 'GITHUB_REDIRECT_URI không hợp lệ';
    }
    
    return $errors;
}
?>