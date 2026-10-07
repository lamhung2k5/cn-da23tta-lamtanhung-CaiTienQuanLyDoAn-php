<?php
/**
 * GITHUB OAUTH CALLBACK
 * Xử lý callback từ GitHub sau khi user authorize
 */

require_once '../bootstrap.php';

// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    redirect('index.php');
}

// Kiểm tra có code và state không
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$error = $_GET['error'] ?? '';

// Xử lý lỗi từ GitHub
if ($error) {
    $errorDescription = $_GET['error_description'] ?? 'Unknown error';
    setFlashMessage('error', 'GitHub OAuth Error: ' . $errorDescription);
    redirect('login.php');
}

// Kiểm tra state để tránh CSRF
if (empty($state) || $state !== ($_SESSION['github_oauth_state'] ?? '')) {
    setFlashMessage('error', 'Invalid state parameter. Possible CSRF attack.');
    redirect('login.php');
}

// Xóa state đã sử dụng
unset($_SESSION['github_oauth_state']);

if (empty($code)) {
    setFlashMessage('error', 'Authorization code not received from GitHub');
    redirect('login.php');
}

try {
    // Bước 1: Đổi code lấy access token
    $tokenData = [
        'client_id' => GITHUB_CLIENT_ID,
        'client_secret' => GITHUB_CLIENT_SECRET,
        'code' => $code,
        'redirect_uri' => GITHUB_REDIRECT_URI
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GITHUB_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'User-Agent: QLDT-System'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to get access token from GitHub. HTTP Code: ' . $httpCode);
    }

    $tokenResponse = json_decode($response, true);

    if (!isset($tokenResponse['access_token'])) {
        throw new Exception('Access token not found in GitHub response: ' . $response);
    }

    $accessToken = $tokenResponse['access_token'];

    // Bước 2: Lấy thông tin user từ GitHub
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GITHUB_USERINFO_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'User-Agent: QLDT-System',
        'Accept: application/vnd.github.v3+json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $userResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to get user info from GitHub. HTTP Code: ' . $httpCode);
    }

    $githubUser = json_decode($userResponse, true);

    if (!isset($githubUser['id'])) {
        throw new Exception('Invalid user data from GitHub: ' . $userResponse);
    }

    // Bước 3: Lấy email từ GitHub (có thể cần API call riêng)
    $email = $githubUser['email'];

    // Nếu email null, lấy từ API emails
    if (empty($email)) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/user/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'User-Agent: QLDT-System',
            'Accept: application/vnd.github.v3+json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $emailsResponse = curl_exec($ch);
        curl_close($ch);

        $emails = json_decode($emailsResponse, true);

        // Tìm email primary
        foreach ($emails as $emailData) {
            if ($emailData['primary'] && $emailData['verified']) {
                $email = $emailData['email'];
                break;
            }
        }
    }

    if (empty($email)) {
        throw new Exception('Could not get email from GitHub account');
    }

    // Bước 4: Kiểm tra user có tồn tại trong hệ thống không
    $nguoiDungModel = new NguoiDungModel();
    $existingUser = $nguoiDungModel->findByEmail($email);

    if (!$existingUser) {
        setFlashMessage('error', 'Email ' . $email . ' chưa được đăng ký trong hệ thống. Vui lòng đăng ký tài khoản trước.');
        redirect('register.php');
    }

    // Bước 5: Đăng nhập user
    // Regenerate session ID để bảo mật
    session_regenerate_id(true);

    // Lưu session
    $_SESSION['user_id'] = $existingUser['id'];
    $_SESSION['email'] = $existingUser['email'];
    $_SESSION['ho_ten'] = $existingUser['ho_ten'];
    $_SESSION['vai_tro'] = $existingUser['vai_tro'];
    $_SESSION['vai_tro_id'] = $existingUser['vai_tro_id'];
    $_SESSION['profile_id'] = $existingUser['profile_id'];
    $_SESSION['login_time'] = time();
    $_SESSION['login_method'] = 'github';

    // Tạo CSRF token mới
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Log đăng nhập thành công
    error_log("GitHub OAuth login successful for user: " . $email);

    setFlashMessage('success', 'Đăng nhập GitHub thành công Chào mừng ' . $existingUser['ho_ten']);

    // Chuyển hướng theo vai trò
    switch ($existingUser['vai_tro']) {
        case ROLE_GIANG_VIEN:
            // Kiểm tra nếu có mã tạm thời thì chuyển đến trang cập nhật
            $db = Database::getInstance()->getConnection();
            $profile = $db->prepare("SELECT ma_giang_vien FROM giang_vien WHERE nguoi_dung_id = ?");
            $profile->execute([$existingUser['id']]);
            $result = $profile->fetch();
            if ($result && strpos($result['ma_giang_vien'], 'OAUTH_') === 0) {
                redirect('update_profile_code.php');
            } else {
                redirect('giang_vien/dashboard.php');
            }
            break;
        case ROLE_SINH_VIEN:
            // Kiểm tra nếu có mã tạm thời thì chuyển đến trang cập nhật
            $db = Database::getInstance()->getConnection();
            $profile = $db->prepare("SELECT ma_sinh_vien FROM sinh_vien WHERE nguoi_dung_id = ?");
            $profile->execute([$existingUser['id']]);
            $result = $profile->fetch();
            if ($result && strpos($result['ma_sinh_vien'], 'OAUTH_') === 0) {
                redirect('update_profile_code.php');
            } else {
                redirect('sinh_vien/dashboard.php');
            }
            break;
        case ROLE_LANH_DAO:
            // Kiểm tra nếu có mã tạm thời thì chuyển đến trang cập nhật
            $db = Database::getInstance()->getConnection();
            $profile = $db->prepare("SELECT ma_lanh_dao FROM lanh_dao WHERE nguoi_dung_id = ?");
            $profile->execute([$existingUser['id']]);
            $result = $profile->fetch();
            if ($result && strpos($result['ma_lanh_dao'], 'OAUTH_') === 0) {
                redirect('update_profile_code.php');
            } else {
                redirect('lanh_dao/dashboard.php');
            }
            break;
        default:
            redirect('index.php');
    }

} catch (Exception $e) {
    error_log("GitHub OAuth Error: " . $e->getMessage());
    setFlashMessage('error', 'Lỗi đăng nhập GitHub: ' . $e->getMessage());
    redirect('login.php');
}
?>