<?php
/**
 * TRANG CẬP NHẬT MÃ SINH VIÊN/GIẢNG VIÊN/LÃNH ĐẠO
 * Dành cho người dùng đăng nhập OAuth mà chưa có mã chính thức
 */

require_once '../bootstrap.php';
requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

// Kiểm tra xem user có cần cập nhật mã không
$needsUpdate = false;
$currentCode = '';
$codeType = '';

$db = Database::getInstance()->getConnection();

switch ($user['vai_tro']) {
    case ROLE_SINH_VIEN:
        $profile = $db->prepare("SELECT ma_sinh_vien FROM sinh_vien WHERE nguoi_dung_id = ?");
        $profile->execute([$user['id']]);
        $result = $profile->fetch();
        if ($result && strpos($result['ma_sinh_vien'], 'OAUTH_') === 0) {
            $needsUpdate = true;
            $currentCode = $result['ma_sinh_vien'];
            $codeType = 'Mã sinh viên';
        }
        break;

    case ROLE_GIANG_VIEN:
        $profile = $db->prepare("SELECT ma_giang_vien FROM giang_vien WHERE nguoi_dung_id = ?");
        $profile->execute([$user['id']]);
        $result = $profile->fetch();
        if ($result && strpos($result['ma_giang_vien'], 'OAUTH_') === 0) {
            $needsUpdate = true;
            $currentCode = $result['ma_giang_vien'];
            $codeType = 'Mã giảng viên';
        }
        break;

    case ROLE_LANH_DAO:
        $profile = $db->prepare("SELECT ma_lanh_dao FROM lanh_dao WHERE nguoi_dung_id = ?");
        $profile->execute([$user['id']]);
        $result = $profile->fetch();
        if ($result && strpos($result['ma_lanh_dao'], 'OAUTH_') === 0) {
            $needsUpdate = true;
            $currentCode = $result['ma_lanh_dao'];
            $codeType = 'Mã lãnh đạo';
        }
        break;
}

// Nếu không cần cập nhật, chuyển về dashboard
if (!$needsUpdate) {
    switch ($user['vai_tro']) {
        case ROLE_SINH_VIEN:
            redirect('sinh_vien/dashboard.php');
            break;
        case ROLE_GIANG_VIEN:
            redirect('giang_vien/dashboard.php');
            break;
        case ROLE_LANH_DAO:
            redirect('lanh_dao/dashboard.php');
            break;
        default:
            redirect('index.php');
    }
}

// Xử lý cập nhật mã
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newCode = sanitize($_POST['new_code'] ?? '');

    if (empty($newCode)) {
        $error = 'Vui lòng nhập mã mới';
    } else {
        try {
            $updated = false;

            switch ($user['vai_tro']) {
                case ROLE_SINH_VIEN:
                    // Kiểm tra mã sinh viên đã tồn tại chưa
                    $check = $db->prepare("SELECT id FROM sinh_vien WHERE ma_sinh_vien = ? AND nguoi_dung_id != ?");
                    $check->execute([$newCode, $user['id']]);
                    if ($check->fetch()) {
                        $error = 'Mã sinh viên đã tồn tại';
                    } else {
                        $update = $db->prepare("UPDATE sinh_vien SET ma_sinh_vien = ? WHERE nguoi_dung_id = ?");
                        $updated = $update->execute([$newCode, $user['id']]);
                    }
                    break;

                case ROLE_GIANG_VIEN:
                    // Kiểm tra mã giảng viên đã tồn tại chưa
                    $check = $db->prepare("SELECT id FROM giang_vien WHERE ma_giang_vien = ? AND nguoi_dung_id != ?");
                    $check->execute([$newCode, $user['id']]);
                    if ($check->fetch()) {
                        $error = 'Mã giảng viên đã tồn tại';
                    } else {
                        $update = $db->prepare("UPDATE giang_vien SET ma_giang_vien = ? WHERE nguoi_dung_id = ?");
                        $updated = $update->execute([$newCode, $user['id']]);
                    }
                    break;

                case ROLE_LANH_DAO:
                    // Kiểm tra mã lãnh đạo đã tồn tại chưa
                    $check = $db->prepare("SELECT id FROM lanh_dao WHERE ma_lanh_dao = ? AND nguoi_dung_id != ?");
                    $check->execute([$newCode, $user['id']]);
                    if ($check->fetch()) {
                        $error = 'Mã lãnh đạo đã tồn tại';
                    } else {
                        $update = $db->prepare("UPDATE lanh_dao SET ma_lanh_dao = ? WHERE nguoi_dung_id = ?");
                        $updated = $update->execute([$newCode, $user['id']]);
                    }
                    break;
            }

            if ($updated) {
                $success = 'Cập nhật mã thành công';
                // Chuyển hướng sau 2 giây
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '" . getDashboardUrl($user['vai_tro']) . "';
                    }, 2000);
                </script>";
            } elseif (empty($error)) {
                $error = 'Không thể cập nhật mã. Vui lòng thử lại.';
            }

        } catch (Exception $e) {
            $error = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

function getDashboardUrl($role)
{
    switch ($role) {
        case ROLE_SINH_VIEN:
            return '../sinh_vien/dashboard.php';
        case ROLE_GIANG_VIEN:
            return '../giang_vien/dashboard.php';
        case ROLE_LANH_DAO:
            return '../lanh_dao/dashboard.php';
        default:
            return '../index.php';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật mã - Hệ thống QLĐT</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .update-container {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .update-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
        }

        .update-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
            text-align: center;
        }

        .update-subtitle {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.5;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            height: 50px;
            border: 1.5px solid #f1f5f9;
            border-radius: 12px;
            padding: 0 16px;
            font-size: 15px;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12),
                0 4px 12px rgba(99, 102, 241, 0.15);
            outline: none;
            background: #ffffff;
            transform: translateY(-1px);
        }

        .btn-update {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 500;
            margin-top: 20px;
            margin-bottom: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }

        .btn-update::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn-update:hover::before {
            left: 100%;
        }

        .btn-update:hover {
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
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

        .alert-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #16a34a;
            border-left: 4px solid #16a34a;
        }

        .current-code {
            background: rgba(248, 250, 252, 0.8);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #f59e0b;
        }

        .current-code-label {
            font-size: 13px;
            color: #92400e;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .current-code-value {
            font-size: 16px;
            color: #92400e;
            font-weight: 500;
            font-family: 'Courier New', monospace;
        }

        .icon-warning {
            color: #f59e0b;
            font-size: 48px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="update-container">
            <div class="update-card">
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle icon-warning"></i>
                    <div class="update-title">Cập nhật <?= $codeType ?></div>
                    <div class="update-subtitle">
                        Bạn đang sử dụng mã tạm thời. Vui lòng cập nhật mã chính thức để tiếp tục sử dụng hệ thống.
                    </div>
                </div>

                <div class="current-code">
                    <div class="current-code-label">Mã hiện tại (tạm thời):</div>
                    <div class="current-code-value"><?= htmlspecialchars($currentCode) ?></div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?= $success ?>
                        <br><small>Đang chuyển hướng...</small>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label"><?= $codeType ?> mới <span class="text-danger">*</span></label>
                        <input type="text" name="new_code" class="form-control"
                            placeholder="Nhập <?= strtolower($codeType) ?> chính thức"
                            value="<?= $_POST['new_code'] ?? '' ?>" required>
                        <small class="text-muted">
                            Nhập <?= strtolower($codeType) ?> chính thức của bạn để thay thế mã tạm thời.
                        </small>
                    </div>

                    <button type="submit" class="btn-update">
                        <i class="bi bi-check-circle"></i> Cập nhật mã
                    </button>
                </form>

                <div class="text-center">
                    <a href="<?= getDashboardUrl($user['vai_tro']) ?>" class="text-decoration-none"
                        style="color: #6366f1;">
                        <i class="bi bi-arrow-left"></i> Bỏ qua (sử dụng mã tạm thời)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>