<?php
/**
 * MICROSOFT CALLBACK - Xử lý response từ Microsoft OAuth
 */

require_once '../bootstrap.php';
require_once '../config/microsoft_oauth.php';

// Kiểm tra state token (thống nhất với Google)
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    setFlashMessage('error', 'Xác thực không hợp lệ. Vui lòng thử lại.');
    redirect('login.php');
}

// Kiểm tra có code không
if (!isset($_GET['code'])) {
    setFlashMessage('error', 'Đăng nhập Microsoft thất bại. Vui lòng thử lại.');
    redirect('login.php');
}

$code = $_GET['code'];

// Đổi code lấy access token
$token_params = [
    'code' => $code,
    'client_id' => MICROSOFT_CLIENT_ID,
    'client_secret' => MICROSOFT_CLIENT_SECRET,
    'redirect_uri' => MICROSOFT_REDIRECT_URI,
    'grant_type' => 'authorization_code',
    'scope' => 'openid profile email User.Read'
];

$ch = curl_init(MICROSOFT_TOKEN_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    setFlashMessage('error', 'Không thể lấy thông tin từ Microsoft. Vui lòng thử lại.');
    redirect('login.php');
}

// Lấy thông tin user từ Microsoft Graph API
$ch = curl_init(MICROSOFT_USERINFO_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token_data['access_token']
]);
$user_response = curl_exec($ch);
curl_close($ch);

$microsoft_user = json_decode($user_response, true);

if (!isset($microsoft_user['mail']) && !isset($microsoft_user['userPrincipalName'])) {
    setFlashMessage('error', 'Không thể lấy thông tin email từ Microsoft.');
    redirect('login.php');
}

// Microsoft có thể trả về email ở 'mail' hoặc 'userPrincipalName'
$email = $microsoft_user['mail'] ?? $microsoft_user['userPrincipalName'];

// Xử lý đăng nhập/đăng ký
$nguoiDungModel = new NguoiDungModel();
$user = $nguoiDungModel->findByEmail($email);

if (!$user) {
    // Email chưa tồn tại trong hệ thống
    setFlashMessage('error', 'Email ' . $email . ' chưa được đăng ký trong hệ thống. Vui lòng liên hệ quản trị viên.');
    redirect('login.php');
}

// Kiểm tra trạng thái tài khoản
if ($user['trang_thai'] !== 'active') {
    setFlashMessage('error', 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.');
    redirect('login.php');
}

// Lấy thông tin vai trò
$sql = "SELECT vt.ma_vai_tro, vt.ten_vai_tro 
        FROM vai_tro vt 
        WHERE vt.id = :vai_tro_id";
$vaiTro = $nguoiDungModel->queryOne($sql, ['vai_tro_id' => $user['vai_tro_id']]);

// Lấy profile_id
$profileId = null;
switch ($vaiTro['ma_vai_tro']) {
    case ROLE_GIANG_VIEN:
        $profile = $nguoiDungModel->queryOne("SELECT id FROM giang_vien WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
        $profileId = $profile['id'] ?? null;
        break;
    case ROLE_SINH_VIEN:
        $profile = $nguoiDungModel->queryOne("SELECT id FROM sinh_vien WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
        $profileId = $profile['id'] ?? null;
        break;
    case ROLE_LANH_DAO:
        $profile = $nguoiDungModel->queryOne("SELECT id FROM lanh_dao WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
        $profileId = $profile['id'] ?? null;
        break;
}

// Lưu session
$_SESSION['user_id'] = $user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['ho_ten'] = $user['ho_ten'];
$_SESSION['vai_tro'] = $vaiTro['ma_vai_tro'];
$_SESSION['vai_tro_id'] = $user['vai_tro_id'];
$_SESSION['profile_id'] = $profileId;
$_SESSION['login_method'] = 'microsoft';

// Xóa state token
unset($_SESSION['microsoft_oauth_state']);

// Chuyển hướng theo vai trò
switch ($vaiTro['ma_vai_tro']) {
    case ROLE_GIANG_VIEN:
        redirect('giang_vien/dashboard.php');
        break;
    case ROLE_SINH_VIEN:
        redirect('sinh_vien/dashboard.php');
        break;
    case ROLE_LANH_DAO:
        redirect('lanh_dao/dashboard.php');
        break;
    default:
        redirect('index.php');
}
