<?php
/**
 * ĐĂNG KÝ TÀI KHOẢN
 * Bước 1: Nhập thông tin và gửi OTP
 */

require_once '../bootstrap.php';

$error = getFlashMessage('error');
$success = getFlashMessage('success');

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vaiTro = sanitize($_POST['vai_tro'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $hoTen = sanitize($_POST['ho_ten'] ?? '');
    $matKhau = $_POST['mat_khau'] ?? '';
    $xacNhanMatKhau = $_POST['xac_nhan_mat_khau'] ?? '';
    
    // Validate
    if (empty($vaiTro) || empty($email) || empty($hoTen) || empty($matKhau)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif (!isValidEmail($email)) {
        $error = 'Email không hợp lệ';
    } elseif (strlen($matKhau) < PASSWORD_MIN_LENGTH) {
        $error = 'Mật khẩu phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự';
    } elseif ($matKhau !== $xacNhanMatKhau) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        // Kiểm tra email đã tồn tại
        $nguoiDungModel = new NguoiDungModel();
        if ($nguoiDungModel->emailExists($email)) {
            $error = 'Email đã được sử dụng';
        } else {
            // Lấy thông tin bổ sung theo vai trò
            $additionalData = [];
            
            switch ($vaiTro) {
                case ROLE_GIANG_VIEN:
                    $additionalData = [
                        'ma_giang_vien' => sanitize($_POST['ma_giang_vien'] ?? ''),
                        'khoa' => sanitize($_POST['khoa'] ?? ''),
                        'chuyen_mon' => sanitize($_POST['chuyen_mon'] ?? ''),
                        'so_dien_thoai' => sanitize($_POST['so_dien_thoai'] ?? '')
                    ];
                    
                    if (empty($additionalData['ma_giang_vien'])) {
                        $error = 'Vui lòng nhập mã giảng viên';
                    } else {
                        $gvModel = new GiangVienModel();
                        if ($gvModel->maExists($additionalData['ma_giang_vien'])) {
                            $error = 'Mã giảng viên đã tồn tại';
                        }
                    }
                    break;
                    
                case ROLE_SINH_VIEN:
                    $additionalData = [
                        'ma_sinh_vien' => sanitize($_POST['ma_sinh_vien'] ?? ''),
                        'lop' => sanitize($_POST['lop'] ?? ''),
                        'khoa_hoc' => sanitize($_POST['khoa_hoc'] ?? ''),
                        'chuyen_nganh' => sanitize($_POST['chuyen_nganh'] ?? ''),
                        'so_dien_thoai' => sanitize($_POST['so_dien_thoai'] ?? '')
                    ];
                    
                    if (empty($additionalData['ma_sinh_vien'])) {
                        $error = 'Vui lòng nhập mã sinh viên';
                    } else {
                        $svModel = new SinhVienModel();
                        if ($svModel->mssvExists($additionalData['ma_sinh_vien'])) {
                            $error = 'Mã sinh viên đã tồn tại';
                        }
                    }
                    break;
                    
                case ROLE_LANH_DAO:
                    $additionalData = [
                        'ma_lanh_dao' => sanitize($_POST['ma_lanh_dao'] ?? ''),
                        'chuc_vu' => sanitize($_POST['chuc_vu'] ?? ''),
                        'khoa' => sanitize($_POST['khoa'] ?? ''),
                        'so_dien_thoai' => sanitize($_POST['so_dien_thoai'] ?? '')
                    ];
                    
                    if (empty($additionalData['ma_lanh_dao'])) {
                        $error = 'Vui lòng nhập mã lãnh đạo';
                    } else {
                        $ldModel = new LanhDaoModel();
                        if ($ldModel->maExists($additionalData['ma_lanh_dao'])) {
                            $error = 'Mã lãnh đạo đã tồn tại';
                        }
                    }
                    break;
            }
            
            if (!$error) {
                try {
                    // Tạo OTP
                    $otpModel = new OTPModel();
                    $registrationData = [
                        'email' => $email,
                        'ho_ten' => $hoTen,
                        'mat_khau' => hashPassword($matKhau), // Hash mật khẩu
                        'additional_data' => $additionalData
                    ];
                    
                    $otpResult = $otpModel->createOTP($email, $vaiTro, $registrationData);
                    
                    // Lưu session ngay lập tức
                    $_SESSION['registration_email'] = $email;
                    $_SESSION['dev_otp'] = $otpResult['otp_code']; // Luôn lưu để debug
                    
                    // Thử gửi email thật (luôn thử gửi)
                    $emailSent = false;
                    try {
                        $emailSent = sendOTPEmail($email, $otpResult['otp_code'], $hoTen);
                        if ($emailSent) {
                            error_log("OTP email sent successfully to: $email");
                        } else {
                            error_log("Failed to send OTP email to: $email");
                        }
                    } catch (Exception $e) {
                        // Ghi log lỗi
                        error_log("Email error: " . $e->getMessage());
                    }
                    
                    // Luôn cho phép tiếp tục (chỉ hiển thị mã khi email thất bại)
                    if ($emailSent) {
                        // Email gửi thành công - không hiển thị mã OTP
                        setFlashMessage('success', 'Mã OTP đã được gửi đến email của bạn');
                    } else {
                        // Email thất bại - hiển thị mã OTP để user có thể tiếp tục
                        if (DEVELOPMENT_MODE) {
                            setFlashMessage('warning', 'Không thể gửi email. Mã OTP của bạn là: <strong>' . $otpResult['otp_code'] . '</strong> (Development mode)');
                        } else {
                            setFlashMessage('warning', 'Không thể gửi email. Mã OTP của bạn là: <strong>' . $otpResult['otp_code'] . '</strong>');
                        }
                    }
                        
                        redirect('auth/verify_otp.php');
                    
                } catch (Exception $e) {
                    $error = 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }
    }
}

$vaiTroModel = new VaiTroModel();
$danhSachVaiTro = $vaiTroModel->getAllVaiTro();

$preSelectRole = $_GET['vai_tro'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Hệ thống QLĐT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-image: url('../img/back.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            padding: 10px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
        }
        
        .container {
            max-width: 900px;
        }
        
        .register-card {
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border: none;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.98);
        }
        
        .card-body {
            padding: 25px !important;
        }
        
        .text-center h3 {
            font-size: 22px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 5px;
        }
        
        .text-muted {
            color: #64748b !important;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 6px;
            display: block;
        }
        
        .form-control, .form-select {
            height: 44px;
            border: 1.5px solid #f1f5f9;
            border-radius: 10px;
            padding: 0 14px;
            font-size: 14px;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12), 
                        0 4px 12px rgba(99, 102, 241, 0.15);
            outline: none;
            background: #ffffff;
            transform: translateY(-1px);
        }
        
        .form-control:hover, .form-select:hover {
            border-color: #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .btn-primary {
            height: 48px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
        }
        
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        .additional-fields {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: rgba(248, 250, 252, 0.8);
            border-radius: 10px;
            border: 1px solid #f1f5f9;
        }
        
        .additional-fields h5 {
            color: #334155;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .text-danger {
            color: #dc2626 !important;
        }
        
        .bi-person-plus-fill {
            color: #6366f1 !important;
            font-size: 2.8rem !important;
            margin-bottom: 10px;
        }
        
        a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        a:hover {
            color: #5b21b6;
            text-decoration: underline;
        }
        
        hr {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 20px 0;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 30px 20px !important;
            }
            
            .container {
                padding: 0 15px;
            }
            
            .text-center h3 {
                font-size: 24px;
            }
            
            .form-control, .form-select {
                height: 48px;
            }
            
            .btn-primary {
                height: 50px;
            }
        }
        
        /* Giảm khoảng cách giữa các form groups */
        .mb-3 {
            margin-bottom: 12px !important;
        }
        
        .mt-3 {
            margin-top: 12px !important;
        }
        
        .py-2 {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card register-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-plus-fill text-primary" style="font-size: 3rem;"></i>
                            <h3 class="mt-3">Đăng ký tài khoản</h3>
                            <p class="text-muted">Tạo tài khoản mới trong hệ thống</p>
                            <div class="mt-2">
                                <a href="import_sinh_vien.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-upload"></i> Import sinh viên
                                </a>
                                <a href="import_giang_vien.php" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-upload"></i> Import giảng viên
                                </a>
                            </div>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-circle"></i> <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="registerForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                    <select name="vai_tro" id="vaiTro" class="form-select" required>
                                        <option value="">-- Chọn vai trò --</option>
                                        <?php foreach ($danhSachVaiTro as $vt): ?>
                                            <option value="<?= $vt['ma_vai_tro'] ?>" <?= $preSelectRole === $vt['ma_vai_tro'] ? 'selected' : '' ?>><?= $vt['ten_vai_tro'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" name="ho_ten" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" name="mat_khau" class="form-control" 
                                           minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" name="xac_nhan_mat_khau" class="form-control" required>
                                </div>
                            </div>

                            <!-- Giảng viên fields -->
                            <div id="giangVienFields" class="additional-fields">
                                <hr>
                                <h5>Thông tin giảng viên</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Mã giảng viên <span class="text-danger">*</span></label>
                                        <input type="text" name="ma_giang_vien" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Khoa</label>
                                        <input type="text" name="khoa" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Chuyên môn</label>
                                        <input type="text" name="chuyen_mon" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Số điện thoại</label>
                                        <input type="text" name="so_dien_thoai" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- Sinh viên fields -->
                            <div id="sinhVienFields" class="additional-fields">
                                <hr>
                                <h5>Thông tin sinh viên</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Mã sinh viên <span class="text-danger">*</span></label>
                                        <input type="text" name="ma_sinh_vien" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Lớp</label>
                                        <input type="text" name="lop" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Khóa học</label>
                                        <input type="text" name="khoa_hoc" class="form-control" placeholder="VD: 2021-2025">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Chuyên ngành</label>
                                        <input type="text" name="chuyen_nganh" class="form-control">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="so_dien_thoai" class="form-control">
                                </div>
                            </div>

                            <!-- Lãnh đạo fields -->
                            <div id="lanhDaoFields" class="additional-fields">
                                <hr>
                                <h5>Thông tin lãnh đạo</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Mã lãnh đạo <span class="text-danger">*</span></label>
                                        <input type="text" name="ma_lanh_dao" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Chức vụ</label>
                                        <input type="text" name="chuc_vu" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Khoa</label>
                                        <input type="text" name="khoa" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Số điện thoại</label>
                                        <input type="text" name="so_dien_thoai" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mt-3">
                                <i class="bi bi-send"></i> Gửi mã OTP
                            </button>

                            <div class="text-center mt-3">
                                <p class="mb-0">Đã có tài khoản? 
                                    <a href="login.php" class="text-decoration-none">Đăng nhập</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showRoleFields(selectedRole) {
            document.querySelectorAll('.additional-fields').forEach(el => {
                el.style.display = 'none';
                el.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
            });
            
            if (selectedRole === 'giang_vien') {
                let fields = document.getElementById('giangVienFields');
                fields.style.display = 'block';
                fields.querySelector('[name="ma_giang_vien"]').setAttribute('required', 'required');
            } else if (selectedRole === 'sinh_vien') {
                let fields = document.getElementById('sinhVienFields');
                fields.style.display = 'block';
                fields.querySelector('[name="ma_sinh_vien"]').setAttribute('required', 'required');
            } else if (selectedRole === 'lanh_dao') {
                let fields = document.getElementById('lanhDaoFields');
                fields.style.display = 'block';
                fields.querySelector('[name="ma_lanh_dao"]').setAttribute('required', 'required');
            }
        }
        
        document.getElementById('vaiTro').addEventListener('change', function() {
            showRoleFields(this.value);
        });
        
        // Auto show fields if role is pre-selected from URL
        const preSelectRole = '<?= $preSelectRole ?>';
        if (preSelectRole) {
            showRoleFields(preSelectRole);
        }
    </script>
</body>
</html>
