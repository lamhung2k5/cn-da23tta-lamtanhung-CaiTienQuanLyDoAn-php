<?php
/**
 * TRANG DOI MAT KHAU
 * Giao diện giống forgot_password.php
 */

session_start();

require_once __DIR__ . '/../bootstrap.php';

$error = '';
$success = '';
$token = '';

// Lấy token từ URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $_SESSION['reset_token'] = $token;
} elseif (isset($_SESSION['reset_token'])) {
    $token = $_SESSION['reset_token'];
}

// Khởi tạo model
$nguoiDungModel = new NguoiDungModel();
$user = null;

// Kiểm tra token nếu có
if (!empty($token)) {
    $sql = "SELECT * FROM nguoi_dung WHERE reset_token = :token";
    $user = $nguoiDungModel->queryOne($sql, ['token' => $token]);
}

// Xử lý form đặt lại mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $token = $_SESSION['reset_token'] ?? '';
    
    if (!empty($token)) {
        $sql = "SELECT * FROM nguoi_dung WHERE reset_token = :token";
        $user = $nguoiDungModel->queryOne($sql, ['token' => $token]);
    }
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (!$user) {
        $error = 'Link khôi phục mật khẩu không hợp lệ hoặc đã hết hạn';
    } else {
        if ($nguoiDungModel->updatePassword($user['id'], $password)) {
            $nguoiDungModel->clearResetToken($user['id']);
            unset($_SESSION['reset_token']);
            $_SESSION['success'] = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập';
            header('Location: login.php');
            exit;
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
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            background-image: url('../img/back.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px 0;
        }
        
        .reset-container {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }
        
        .reset-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px 35px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .reset-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-label {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: block;
            text-align: left;
        }
        
        .form-control {
            height: 55px;
            border: 1.5px solid #e1e5e9;
            border-radius: 8px;
            padding: 0 15px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fff;
            width: 100%;
        }
        
        .form-control:focus {
            border-color: #4285f4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #bbb;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: 18px;
            z-index: 10;
        }
        
        .input-group-text:hover {
            color: #4285f4;
        }
        
        .btn-submit {
            width: 100%;
            height: 55px;
            background: #4285f4;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: #3367d6;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
            color: white;
        }
        
        .back-to-login {
            text-align: left;
            margin-top: 20px;
        }
        
        .back-to-login a {
            color: #4285f4;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-to-login a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: #fee;
            color: #c00;
            border-left: 4px solid #c00;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .password-strength {
            margin-top: 8px;
            height: 4px;
            border-radius: 2px;
            background: #e8e8e8;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak { width: 33%; background: #dc3545; }
        .strength-medium { width: 66%; background: #ffc107; }
        .strength-strong { width: 100%; background: #198754; }
        
        @media (max-width: 768px) {
            .reset-container {
                max-width: 100%;
                padding: 0 20px;
            }
            
            .reset-card {
                padding: 30px 25px;
            }
            
            .reset-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <h1 class="reset-title">Đặt lại mật khẩu</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!$user && !empty($token)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Token không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu gửi lại link khôi phục mật khẩu.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label">Mật khẩu mới</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" 
                               id="passwordField" placeholder="Nhập mật khẩu mới" required
                               onkeyup="checkPasswordStrength(this.value)">
                        <span class="input-group-text" onclick="togglePassword('passwordField', this)">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Xác nhận mật khẩu</label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" class="form-control" 
                               id="confirmPasswordField" placeholder="Nhập lại mật khẩu mới" required>
                        <span class="input-group-text" onclick="togglePassword('confirmPasswordField', this)">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-submit">
                    <i class="bi bi-check-circle me-2"></i>Đặt lại mật khẩu
                </button>
            </form>
            
            <div class="back-to-login">
                <a href="login.php">
                    <i class="bi bi-arrow-left"></i>
                    Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            const iconEl = icon.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                iconEl.classList.remove('bi-eye-slash');
                iconEl.classList.add('bi-eye');
            } else {
                field.type = 'password';
                iconEl.classList.remove('bi-eye');
                iconEl.classList.add('bi-eye-slash');
            }
        }
        
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            strengthBar.className = 'password-strength-bar';
            
            if (password.length === 0) {
                strengthBar.style.width = '0';
            } else if (password.length < 6) {
                strengthBar.classList.add('strength-weak');
            } else if (password.length >= 6 && password.length < 10) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        }
    </script>
</body>
</html>
