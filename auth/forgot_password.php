<?php
/**
 * QUÊN MẬT KHẨU
 */

require_once '../bootstrap.php';

// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// Xử lý form quên mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $captcha_input = $_POST['captcha'] ?? '';
    $captcha_session = $_SESSION['captcha'] ?? '';
    
    if (empty($email)) {
        $error = 'Vui lòng nhập email';
    } elseif (empty($captcha_input)) {
        $error = 'Vui lòng nhập mã xác thực';
    } elseif (strtolower($captcha_input) !== strtolower($captcha_session)) {
        $error = 'Mã xác thực không đúng';
    } else {
        $nguoiDungModel = new NguoiDungModel();
        $user = $nguoiDungModel->findByEmail($email);
        
        if ($user) {
            // Tạo token reset password
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Lưu token vào database
            $nguoiDungModel->saveResetToken($user['id'], $token, $expiry);
            
            // Debug - hiển thị token
            error_log("Reset token for " . $user['email'] . ": " . $token);
            
            // Tạo link reset password
            $reset_link = BASE_URL . "auth/dat-lai-mat-khau.php?token=" . $token;
            
            // Gửi email
            if (sendResetPasswordEmail($user['email'], $user['ho_ten'], $reset_link)) {
                // Chuyển thẳng đến trang đặt lại mật khẩu
                $_SESSION['reset_token'] = $token;
                header('Location: ' . BASE_URL . 'auth/dat-lai-mat-khau.php?token=' . $token);
                exit;
            } else {
                $error = 'Không thể gửi email. Vui lòng kiểm tra cấu hình email hoặc thử lại sau.';
            }
        } else {
            $error = 'Email không tồn tại trong hệ thống';
        }
    }
}

// Tạo captcha mới
$captcha = generateCaptcha();
$_SESSION['captcha'] = $captcha;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Hệ thống QLĐT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Favicon -->
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
        
        .forgot-container {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }
        
        .forgot-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px 35px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .forgot-title {
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
        
        .captcha-group {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 25px;
        }
        
        .captcha-input {
            flex: 1;
        }
        
        .captcha-display {
            width: 200px;
            height: 55px;
            background: #e8f5e9;
            border: 1.5px solid #e1e5e9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 8px;
            color: #333;
            font-family: 'Courier New', monospace;
            user-select: none;
        }
        
        .captcha-refresh-input {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: rgba(66, 133, 244, 0.1);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #4285f4;
        }
        
        .captcha-refresh-input:hover {
            background: rgba(66, 133, 244, 0.2);
            transform: translateY(-50%) rotate(180deg);
        }
        
        .captcha-refresh-input i {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
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
        
        @media (max-width: 768px) {
            .forgot-container {
                max-width: 100%;
                padding: 0 20px;
            }
            
            .forgot-card {
                padding: 30px 25px;
            }
            
            .captcha-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .captcha-display {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-container">
            <div class="forgot-card">
                <h1 class="forgot-title">Quên mật khẩu</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-circle"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               placeholder="Nhập email đăng ký trong hệ thống" 
                               value="<?= $email ?? '' ?>" required>
                    </div>

                    <div class="captcha-group">
                        <div class="captcha-input">
                            <label class="form-label">Mã xác thực</label>
                            <div style="position: relative;">
                                <input type="text" name="captcha" class="form-control" 
                                       placeholder="" required autocomplete="off" style="padding-right: 45px;">
                                <button type="button" class="captcha-refresh-input" onclick="refreshCaptcha()" title="Làm mới">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                        <div class="captcha-display" id="captchaDisplay">
                            <?= $captcha ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit">Lấy lại mật khẩu</button>
                </form>

                <div class="back-to-login">
                    <a href="login.php">
                        Quay lại đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function refreshCaptcha() {
            fetch('refresh_captcha.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('captchaDisplay').textContent = data;
                });
        }
    </script>
</body>
</html>

