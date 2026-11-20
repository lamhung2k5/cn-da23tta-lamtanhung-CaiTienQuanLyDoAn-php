<?php

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function js($value, $flags = 0) {
    return json_encode($value, $flags | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
}

function safeCsvRow($row) {
    return array_map(function ($value) {
        return is_string($value) && preg_match('/^[=+@\-\t\r\n]/', $value) ? "'" . $value : $value;
    }, $row);
}

function securityStorageDirectory() {
    // Test databases must not invalidate real sessions or share login throttles.
    return ROOT_PATH . 'storage/security/' . hash('sha256', DB_HOST . ':' . DB_PORT . '/' . DB_NAME);
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(generateCSRF()) . '">';
}

function safeHref($value) {
    $value = trim((string)$value);
    if ($value === '' || preg_match('/[\x00-\x20\\\\]/', $value)) { return '#'; }
    $scheme = parse_url($value, PHP_URL_SCHEME);
    if ($scheme !== null && $scheme !== false && !in_array(strtolower($scheme), ['http','https','mailto'], true)) { return '#'; }
    if (strpos($value, '//') === 0) { return '#'; }
    return $value;
}

function accountSessionFingerprint($user) {
    $versionFile = securityStorageDirectory() . '/account-' . (int)$user['id'] . '.version';
    $version = is_file($versionFile) ? file_get_contents($versionFile) : '';
    return hash('sha256', $user['mat_khau'] . '|' . $user['vai_tro_id'] . '|' . $version);
}

function invalidateAccountSessions($id) {
    $directory = securityStorageDirectory();
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) { throw new RuntimeException('Security directory unavailable.'); }
    if (file_put_contents($directory . '/account-' . (int)$id . '.version', bin2hex(random_bytes(32)), LOCK_EX) === false) {
        throw new RuntimeException('Cannot invalidate account sessions.');
    }
}

function securityDeny($status = 403, $message = 'Bạn không có quyền thực hiện yêu cầu này.') {
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo $message;
    exit;
}

function requirePost() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        securityDeny(405, 'Thao tác này yêu cầu phương thức POST.');
    }
}

function safeError($exception) {
    error_log(get_class($exception) . ': ' . $exception->getMessage());
    return 'Không thể hoàn tất yêu cầu. Vui lòng thử lại hoặc liên hệ Lãnh đạo.';
}

function auditSecurity($action, $targetId = null) {
    $directory = securityStorageDirectory();
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Security audit directory unavailable.');
    }
    $entry = json_encode(['time' => date(DATE_ATOM), 'actor' => $_SESSION['user_id'] ?? null,
        'action' => $action, 'target' => $targetId], JSON_UNESCAPED_UNICODE);
    if (file_put_contents($directory . '/audit.log', $entry . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
        throw new RuntimeException('Security audit log unavailable.');
    }
}

function authenticateSchoolAccount($email, $password) {
    $email = strtolower(trim((string)$email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255 || !is_string($password) || strlen($password) > 1024) {
        return ['success' => false, 'message' => 'Tài khoản hoặc mật khẩu không hợp lệ.'];
    }
    $directory = securityStorageDirectory();
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Login throttle unavailable.');
    }
    // IP is always locked first: blocked IPs cannot create unlimited account files.
    $keys = [hash('sha256', 'ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'cli')) => 20,
        hash('sha256', 'account:' . $email) => 5];
    $handles = [];
    $states = [];
    try {
        foreach ($keys as $key => $limit) {
            $handle = fopen($directory . '/login-' . $key . '.json', 'c+');
            if (!$handle || !flock($handle, LOCK_EX)) {
                throw new RuntimeException('Cannot lock login throttle.');
            }
            $handles[$key] = $handle;
            $state = json_decode(stream_get_contents($handle), true);
            if (!is_array($state) || time() - ($state['start'] ?? 0) >= 900) {
                $state = ['start' => time(), 'failures' => 0];
            }
            $states[$key] = $state;
            if ($state['failures'] >= $limit) {
                return ['success' => false, 'message' => 'Đã vượt số lần đăng nhập. Vui lòng thử lại sau 15 phút.'];
            }
        }
        $result = (new NguoiDungModel())->login($email, $password);
        foreach ($handles as $key => $handle) {
            // Successful logins clear only their account counter, not shared IP failures.
            if (!$result['success']) {
                $states[$key]['failures']++;
            } elseif ($keys[$key] === 5) {
                $states[$key]['failures'] = 0;
            }
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($states[$key]));
            fflush($handle);
        }
        if (!$result['success']) {
            $result['message'] = 'Tài khoản hoặc mật khẩu không hợp lệ. Liên hệ Lãnh đạo nếu cần hỗ trợ.';
        }
        return $result;
    } finally {
        foreach ($handles as $handle) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}

function validateSchoolSession() {
    if (!isLoggedIn()) { return; }
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT nd.id, nd.email, nd.ho_ten, nd.mat_khau, nd.trang_thai, nd.vai_tro_id, vt.ma_vai_tro FROM nguoi_dung nd JOIN vai_tro vt ON vt.id=nd.vai_tro_id WHERE nd.id=?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $fingerprint = $user ? accountSessionFingerprint($user) : '';
    if (!$user || $user['trang_thai'] !== 'active' || !isset($_SESSION['auth_fingerprint']) ||
        !hash_equals($_SESSION['auth_fingerprint'], $fingerprint) ||
        time() - ($_SESSION['login_time'] ?? 0) > SESSION_TIMEOUT) {
        destroySecureSession();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { securityDeny(401, 'Phiên đăng nhập không còn hợp lệ.'); }
        redirect('auth/login.php');
    }
    $_SESSION['email'] = $user['email'];
    $_SESSION['ho_ten'] = $user['ho_ten'];
    $_SESSION['vai_tro'] = $user['ma_vai_tro'];
    $_SESSION['vai_tro_id'] = $user['vai_tro_id'];
    $_SESSION['login_time'] = time();
}

function requireTopicOwner($topicId) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT dt.*, gv.nguoi_dung_id AS owner_id FROM de_tai dt JOIN giang_vien gv ON gv.id=dt.giang_vien_id WHERE dt.id=?');
    $stmt->execute([(int)$topicId]);
    $topic = $stmt->fetch();
    if (!$topic || (!hasRole(ROLE_LANH_DAO) && (int)$topic['owner_id'] !== (int)($_SESSION['user_id'] ?? 0))) {
        securityDeny();
    }
    return $topic;
}

function requireRegistrationOwner($registrationId) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT dk.*, gv.nguoi_dung_id AS owner_id FROM dang_ky_de_tai dk JOIN de_tai dt ON dt.id=dk.de_tai_id JOIN giang_vien gv ON gv.id=dt.giang_vien_id WHERE dk.id=?');
    $stmt->execute([(int)$registrationId]);
    $registration = $stmt->fetch();
    if (!$registration || (!hasRole(ROLE_LANH_DAO) && (int)$registration['owner_id'] !== (int)($_SESSION['user_id'] ?? 0))) {
        securityDeny();
    }
    return $registration;
}

function initializeSecurity() {
    if (PHP_SAPI === 'cli') { return; }
    set_exception_handler(function ($error) {
        $message = safeError($error);
        securityDeny(500, $message);
    });
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'");
    if (IS_HTTPS && !DEVELOPMENT_MODE) { header('Strict-Transport-Security: max-age=31536000'); }
    if (isLoggedIn()) { header('Cache-Control: no-store'); }
    validateSchoolSession();
    $path = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $root = str_replace('\\', '/', ROOT_PATH);
    if (strpos($path, $root . 'lanh_dao/') === 0) { requireRole(ROLE_LANH_DAO); }
    if (strpos($path, $root . 'sinh_vien/') === 0) { requireRole(ROLE_SINH_VIEN); }
    if (strpos($path, $root . 'giang_vien/') === 0) {
        requireLogin();
        if (!in_array($_SESSION['vai_tro'], [ROLE_GIANG_VIEN, ROLE_LANH_DAO], true)) { securityDeny(); }
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!validateCSRF($token)) { securityDeny(403, 'Token bảo mật không hợp lệ. Vui lòng tải lại trang.'); }
    }
}
