<?php
/**
 * ĐĂNG NHẬP GITHUB
 */

require_once '../bootstrap.php';

// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    redirect('index.php');
}

// Kiểm tra cấu hình GitHub OAuth
$configErrors = checkGitHubOAuthConfig();
if (!empty($configErrors)) {
    setFlashMessage('error', 'Cấu hình GitHub OAuth chưa đúng: ' . implode(', ', $configErrors));
    redirect('login.php');
}

// Chuyển hướng đến GitHub OAuth
$githubLoginUrl = getGitHubLoginUrl();
header('Location: ' . $githubLoginUrl);
exit;
?>