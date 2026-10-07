<?php
/**
 * ĐẶT LẠI MẬT KHẨU MỚI - TEST
 */

echo 'NEW_RESET PAGE LOADED';
echo '<br>Token: ' . ($_GET['token'] ?? 'No token');

require_once __DIR__ . '/../bootstrap.php';

// Debug - show if logged in
echo '<br>Logged in: ' . (isLoggedIn() ? 'YES' : 'NO');

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Kiểm tra token
$nguoiDungModel = new NguoiDungModel();
$user = $nguoiDungModel->findByResetToken($token);

if (!$user) {
    setFlashMessage('error', 'Link khôi phục mật khẩu không hợp lệ');
    redirect('login.php');
}

// Xử lý form đặt lại mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        // Cập nhật mật khẩu mới
        if ($nguoiDungModel->updatePassword($user['id'], $password)) {
            // Xóa token
            $nguoiDungModel->clearResetToken($user['id']);
            
            setFlashMessage('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập');
            redirect('login.php');
        } else {
            $error = 'Có lỗi xảy ra. Vui lòng thử lại';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Hệ thống QLĐT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; display: flex; align-items: center; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 20px 0; }
        .reset-container { max-width: 500px; margin: 0 auto; width: 100%; }
        .reset-card { background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1); padding: 40px 35px; border: 1px solid rgba(0, 0, 0, 0.05); }
        .reset-title { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 30px; text-align: center; }
        .form-label { font-size: 16px; font-weight: 600; color: #333; margin-bottom: 10px; display: block; }
        .mb-3 { margin-bottom: 20px; }
        .input-wrapper { position: relative; }
        .form-control { height: 55px; border: 1.5px solid #e1e5e9; border-radius: 8px; padding: 0 45px 0 15px; font-size: 15px; transition: all 0.3s ease; background: #fff; width: 100%; }
        .form-control:focus { border-color: #4285f4; box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1); outline: none; }
        .input-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #999; font-size: 20px; cursor: pointer; }
        .input-icon:hover { color: #666; }
        .btn-submit { width: 100%; height: 55px; background: #4285f4; border: none; border-radius: 8px; color: white; font-size: 16px; font-weight: 600; margin-top: 10px; transition: all 0.3s ease; }
        .btn-submit:hover { background: #3367d6; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3); }
        .alert { border-radius: 8px; margin-bottom: 20px; }
        @media (max-width: 768px) { .reset-container { max-width: 100%; padding: 0 20px; } .reset-card { padding: 30px 25px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <div class="reset-card">
                <h1 class="reset-title">Đặt lại mật khẩu</h1>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-circle"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" class="form-control" id="passwordField" placeholder="Nhập mật khẩu mới" required>
                            <i class="bi bi-eye-slash input-icon" onclick="togglePassword('passwordField', this)" id="toggleIcon1"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu</label>
                        <div class="input-wrapper">
                            <input type="password" name="confirm_password" class="form-control" id="confirmPasswordField" placeholder="Nhập lại mật khẩu mới" required>
                            <i class="bi bi-eye-slash input-icon" onclick="togglePassword('confirmPasswordField', this)" id="toggleIcon2"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-submit">Đặt lại mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }
    </script>
</body>
</html>
