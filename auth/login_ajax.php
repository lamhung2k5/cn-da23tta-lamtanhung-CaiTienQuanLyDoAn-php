<?php
/**
 * AJAX LOGIN HANDLER
 */

require_once '../bootstrap.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
    exit;
}

$nguoiDungModel = new NguoiDungModel();
$result = $nguoiDungModel->login($email, $password);

if ($result['success']) {
    // Lưu session
    $_SESSION['user_id'] = $result['user']['id'];
    $_SESSION['email'] = $result['user']['email'];
    $_SESSION['ho_ten'] = $result['user']['ho_ten'];
    $_SESSION['vai_tro'] = $result['user']['vai_tro'];
    $_SESSION['vai_tro_id'] = $result['user']['vai_tro_id'];
    $_SESSION['profile_id'] = $result['user']['profile_id'];
    
    // Determine redirect URL based on role
    $redirectUrl = 'index.php';
    switch ($result['user']['vai_tro']) {
        case ROLE_GIANG_VIEN:
            $redirectUrl = 'giang_vien/dashboard.php';
            break;
        case ROLE_SINH_VIEN:
            $redirectUrl = 'sinh_vien/dashboard.php';
            break;
        case ROLE_LANH_DAO:
            $redirectUrl = 'lanh_dao/dashboard.php';
            break;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'redirect' => $redirectUrl
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $result['message']
    ]);
}
