<?php
/**
 * XÁC THỰC OTP
 * Bước 2: Nhập mã OTP để hoàn tất đăng ký
 */

require_once '../bootstrap.php';

if (isLoggedIn()) {
    redirect('../index.php');
}

if (!isset($_SESSION['registration_email'])) {
    redirect('auth/register.php');
}

$email = $_SESSION['registration_email'];
$error = getFlashMessage('error');
$success = getFlashMessage('success');
$devOTP = $_SESSION['dev_otp'] ?? null; // Lấy OTP trong dev mode

// Xử lý xác thực OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otpCode = sanitize($_POST['otp_code'] ?? '');

    if (empty($otpCode)) {
        $error = 'Vui lòng nhập mã OTP';
    } else {
        try {
            $otpModel = new OTPModel();
            $result = $otpModel->verifyOTP($email, $otpCode);

            if ($result['success']) {
                // Lấy dữ liệu đăng ký
                $data = $result['data'];
                $vaiTro = $result['vai_tro'];

                // Tạo tài khoản
                $vaiTroModel = new VaiTroModel();
                $vaiTroInfo = $vaiTroModel->findByMa($vaiTro);

                if (!$vaiTroInfo) {
                    $error = 'Vai trò không hợp lệ';
                } else {
                    $nguoiDungModel = new NguoiDungModel();
                    $nguoiDungId = $nguoiDungModel->createUser([
                        'email' => $data['email'],
                        'mat_khau' => $data['mat_khau'], // Đã được hash trong register.php
                        'ho_ten' => $data['ho_ten'],
                        'vai_tro_id' => $vaiTroInfo['id'],
                        'skip_hash' => true // Báo cho createUser không hash lại
                    ]);

                    // Tạo profile theo vai trò
                    switch ($vaiTro) {
                        case ROLE_GIANG_VIEN:
                            $gvModel = new GiangVienModel();
                            $gvModel->createProfile($nguoiDungId, $data['additional_data']);
                            break;

                        case ROLE_SINH_VIEN:
                            $svModel = new SinhVienModel();
                            $svModel->createProfile($nguoiDungId, $data['additional_data']);
                            break;

                        case ROLE_LANH_DAO:
                            $ldModel = new LanhDaoModel();
                            $ldModel->createProfile($nguoiDungId, $data['additional_data']);
                            break;
                    }

                    // Xóa session đăng ký
                    unset($_SESSION['registration_email']);
                    unset($_SESSION['dev_otp']);

                    // Chuyển đến trang đăng nhập
                    setFlashMessage('success', 'Đăng ký thành công Vui lòng đăng nhập');
                    redirect('auth/login.php');
                }
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực OTP - Hệ thống QLĐT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
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
        }

        .otp-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .otp-input {
            font-size: 2rem;
            text-align: center;
            letter-spacing: 1rem;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card otp-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                            <h3 class="mt-3">Xác thực OTP</h3>
                            <p class="text-muted">Mã OTP đã được gửi đến email:<br>
                                <strong><?= $email ?></strong>
                            </p>
                        </div>

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
                                <label class="form-label text-center d-block">Nhập mã OTP (<?= OTP_LENGTH ?> số)</label>
                                <input type="text" name="otp_code" class="form-control otp-input"
                                    maxlength="<?= OTP_LENGTH ?>" pattern="[0-9]{<?= OTP_LENGTH ?>}"
                                    placeholder="<?= str_repeat('•', OTP_LENGTH) ?>" required autofocus>
                                <small class="text-muted d-block text-center mt-2">
                                    Mã OTP có hiệu lực trong <?= OTP_EXPIRE_MINUTES ?> phút
                                </small>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 mb-3">
                                <i class="bi bi-check-circle"></i> Xác thực
                            </button>

                            <div class="text-center">
                                <p class="mb-0">
                                    <a href="login.php" class="text-decoration-none">
                                        <i class="bi bi-arrow-left"></i> Quay lại trang đăng nhập
                                    </a>
                                </p>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                <strong>Lưu ý:</strong> Kiểm tra cả hộp thư spam nếu không thấy email.
                                <?php
                                // Chỉ hiển thị mã OTP trong development mode hoặc khi có lỗi gửi email
                                $showOTP = false;
                                if ($success && (strpos($success, 'Không thể gửi email') !== false || strpos($success, 'Email failed') !== false)) {
                                    $showOTP = true;
                                }

                                if (DEVELOPMENT_MODE && $devOTP && $showOTP): ?>
                                    <br><br>
                                    <strong class="text-danger">Development Mode:</strong>
                                    Mã OTP của bạn là: <strong class="fs-5"><?= $devOTP ?></strong>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto format OTP input
        document.querySelector('.otp-input').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>

</html>