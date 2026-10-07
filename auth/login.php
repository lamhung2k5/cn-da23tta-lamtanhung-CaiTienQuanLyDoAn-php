<?php
/**
 * ĐĂNG NHẬP THỐNG NHẤT
 * Hỗ trợ tất cả các vai trò: Sinh viên, Giảng viên, Lãnh đạo
 */

require_once '../bootstrap.php';

// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    redirect('index.php');
}

// Tạo CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = getFlashMessage('error');
$success = getFlashMessage('success');

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Token bảo mật không hợp lệ';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role_filter = $_POST['role_filter'] ?? ''; // Lọc theo vai trò nếu có

        if (empty($email) || empty($password)) {
            $error = 'Vui lòng nhập đầy đủ thông tin';
        } else {
            $nguoiDungModel = new NguoiDungModel();
            $result = $nguoiDungModel->login($email, $password);

            if ($result['success']) {
                // Kiểm tra vai trò nếu có lọc
                if (!empty($role_filter) && $result['user']['vai_tro'] !== $role_filter) {
                    $error = 'Tài khoản này không có quyền truy cập';
                } else {
                    // Regenerate session ID để bảo mật
                    session_regenerate_id(true);

                    // Lưu session
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['email'] = $result['user']['email'];
                    $_SESSION['ho_ten'] = $result['user']['ho_ten'];
                    $_SESSION['vai_tro'] = $result['user']['vai_tro'];
                    $_SESSION['vai_tro_id'] = $result['user']['vai_tro_id'];
                    $_SESSION['profile_id'] = $result['user']['profile_id'];
                    $_SESSION['login_time'] = time(); // Thêm thời gian đăng nhập

                    // Lưu ngữ cảnh đăng nhập từ URL parameter
                    $context = $_GET['context'] ?? '';
                    if (in_array($context, ['co_so_nganh', 'chuyen_nganh'])) {
                        $_SESSION['he_dao_tao'] = $context;
                    }

                    // Tạo CSRF token mới
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    // Chuyển hướng theo vai trò
                    switch ($result['user']['vai_tro']) {
                        case ROLE_GIANG_VIEN:
                            redirect('giang_vien/dashboard.php');
                            break;
                        case ROLE_SINH_VIEN:
                            //unset($_SESSION['vai_tro']);
                            redirect('sinh_vien/dashboard.php');
                            break;
                        case ROLE_LANH_DAO:
                            redirect('lanh_dao/dashboard.php');
                            break;
                        default:
                            redirect('index.php');
                    }
                }
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Lấy thông tin context từ URL nếu có
$context = $_GET['context'] ?? '';
$role_filter = $_GET['role'] ?? '';
$page_title = 'Đăng nhập';
$page_subtitle = 'Hệ thống quản lý đồ án';
$theme_color = '#4285f4'; // Mặc định
$context_badge = '';
$context_icon = '';

// Tùy chỉnh giao diện theo context
switch ($context) {
    case 'co_so_nganh':
        $page_title = 'Đăng nhập Cơ sở ngành';
        $theme_color = '#28a745'; // Xanh lá
        $context_badge = 'Cơ sở ngành';
        $context_icon = 'bi-book';
        break;
    case 'chuyen_nganh':
        $page_title = 'Đăng nhập Chuyên ngành';
        $theme_color = '#ffc107'; // Vàng
        $context_badge = 'Chuyên ngành';
        $context_icon = 'bi-award';
        break;
    default:
        // Tùy chỉnh theo vai trò như cũ
        switch ($role_filter) {
            case ROLE_SINH_VIEN:
                $page_title = 'Đăng nhập Sinh viên';
                $theme_color = '#28a745'; // Xanh lá
                break;
            case ROLE_GIANG_VIEN:
                $page_title = 'Đăng nhập Giảng viên';
                $theme_color = '#007bff'; // Xanh dương
                break;
            case ROLE_LANH_DAO:
                $page_title = 'Đăng nhập Lãnh đạo';
                $theme_color = '#ffc107'; // Vàng
                break;
        }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Hệ thống QLĐT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo.png">

    <style>
        :root {
            --theme-color:
                <?= $theme_color ?>
            ;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-image: url('../img/back.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .login-container {
            max-width: 480px;
            margin: 0 auto;
            width: 100%;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 35px 40px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 25px;
            position: relative;
        }

        .logo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 12px;
        }

        .logo-wrapper::before {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.4), rgba(168, 85, 247, 0.4));
            filter: blur(8px);
            z-index: 0;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .logo-wrapper:hover::before {
            opacity: 1;
            filter: blur(12px);
            transform: scale(1.05);
        }

        .logo-img {
            width: 76px;
            height: 76px;
            position: relative;
            z-index: 1;
            border-radius: 50%;
            background: #ffffff;
            padding: 4px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            transition: transform 0.3s ease;
        }

        .logo-wrapper:hover .logo-img {
            transform: scale(1.03);
        }

        .login-title {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #0f172a 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-subtitle {
            font-size: 13.5px;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .faculty-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(99, 102, 241, 0.08);
            color: #4f46e5;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            border: 1px solid rgba(99, 102, 241, 0.15);
            margin-top: 4px;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.05);
        }

        /* Form Labels */
        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 6px;
            display: block;
            text-align: left;
            transition: all 0.3s ease;
        }

        .form-control {
            height: 48px;
            border: 1.5px solid #f1f5f9;
            border-radius: 25px !important;
            padding: 0 18px;
            font-size: 15px;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            width: 100% !important;
            box-sizing: border-box;
            display: block;
            max-width: 100%;
            color: #0f172a !important;
            line-height: 1.5;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12),
                0 4px 12px rgba(99, 102, 241, 0.15);
            outline: none;
            background: #ffffff;
            color: #0f172a !important;
            transform: translateY(-1px);
        }

        .form-control:hover {
            border-color: #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            background: #ffffff;
            color: #0f172a !important;
        }

        .form-control::placeholder {
            color: #64748b !important;
            font-weight: 400;
            transition: all 0.3s ease;
        }

        .form-control:focus::placeholder {
            color: #94a3b8 !important;
            transform: translateX(4px);
        }

        .input-group {
            position: relative;
            margin-bottom: 10px;
            display: block;
            width: 100%;
        }

        .input-group .form-control {
            width: 100% !important;
            display: block !important;
        }

        .input-group input[type="email"],
        .input-group input[type="password"] {
            border-radius: 25px !important;
        }

        .input-group .input-icon {
            top: calc(50% + 16px);
        }

        .input-group input[type="password"],
        .input-group input[type="text"]#passwordField {
            padding-right: 45px;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #999;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0;
        }

        .password-toggle {
            cursor: pointer;
            user-select: none;
            color: #64748b;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 6px;
            border-radius: 6px;
            position: relative;
        }

        .password-toggle::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 6px;
            background: transparent;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #6366f1;
            transform: scale(1.05);
        }

        .password-toggle:hover::before {
            background: rgba(99, 102, 241, 0.08);
        }

        .btn-login {
            width: 100%;
            height: 46px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 15px;
            font-weight: 500;
            margin-top: 16px;
            margin-bottom: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
            transition: all 0.1s ease;
        }

        .forgot-password-inline {
            color: #4285f4;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-password-inline:hover {
            text-decoration: underline;
            color: #4285f4;
        }

        .divider {
            text-align: center;
            margin: 12px 0 16px 0;
            position: relative;
            color: #666;
            font-size: 13px;
            font-weight: 500;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1e5e9;
            z-index: 1;
        }

        .divider span {
            background: white;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        .social-login {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .btn-social {
            flex: 1;
            height: 44px;
            border: 1.5px solid #f1f5f9;
            border-radius: 12px;
            background: #ffffff;
            color: #374151;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .btn-social::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.02), transparent);
            transition: left 0.4s ease;
        }

        .btn-social:hover::before {
            left: 100%;
        }

        .btn-social:hover {
            background: #fafafa;
            border-color: #e2e8f0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-social.google:hover {
            color: #dc2626;
            border-color: #fecaca;
            background: #fef2f2;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }

        .btn-social.github:hover {
            color: #1f2937;
            border-color: #d1d5db;
            background: #f9fafb;
            box-shadow: 0 4px 12px rgba(31, 41, 55, 0.15);
        }

        .btn-social:active {
            transform: translateY(0);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.1s ease;
        }

        .social-icon {
            width: 20px;
            height: 20px;
        }

        .btn-back {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #6366f1;
            border-color: #6366f1;
            color: white;
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .remember-me {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            accent-color: #4285f4;
        }

        .remember-me label {
            font-size: 14px;
            color: #666;
            margin: 0;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .login-container {
                max-width: 100%;
                padding: 0 20px;
            }

            .login-card {
                padding: 30px 25px;
            }

            .social-login {
                flex-direction: column;
            }

            .btn-social {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <!-- Nút quay lại -->
                <div class="mb-3">
                    <a href="../index.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>
                <div class="logo-container">
                    <div class="logo-wrapper">
                        <img src="../assets/images/logo.png" alt="TVU Logo" class="logo-img">
                    </div>
                    <div class="login-title"><?= $page_title ?></div>
                    <?php if ($context_badge): ?>
                        <div class="specialty-badge"
                            style="background: linear-gradient(135deg, <?= $theme_color ?> 0%, <?= $theme_color ?>cc 100%); color: white; padding: 6px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin: 8px 0 12px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            <i class="<?= $context_icon ?>"></i>
                            <?= $context_badge ?>
                        </div>
                    <?php endif; ?>
                    <div class="login-subtitle"><?= $page_subtitle ?></div>
                    <div>
                        <span class="faculty-tag">
                            <i class="bi bi-mortarboard-fill"></i> Khoa Công Nghệ Thông Tin
                        </span>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $error ?>
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
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <!-- Role filter nếu có -->
                    <?php if (!empty($role_filter)): ?>
                        <input type="hidden" name="role_filter" value="<?= $role_filter ?>">
                    <?php endif; ?>

                    <div class="input-group">
                        <label class="form-label">Tài khoản</label>
                        <input type="email" name="email" class="form-control" placeholder="" value="<?= $email ?? '' ?>"
                            required>
                    </div>

                    <div class="input-group">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Mật khẩu</label>
                           <!-- <a href="forgot_password.php" class="forgot-password-inline">Quên mật khẩu</a>-->
                        </div>
                        <input type="password" name="password" class="form-control" placeholder="" id="passwordField"
                            required>
                        <i class="bi bi-eye-slash input-icon password-toggle" onclick="togglePassword()"
                            id="toggleIcon"></i>
                    </div>

                    <button type="submit" class="btn btn-login">Đăng nhập</button>

                    <!--
                    <div class="divider">
                        <span>Hoặc đăng nhập với</span>
                    </div>

                    <div class="social-login">
                        <a href="google_login.php" class="btn-social google">
                            <svg class="social-icon" viewBox="0 0 24 24">
                                <path fill="#4285F4"
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                <path fill="#34A853"
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                <path fill="#FBBC05"
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                <path fill="#EA4335"
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                            </svg>
                            Google
                        </a>
                        <a href="github_login.php" class="btn-social github">
                            <svg class="social-icon" viewBox="0 0 24 24">
                                <path fill="#333"
                                    d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                            </svg>
                            GitHub
                        </a>
                    </div>
-->
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('passwordField');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        }
    </script>
</body>

</html>