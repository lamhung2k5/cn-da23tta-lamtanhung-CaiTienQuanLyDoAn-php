<?php
/**
 * TRANG ĐỔI MẬT KHẨU
 */

require_once 'bootstrap.php';
requireLogin();

$user = getCurrentUser();
$pageTitle = 'Đổi mật khẩu';

$nguoiDungModel = new NguoiDungModel();
$userInfo = $nguoiDungModel->getUserFullInfo($user['id']);

$error = '';
$success = '';

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

<style>
body {
    overflow: hidden;
}

.change-password-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 110px 1rem 2rem 1rem;
    height: calc(100vh - 80px);
    overflow: hidden;
}

.change-password-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 2rem;
    overflow: hidden;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #000;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #003d82;
}

.password-form {
    max-width: 600px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 0.5rem;
}

.form-control {
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #003d82;
    box-shadow: 0 0 0 3px rgba(0, 61, 130, 0.1);
}

.password-requirements {
    background: #f8f9fa;
    border-left: 4px solid #003d82;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.password-requirements h6 {
    color: #003d82;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.password-requirements ul {
    margin: 0;
    padding-left: 1.2rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.password-requirements li {
    margin-bottom: 0.25rem;
}

.btn-group {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn-primary-custom {
    background: #003d82;
    border: 2px solid #003d82;
    color: white;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary-custom:hover {
    background: #002a5c;
    border-color: #002a5c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 61, 130, 0.3);
}

.btn-secondary-custom {
    background: white;
    border: 2px solid #e2e8f0;
    color: #4a5568;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-secondary-custom:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
    transform: translateY(-2px);
    color: #2d3748;
}

.security-info {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    text-align: center;
}

.security-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #003d82 0%, #0052a8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    box-shadow: 0 4px 12px rgba(0, 61, 130, 0.3);
}

.security-icon i {
    color: white;
    font-size: 24px;
}

.security-text {
    color: #4a5568;
    font-size: 0.95rem;
    line-height: 1.5;
}

.form-label-simple {
    display: block;
    font-size: 16px;
    font-weight: 500;
    color: #666;
    margin-bottom: 8px;
}

.password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.form-control-simple {
    width: 100%;
    padding: 12px 50px 12px 16px;
    border: 1px solid #000000ff;
    border-radius: 4px;
    font-size: 16px;
    background: white;
    transition: all 0.3s ease;
}

.form-control-simple:focus {
    outline: none;
    border-color: #000000ff;
    box-shadow: 0 0 0 1px rgba(0, 123, 255, 0.25);
}

.password-toggle {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    color: #666;
    font-size: 18px;
    z-index: 10;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #007bff;
}

.password-toggle i {
    pointer-events: none;
}

.btn-group-simple {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-agree {
    flex: 1;
    padding: 12px 24px;
    background: #4a5568;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-agree:hover {
    background: #2d3748;
}

.btn-cancel {
    flex: 1;
    padding: 12px 24px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #c82333;
    color: white;
}

.btn-secondary-custom{
    background-color: #dc3545; /* đỏ Bootstrap */
    color: #fff;
    padding: 0.5rem 1rem;
    text-decoration: none;
}

.btn-secondary-custom:hover{
    background-color: #bb2d3b;
    color: #fff;
}
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <?php 
                // Xác định vai trò hiện tại dựa trên referer hoặc tham số
                $currentRole = $userInfo['ma_vai_tro'];
                $referer = $_SERVER['HTTP_REFERER'] ?? '';
                
                // Nếu đến từ trang giảng viên hoặc có tham số mode=giang_vien
                if (strpos($referer, '/giang_vien/') !== false || ($_GET['mode'] ?? '') === 'giang_vien') {
                    $currentRole = 'giang_vien';
                }
                // Nếu đến từ trang lãnh đạo hoặc có tham số mode=lanh_dao
                elseif (strpos($referer, '/lanh_dao/') !== false || ($_GET['mode'] ?? '') === 'lanh_dao') {
                    $currentRole = 'lanh_dao';
                }
                // Nếu đến từ trang sinh viên hoặc có tham số mode=sinh_vien
                elseif (strpos($referer, '/sinh_vien/') !== false || ($_GET['mode'] ?? '') === 'sinh_vien') {
                    $currentRole = 'sinh_vien';
                }
                // Fallback: sử dụng vai trò từ session nếu không xác định được từ referer
                else {
                    $currentRole = $_SESSION['vai_tro'] ?? $userInfo['ma_vai_tro'];
                }
                ?>
                
                <?php if ($currentRole === 'sinh_vien'): ?>
                    <a class="nav-link" href="sinh_vien/dashboard.php">
                        <i class="bi bi-house-door"></i> Trang chủ
                    </a>
                    <a class="nav-link" href="sinh_vien/danh_sach_de_tai.php">
                        <i class="bi bi-journal-text"></i> Đề tài có thể đăng ký
                    </a>
                    <a class="nav-link" href="sinh_vien/de_tai_cua_toi.php">
                        <i class="bi bi-bookmark-check"></i> Đề tài của tôi
                    </a>
                    
                <?php elseif ($currentRole === 'giang_vien'): ?>
                    <div class="nav-section-title">QUẢN LÝ HỆ THỐNG</div>
                    <a class="nav-link" href="giang_vien/dashboard.php">
                        <i class="bi bi-house-door"></i> Trang chủ
                    </a>
                    <a class="nav-link" href="giang_vien/chon_loai_de_tai.php">
                        <i class="bi bi-plus-circle"></i> Tạo đề tài mới
                    </a>
                    <a class="nav-link" href="giang_vien/danh_sach_de_tai.php">
                        <i class="bi bi-journal-text"></i> Danh sách đề tài
                    </a>
                    <a class="nav-link" href="giang_vien/duyet_sinh_vien.php">
                        <i class="bi bi-person-add"></i> Duyệt sinh viên
                    </a>
                    <a class="nav-link" href="giang_vien/danh_sach_sinh_vien.php">
                        <i class="bi bi-people"></i> Sinh viên của tôi
                    </a>
                    
                    <div class="nav-section-title">CHỨC NĂNG LÃNH ĐẠO</div>
                    <a class="nav-link" href="lanh_dao/dashboard.php">
                        <i class="bi bi-shield-check"></i> Chế độ Lãnh đạo
                    </a>
                    
                <?php elseif ($currentRole === 'lanh_dao'): ?>
                    <div class="nav-section-title">QUẢN LÝ HỆ THỐNG</div>
                    <a class="nav-link" href="lanh_dao/dashboard.php">
                        <i class="bi bi-house-door"></i> Trang chủ
                    </a>
                    <a class="nav-link" href="lanh_dao/duyet_de_tai.php">
                        <i class="bi bi-journal-check"></i> Duyệt đề tài
                    </a>
                    <a class="nav-link" href="lanh_dao/danh_sach_phan_cong.php">
                        <i class="bi bi-person-check"></i> Phân công chấm
                    </a>
                    <a class="nav-link" href="lanh_dao/xuat_bao_cao.php">
                        <i class="bi bi-file-earmark-text"></i> Xuất danh sách
                    </a>
                    <a class="nav-link" href="lanh_dao/cai_dat_thong_so.php">
                        <i class="bi bi-gear"></i> Cài đặt thông số
                    </a>
                    <div class="nav-section-title">CHỨC NĂNG GIẢNG VIÊN</div>
                    <a class="nav-link" href="giang_vien/dashboard.php">
                        <i class="bi bi-person-workspace"></i> Chế độ Giảng viên
                    </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Main content -->
        <div class="col-md-10 p-4">
            <div class="change-password-container">
                <div class="change-password-card">
                    <h4 class="mb-4" style="font-weight: 700; color: #000;">
                        Đổi mật khẩu
                    </h4>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                        
                    <form method="POST" action="">
                        <div style="max-width: 600px; margin: auto;">
                            
                            <div class="mb-4">
                                <label class="form-label-simple">Mật khẩu cũ</label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="old_password" id="old_password"
                                           class="form-control-simple" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('old_password')">
                                        <i class="bi bi-eye" id="old_password_icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label-simple">Mật khẩu mới</label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="new_password" id="new_password"
                                           class="form-control-simple" 
                                           minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                        <i class="bi bi-eye" id="new_password_icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label-simple">Xác nhận lại mật khẩu</label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="confirm_password" id="confirm_password"
                                           class="form-control-simple" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                        <i class="bi bi-eye" id="confirm_password_icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="btn-group-simple">
                                <button type="submit" class="btn-agree">
                                    Đồng ý
                                </button>
                                <a href="profile.php?mode=<?= $currentRole ?>" class="btn-cancel">
                                    Hủy bỏ
                                </a>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        passwordField.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>
