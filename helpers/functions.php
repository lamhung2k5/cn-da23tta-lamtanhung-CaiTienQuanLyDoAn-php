<?php
/**
 * HELPER FUNCTIONS
 * Các hàm tiện ích dùng chung
 */

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'ho_ten' => $_SESSION['ho_ten'] ?? null,
        'vai_tro' => $_SESSION['vai_tro'] ?? null,
        'vai_tro_id' => $_SESSION['vai_tro_id'] ?? null,
        'profile_id' => $_SESSION['profile_id'] ?? null, // ID của giang_vien/sinh_vien/lanh_dao
    ];
}

/**
 * Check user role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['vai_tro'] === $role;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Vui lòng đăng nhập để tiếp tục';
        redirect('auth/login.php');
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirect('index.php');
    }
}

/**
 * Sanitize input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password bằng password_hash()
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Kiểm tra mật khẩu bằng password_verify()
 */
function verifyPassword($password, $hash) {
    if (password_verify($password, $hash)) {
        return true;
    }
    // Backward compatibility cho dữ liệu cũ (MD5 hoặc Plaintext)
    if ($hash === md5($password) || $hash === $password) {
        return true;
    }
    return false;
}

/**
 * Tự động chuyển hướng sang HTTPS nếu cần
 */
function enforceHttps() {
    if (!defined('IS_HTTPS') || !IS_HTTPS) {
        if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
            $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirectUrl, true, 301);
            exit();
        }
    }
}

/**
 * Generate OTP code
 */
function generateOTP($length = 6) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Format date
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Get flash message
 */
function getFlashMessage($key) {
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}

/**
 * Set flash message
 */
function setFlashMessage($key, $message) {
    $_SESSION[$key] = $message;
}

/**
 * JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Success JSON response
 */
function jsonSuccess($message, $data = null) {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Error JSON response
 */
function jsonError($message, $statusCode = 400) {
    jsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

/**
 * Validate CSRF token
 */
function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 */
function generateCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'nhap' => '<span class="badge bg-secondary">Nháp</span>',
        'cho_duyet' => '<span class="badge bg-warning">Chờ duyệt</span>',
        'da_duyet' => '<span class="badge bg-success">Đã duyệt</span>',
        'tu_choi' => '<span class="badge bg-danger">Từ chối</span>',
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}

/**
 * Get hệ đào tạo label
 */
function getHeDaoTaoLabel($he) {
    $labels = [
        'co_so_nganh' => 'Cơ sở ngành',
        'chuyen_nganh' => 'Chuyên ngành',
    ];
    return $labels[$he] ?? $he;
}

/**
 * Get trạng thái label (text only, no HTML)
 */
function getTrangThaiLabel($status) {
    $labels = [
        'nhap' => 'Nháp',
        'cho_duyet' => 'Chờ duyệt',
        'da_duyet' => 'Đã duyệt',
        'tu_choi' => 'Từ chối',
    ];
    return $labels[$status] ?? $status;
}

/**
 * Generate CAPTCHA code
 */
function generateCaptcha($length = 6) {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $captcha = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha;
}
/**
 * Kiểm tra session timeout
 */
function checkSessionTimeout() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Kiểm tra thời gian đăng nhập (mặc định 2 giờ)
    $sessionTimeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 7200; // 2 hours
    $loginTime = $_SESSION['login_time'] ?? time();
    
    if (time() - $loginTime > $sessionTimeout) {
        // Session hết hạn
        session_destroy();
        setFlashMessage('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        return false;
    }
    
    return true;
}

/**
 * Làm mới session timeout
 */
function refreshSessionTimeout() {
    if (isLoggedIn()) {
        $_SESSION['login_time'] = time();
    }
}

/**
 * Kiểm tra và làm mới session (gọi trong bootstrap)
 */
function validateSession() {
    if (isLoggedIn() && !checkSessionTimeout()) {
        redirect('auth/login.php');
    }
    
    // Làm mới session timeout mỗi request
    refreshSessionTimeout();
}

/**
 * Tạo secure session sau khi đăng nhập thành công
 */
function createSecureSession($userData) {
    // Regenerate session ID để bảo mật
    session_regenerate_id(true);
    
    // Lưu thông tin user
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['email'] = $userData['email'];
    $_SESSION['ho_ten'] = $userData['ho_ten'];
    $_SESSION['vai_tro'] = $userData['vai_tro'];
    $_SESSION['vai_tro_id'] = $userData['vai_tro_id'];
    $_SESSION['profile_id'] = $userData['profile_id'] ?? null;
    $_SESSION['login_time'] = time();
    
    // Tạo CSRF token mới
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Xóa session an toàn khi đăng xuất
 */
function destroySecureSession() {
    // Xóa tất cả session variables
    $_SESSION = array();
    
    // Xóa session cookie nếu có
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Lấy các link cấu hình menu từ file JSON
 */
function getMenuLinks() {
    $configFile = __DIR__ . '/../config/links_config.json';
    $defaultLinks = [
        'ket_qua_thi' => 'https://drive.google.com/drive/mobile/folders/1KKPMSbuF2EK1rviPWAllBZcTSp5EjKLE?usp=drive_link&pli=1&sort=15&direction=d',
        'bieu_mau_sinh_vien' => '',
        'bieu_mau_giang_vien' => ''
    ];
    
    if (file_exists($configFile)) {
        $links = json_decode(file_get_contents($configFile), true);
        if ($links) {
            return array_merge($defaultLinks, $links);
        }
    }
    
    return $defaultLinks;
}
