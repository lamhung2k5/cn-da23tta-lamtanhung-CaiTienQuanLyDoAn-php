<?php
/**
 * TRANG CHỈNH SỬA HỒ SƠ CÁ NHÂN
 */

require_once 'bootstrap.php';
requireLogin();

$user = getCurrentUser();
$pageTitle = 'Chỉnh sửa hồ sơ';

$nguoiDungModel = new NguoiDungModel();

$error = '';
$success = '';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        
        if (empty($ho_ten)) {
            $error = 'Vui lòng nhập họ tên';
        } else {
            // Cập nhật thông tin cơ bản trong bảng nguoi_dung
            $updateData = [
                'ho_ten' => $ho_ten
            ];
            
            $result = $nguoiDungModel->update($user['id'], $updateData);
            
            if ($result) {
                // Lấy thông tin user hiện tại để xác định vai trò
                $currentUserInfo = $nguoiDungModel->getUserFullInfo($user['id']);
                
                // Cập nhật thông tin chi tiết theo vai trò
                if ($currentUserInfo['ma_vai_tro'] === 'giang_vien' && $currentUserInfo['profile']) {
                    $giangVienModel = new GiangVienModel();
                    $profileData = [
                        'ma_giang_vien' => trim($_POST['ma_giang_vien'] ?? ''),
                        'khoa' => trim($_POST['khoa'] ?? ''),
                        'chuyen_mon' => trim($_POST['chuyen_mon'] ?? ''),
                        'so_dien_thoai' => $so_dien_thoai
                    ];
                    $giangVienModel->update($currentUserInfo['profile']['id'], $profileData);
                    
                } elseif ($currentUserInfo['ma_vai_tro'] === 'sinh_vien' && $currentUserInfo['profile']) {
                    $sinhVienModel = new SinhVienModel();
                    $profileData = [
                        'lop' => trim($_POST['lop'] ?? ''),
                        'khoa_hoc' => trim($_POST['khoa_hoc'] ?? ''),
                        'chuyen_nganh' => trim($_POST['chuyen_nganh'] ?? ''),
                        'so_dien_thoai' => $so_dien_thoai
                    ];
                    $sinhVienModel->update($currentUserInfo['profile']['id'], $profileData);
                    
                } elseif ($currentUserInfo['ma_vai_tro'] === 'lanh_dao' && $currentUserInfo['profile']) {
                    $lanhDaoModel = new LanhDaoModel();
                    $profileData = [
                        'chuc_vu' => trim($_POST['chuc_vu'] ?? ''),
                        'khoa' => trim($_POST['khoa'] ?? ''),
                        'so_dien_thoai' => $so_dien_thoai
                    ];
                    $lanhDaoModel->update($currentUserInfo['profile']['id'], $profileData);
                }
                
                // Cập nhật session với tên mới
                $_SESSION['ho_ten'] = $ho_ten;
                if (isset($_SESSION['user'])) {
                    $_SESSION['user']['ho_ten'] = $ho_ten;
                }
                
                // Lưu thông báo thành công vào session
                $_SESSION['success_message'] = 'Cập nhật hồ sơ thành công';
                
                // Redirect để tránh resubmit và load lại dữ liệu mới
                $mode = $_GET['mode'] ?? $currentUserInfo['ma_vai_tro'];
                header("Location: edit-profile.php?mode=" . urlencode($mode));
                exit();
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật hồ sơ. Vui lòng thử lại.';
            }
        }
    } catch (Exception $e) {
        $error = 'Lỗi hệ thống: ' . $e->getMessage();
    }
}

// Kiểm tra thông báo thành công từ session
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Load thông tin user (sau khi cập nhật hoặc lần đầu load)
$userInfo = $nguoiDungModel->getUserFullInfo($user['id']);

include 'includes/header.php';
?>

<style>
body {
    overflow: hidden;
}
.edit-profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 47px 1rem 2rem 1rem;
    height: calc(100vh - 80px);
    overflow: hidden;
}

.edit-profile-card {
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

.info-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.info-value {
    font-size: 1rem;
    color: #000;
    font-weight: 500;
}

.info-card {
    background: #ffffff;
    border-left: 4px solid #003d82;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border: 1px solid #e3e6f0;
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
<div class="edit-profile-container">
    <div class="edit-profile-card">
        <h4 class="mb-4" style="font-weight: 700; color: #000;">
            Chỉnh sửa hồ sơ cá nhân
        </h4>
        
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
        
        <form method="POST" action="?mode=<?= htmlspecialchars($_GET['mode'] ?? $currentRole) ?>">
            <!-- Thông tin chung -->
            <div class="section-title">Thông tin chung</div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="ho_ten" class="form-control" 
                           value="<?= htmlspecialchars($userInfo['ho_ten']) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" 
                           value="<?= htmlspecialchars($userInfo['email']) ?>" disabled>
                </div>
            </div>
            
            <?php if ($userInfo['profile']): ?>
                <!-- Thông tin chi tiết -->
                <div class="section-title mt-4">Thông tin chi tiết</div>
                
                <?php if ($userInfo['ma_vai_tro'] === 'giang_vien'): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã giảng viên</label>
                            <input type="text" name="ma_giang_vien" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['ma_giang_vien']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khoa</label>
                            <input type="text" name="khoa" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['khoa']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chuyên môn</label>
                            <input type="text" name="chuyen_mon" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['chuyen_mon']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['so_dien_thoai'] ?? '') ?>">
                        </div>
                    </div>
                    
                <?php elseif ($userInfo['ma_vai_tro'] === 'sinh_vien'): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã sinh viên</label>
                            <input type="text" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['ma_sinh_vien']) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lớp</label>
                            <input type="text" name="lop" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['lop']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khóa học</label>
                            <input type="text" name="khoa_hoc" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['khoa_hoc']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngành</label>
                            <input type="text" name="chuyen_nganh" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['chuyen_nganh']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['so_dien_thoai'] ?? '') ?>">
                        </div>
                    </div>
                    
                <?php elseif ($userInfo['ma_vai_tro'] === 'lanh_dao'): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã lãnh đạo</label>
                            <input type="text" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['ma_lanh_dao']) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chức vụ</label>
                            <input type="text" name="chuc_vu" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['chuc_vu']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khoa</label>
                            <input type="text" name="khoa" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['khoa']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" class="form-control" 
                                   value="<?= htmlspecialchars($userInfo['profile']['so_dien_thoai'] ?? '') ?>">
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="profile.php?mode=<?= $currentRole ?>" class="btn" style="background-color: #ff0000ff; color: white; border: none; text-decoration: none;">
                  <i class="bi bi-chevron-double-left"></i> Quay lại
                </a>
                <button type="submit" class="btn" style="background-color: #0d6efd; color: white; border: none;">
                    <i class="bi bi-check-circle"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
        </div>
    </div>
</div>