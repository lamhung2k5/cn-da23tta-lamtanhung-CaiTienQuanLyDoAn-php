<?php
/**
 * TRANG CÀI ĐẶT
 */

require_once 'bootstrap.php';
requireLogin();

$user = getCurrentUser();
$pageTitle = 'Cài đặt';

$nguoiDungModel = new NguoiDungModel();
$error = '';
$success = '';

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        $error = 'Mật khẩu mới phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        $result = $nguoiDungModel->changePassword($user['id'], $oldPassword, $newPassword);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Cài đặt tài khoản</h5>
                </div>
                <div class="card-body">
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

                    <!-- Đổi mật khẩu -->
                    <h5 class="mb-3"><i class="bi bi-key"></i> Đổi mật khẩu</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" 
                                   minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
                            <small class="text-muted">Tối thiểu <?= PASSWORD_MIN_LENGTH ?> ký tự</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="javascript:history.back()" class="btn text-white" style="background-color: #0d6efd;">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Đổi mật khẩu
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- Thông tin tài khoản -->
                    <h5 class="mb-3"><i class="bi bi-info-circle"></i> Thông tin tài khoản</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td width="200"><strong>Email:</strong></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Họ tên:</strong></td>
                            <td><?= htmlspecialchars($user['ho_ten']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Vai trò:</strong></td>
                            <td>
                                <?php
                                $roleLabels = [
                                    'giang_vien' => 'Giảng viên',
                                    'sinh_vien' => 'Sinh viên',
                                    'lanh_dao' => 'Lãnh đạo'
                                ];
                                echo $roleLabels[$user['vai_tro']] ?? $user['vai_tro'];
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
