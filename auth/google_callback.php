<?php
/**
 * Xử lý callback từ Google
 */

// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../bootstrap.php';
require_once 'oauth_config.php';

// Debug log
error_log("Google Callback received - GET params: " . json_encode($_GET));
error_log("Google Callback - Session state: " . ($_SESSION['oauth_state'] ?? 'not set'));

// Kiểm tra có lỗi từ Google không
if (isset($_GET['error'])) {
    $error_description = $_GET['error_description'] ?? $_GET['error'];
    error_log("Google OAuth Error: " . $error_description);
    setFlashMessage('error', 'Đăng nhập Google thất bại: ' . $error_description);
    header('Location: login.php');
    exit;
}

// Kiểm tra state để bảo mật
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    setFlashMessage('error', 'Lỗi bảo mật. Vui lòng thử lại.');
    header('Location: login.php');
    exit;
}

// Kiểm tra có code không
if (!isset($_GET['code'])) {
    setFlashMessage('error', 'Đăng nhập Google thất bại.');
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

try {
    // Lấy access token
    $token_data = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => GOOGLE_REDIRECT_URI
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $token_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception('Không thể lấy access token từ Google');
    }

    $token_data = json_decode($token_response, true);
    if (!isset($token_data['access_token'])) {
        throw new Exception('Access token không hợp lệ');
    }

    // Lấy thông tin user
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_USER_URL . '?access_token=' . $token_data['access_token']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $user_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception('Không thể lấy thông tin user từ Google');
    }

    $user_data = json_decode($user_response, true);
    
    // Xử lý đăng nhập với cơ chế retry nếu kết nối DB bị rớt giữa chừng
    $email = $user_data['email'] ?? '';
    $ho_ten = $user_data['name'] ?? '';
    
    if (empty($email)) {
        throw new Exception('Không thể lấy email từ tài khoản Google');
    }

    $user = null;
    $attempt = 0;
    while ($attempt < 2) {
        try {
            // Kiểm tra user có tồn tại không
            $nguoiDungModel = new NguoiDungModel();
            $user = $nguoiDungModel->findByEmail($email);
            
            if (!$user) {
                // Tạo user mới nếu chưa tồn tại
                $result = $nguoiDungModel->createOAuthUser($email, $ho_ten, 'google');
                if (!$result['success']) {
                    throw new Exception($result['message']);
                }
                $user = $result['user'];
            }
            
            // nếu đến tận đây là thành công thì thoát vòng lặp
            break;
        } catch (Exception $e) {
            // nếu lỗi do "server has gone away" thì cố gắng reconnect và retry một lần nữa
            if ($attempt === 0 && stripos($e->getMessage(), '2006') !== false) {
                error_log("Detected DB gone away during OAuth login, reconnecting and retrying");
                Database::getInstance()->reconnect();
                $attempt++;
                continue;
            }
            // khác thì ném tiếp ra ngoài để phần ngoài bắt và chuyển hướng lỗi
            throw $e;
        }
    }
    
    // Kiểm tra và cập nhật vai trò từ email (để phát hiện nếu email giảng viên bị liên kết sai)
    // Auto-detect vai trò từ email - kiểm tra xem email này có trong danh sách giảng viên/lãnh đạo không
    $db = Database::getInstance()->getConnection();
    
    // Kiểm tra nếu người dùng đã chọn role từ trang login
    $decidedRole = isset($_SESSION['oauth_selected_role']) ? $_SESSION['oauth_selected_role'] : null;
    
    // Nếu không có role được chọn, tự detect từ profile hoặc email
    if (!$decidedRole) {
        // Kiểm tra xem email này đã có profile giảng viên chưa
        $giangVienProfile = $db->prepare("
            SELECT gv.id FROM giang_vien gv
            WHERE gv.nguoi_dung_id = :user_id
            LIMIT 1
        ");
        $giangVienProfile->execute(['user_id' => $user['id']]);
        $giangVienExists = $giangVienProfile->fetch();
        
        // Kiểm tra xem email này đã có profile lãnh đạo chưa
        $lanhDaoProfile = $db->prepare("
            SELECT ld.id FROM lanh_dao ld
            WHERE ld.nguoi_dung_id = :user_id
            LIMIT 1
        ");
        $lanhDaoProfile->execute(['user_id' => $user['id']]);
        $lanhDaoExists = $lanhDaoProfile->fetch();
        
        // Kiểm tra xem email này đã có profile sinh viên chưa
        $sinhVienProfile = $db->prepare("
            SELECT sv.id FROM sinh_vien sv
            WHERE sv.nguoi_dung_id = :user_id
            LIMIT 1
        ");
        $sinhVienProfile->execute(['user_id' => $user['id']]);
        $sinhVienExists = $sinhVienProfile->fetch();
        
        // Quyết định vai trò dựa trên profile tồn tại
        $decidedRole = ROLE_SINH_VIEN; // Default
        if ($giangVienExists) {
            $decidedRole = ROLE_GIANG_VIEN;
        } elseif ($lanhDaoExists) {
            $decidedRole = ROLE_LANH_DAO;
        } else {
            // Nếu không có profile, kiểm tra email trong danh sách giảng viên
            $facultyMembers = require '../config/faculty_members.php';
            $isInFacultyList = false;
            foreach ($facultyMembers as $member) {
                if (strtolower($member['email']) === strtolower($email)) {
                    $isInFacultyList = true;
                    break;
                }
            }
            if ($isInFacultyList) {
                $decidedRole = ROLE_GIANG_VIEN;
            }
        }
    }
    
    // Cập nhật vai_tro_id nếu vai trò không khớp
    if ($decidedRole !== ($user['vai_tro'] ?? '')) {
        $vaiTroRecord = $nguoiDungModel->queryOne(
            "SELECT id FROM vai_tro WHERE ma_vai_tro = :role",
            ['role' => $decidedRole]
        );
        
        if ($vaiTroRecord) {
            $updateStmt = $db->prepare("UPDATE nguoi_dung SET vai_tro_id = :vai_tro_id WHERE id = :id");
            $updateStmt->execute(['vai_tro_id' => $vaiTroRecord['id'], 'id' => $user['id']]);
            $user['vai_tro_id'] = $vaiTroRecord['id'];
        }
    }
    
    $user['vai_tro'] = $decidedRole;
    
    // Cập nhật vai_tro_id nếu role được chọn từ session
    if (isset($_SESSION['oauth_selected_role'])) {
        $vaiTroRecord = $nguoiDungModel->queryOne(
            "SELECT id FROM vai_tro WHERE ma_vai_tro = :role",
            ['role' => $decidedRole]
        );
        if ($vaiTroRecord) {
            $user['vai_tro_id'] = $vaiTroRecord['id'];
            // Cập nhật database
            $db->prepare("UPDATE nguoi_dung SET vai_tro_id = :vai_tro_id WHERE id = :id")
                ->execute(['vai_tro_id' => $vaiTroRecord['id'], 'id' => $user['id']]);
        }
    }
    
    // Lấy profile_id tùy theo vai trò
    $profileId = null;
    switch ($decidedRole) {
        case ROLE_GIANG_VIEN:
            $profile = $nguoiDungModel->queryOne("SELECT id FROM giang_vien WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
            $profileId = $profile['id'] ?? null;
            
            // Nếu không có profile, tạo profile mới
            if (!$profileId) {
                $maGiangVien = 'OAUTH_' . substr(md5($user['email']), 0, 10);
                $db->prepare("INSERT INTO giang_vien (nguoi_dung_id, ma_giang_vien, khoa, chuyen_mon) VALUES (:user_id, :ma_giang_vien, :khoa, :chuyen_mon)")
                    ->execute([
                        'user_id' => $user['id'],
                        'ma_giang_vien' => $maGiangVien,
                        'khoa' => 'Công nghệ thông tin',
                        'chuyen_mon' => 'Chưa cập nhật'
                    ]);
                $profileId = $db->lastInsertId();
            }
            break;
        case ROLE_SINH_VIEN:
            $profile = $nguoiDungModel->queryOne("SELECT id FROM sinh_vien WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
            $profileId = $profile['id'] ?? null;
            
            // Nếu không có profile, tạo profile mới
            if (!$profileId) {
                $maSinhVien = 'OAUTH_' . substr(md5($user['email']), 0, 10);
                $db->prepare("INSERT INTO sinh_vien (nguoi_dung_id, ma_sinh_vien) VALUES (:user_id, :ma_sinh_vien)")
                    ->execute(['user_id' => $user['id'], 'ma_sinh_vien' => $maSinhVien]);
                $profileId = $db->lastInsertId();
            }
            break;
        case ROLE_LANH_DAO:
            $profile = $nguoiDungModel->queryOne("SELECT id FROM lanh_dao WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
            $profileId = $profile['id'] ?? null;
            
            // Nếu không có profile, tạo profile mới
            if (!$profileId) {
                $maLanhDao = 'OAUTH_' . substr(md5($user['email']), 0, 10);
                $db->prepare("INSERT INTO lanh_dao (nguoi_dung_id, ma_lanh_dao) VALUES (:user_id, :ma_lanh_dao)")
                    ->execute(['user_id' => $user['id'], 'ma_lanh_dao' => $maLanhDao]);
                $profileId = $db->lastInsertId();
            }
            break;
    }
    $user['profile_id'] = $profileId;
    
    // Đăng nhập
    // Regenerate session ID để bảo mật
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['ho_ten'] = $user['ho_ten'];
    $_SESSION['vai_tro'] = $user['vai_tro'];
    $_SESSION['vai_tro_id'] = $user['vai_tro_id'];
    $_SESSION['profile_id'] = $user['profile_id'];
    $_SESSION['login_time'] = time(); // Thêm thời gian đăng nhập
    
    // Xóa OAuth state tokens
    unset($_SESSION['oauth_state']);
    unset($_SESSION['oauth_selected_role']);
    
    // Debug log
    error_log("OAuth Login Success: " . json_encode([
        'user_id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'vai_tro' => $_SESSION['vai_tro']
    ]));
    
    // Chuyển hướng theo vai trò
    switch ($user['vai_tro']) {
        case ROLE_GIANG_VIEN:
            // Kiểm tra nếu có mã tạm thời thì chuyển đến trang cập nhật
            $profile = $nguoiDungModel->queryOne("SELECT ma_giang_vien FROM giang_vien WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
            if ($profile && strpos($profile['ma_giang_vien'], 'OAUTH_') === 0) {
                header('Location: update_profile_code.php');
            } else {
                header('Location: ../giang_vien/dashboard.php');
            }
            break;
        case ROLE_SINH_VIEN:
            // Kiểm tra nếu có mã tạm thời thì chuyển đến trang cập nhật
            $profile = $nguoiDungModel->queryOne("SELECT ma_sinh_vien FROM sinh_vien WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
            if ($profile && strpos($profile['ma_sinh_vien'], 'OAUTH_') === 0) {
                header('Location: update_profile_code.php');
            } else {
                header('Location: ../sinh_vien/dashboard.php');
            }
            break;
        case ROLE_LANH_DAO:
            // Kiểm tra nếu có mã tạm thời thì chuyển đến trang cập nhật
            $profile = $nguoiDungModel->queryOne("SELECT ma_lanh_dao FROM lanh_dao WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
            if ($profile && strpos($profile['ma_lanh_dao'], 'OAUTH_') === 0) {
                header('Location: update_profile_code.php');
            } else {
                header('Location: ../lanh_dao/dashboard.php');
            }
            break;
        default:
            header('Location: ../index.php'); // chuyển về trang chủ sau khi callback
    }
    exit;

} catch (Exception $e) {
    setFlashMessage('error', 'Đăng nhập Google thất bại: ' . $e->getMessage());
    header('Location: login.php');
    exit;
}