<?php
/**
 * TRANG HỒ SƠ CÁ NHÂN
 */

require_once 'bootstrap.php';
requireLogin();

$user = getCurrentUser();
$pageTitle = 'Hồ sơ cá nhân';

$nguoiDungModel = new NguoiDungModel();
$userInfo = $nguoiDungModel->getUserFullInfo($user['id']);

include 'includes/header.php';
?>

<style>
body {
    overflow: hidden;
}

.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 1rem 2rem 1rem; /* Reduced top padding */
    height: calc(100vh - 80px);
    overflow: hidden;
}

.profile-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.profile-sidebar {
    background: #ffffff;
    padding: 2rem;
    text-align: center;
    border-right: 1px solid #e3e6f0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100%;
}

.profile-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    margin: 0 auto 1.5rem;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #000;
    margin-bottom: 0.5rem;
    word-wrap: break-word;
    line-height: 1.3;
}

.profile-username {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.profile-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #d4edda;
    color: #155724;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.profile-status::before {
    content: '';
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.profile-stats {
    display: flex;
    justify-content: space-around;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #dee2e6;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #003d82;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.profile-content {
    padding: 2rem;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #000;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #003d82;
}

.info-group {
    margin-bottom: 1.5rem;
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

.btn-edit-profile {
    width: 100%;
    padding: 0.75rem;
    background: white;
    border: 2px solid #003d82;
    color: #003d82;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-edit-profile:hover {
    background: #003d82;
    color: white;
}

.role-badge {
    display: inline-block;
    background: linear-gradient(135deg, #003d82 0%, #0052a8 100%);
    color: white;
    padding: 0.5rem 1.25rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1rem;
}
</style>

<!-- Back Button -->
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
<div class="profile-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Thông tin cá nhân</h2>
    </div>
    
    <div class="profile-card">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-5">
                <div class="profile-sidebar">
                    <!-- Avatar -->
                    <div class="profile-avatar">
                        <img src="<?= BASE_URL ?>assets/images/hinh.png" alt="Avatar">
                    </div>
                    
                    <!-- Name -->
                    <div class="profile-name"><?= htmlspecialchars($userInfo['ho_ten']) ?></div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="col-md-7">
                <div class="profile-content">
                    <!-- Thông tin chung -->
                    <div class="section-title">
                        Thông tin chung
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Email</div>
                                <div class="info-value">
                                    <i class="bi bi-envelope text-primary me-2"></i>
                                    <?= htmlspecialchars($userInfo['email']) ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Vai trò</div>
                                <div class="info-value">
                                    <i class="bi bi-person-badge text-primary me-2"></i>
                                    <?php
                                    $roleLabels = [
                                        'giang_vien' => 'Giảng viên',
                                        'sinh_vien' => 'Sinh viên',
                                        'lanh_dao' => 'Lãnh đạo'
                                    ];
                                    echo $roleLabels[$userInfo['ma_vai_tro']] ?? $userInfo['ma_vai_tro'];
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($userInfo['profile']): ?>
                        <!-- Thông tin chi tiết -->
                        <div class="section-title mt-4">
                            Thông tin chi tiết
                        </div>
                        
                        <?php if ($userInfo['ma_vai_tro'] === 'giang_vien'): ?>
                            <div class="info-card">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="info-label">Mã giảng viên</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['ma_giang_vien']) ?></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="info-label">Khoa</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['khoa']) ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-label">Chuyên môn</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['chuyen_mon']) ?></div>
                                    </div>
                                    <?php if (!empty($userInfo['profile']['so_dien_thoai'])): ?>
                                        <div class="col-md-6">
                                            <div class="info-label">Số điện thoại</div>
                                            <div class="info-value">
                                                <?= htmlspecialchars($userInfo['profile']['so_dien_thoai']) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        <?php elseif ($userInfo['ma_vai_tro'] === 'sinh_vien'): ?>
                            <div class="info-card">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="info-label">Mã sinh viên</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['ma_sinh_vien']) ?></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="info-label">Lớp</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['lop']) ?></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="info-label">Khóa học</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['khoa_hoc']) ?></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="info-label">Ngành</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['chuyen_nganh']) ?></div>
                                    </div>
                                    <?php if (!empty($userInfo['profile']['so_dien_thoai'])): ?>
                                        <div class="col-md-6">
                                            <div class="info-label">Số điện thoại</div>
                                            <div class="info-value">
                                                <?= htmlspecialchars($userInfo['profile']['so_dien_thoai']) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        <?php elseif ($userInfo['ma_vai_tro'] === 'lanh_dao'): ?>
                            <div class="info-card">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="info-label">Mã lãnh đạo</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['ma_lanh_dao']) ?></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="info-label">Chức vụ</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['chuc_vu']) ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-label">Khoa</div>
                                        <div class="info-value"><?= htmlspecialchars($userInfo['profile']['khoa']) ?></div>
                                    </div>
                                    <?php if (!empty($userInfo['profile']['so_dien_thoai'])): ?>
                                        <div class="col-md-6">
                                            <div class="info-label">Số điện thoại</div>
                                            <div class="info-value">
                                                <?= htmlspecialchars($userInfo['profile']['so_dien_thoai']) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="mt-4 d-flex justify-content-end gap-3">
                        <a href="change_password.php?mode=<?= $currentRole ?>" class="btn" style="background-color: #dc3545; color: white; border: none; text-decoration: none;">
                            <i class="bi bi-shield-lock"></i> Đổi mật khẩu
                        </a>
                        <a href="edit-profile.php?mode=<?= $currentRole ?>" class="btn" style="background-color: #207dffff; color: white; border: none; text-decoration: none;">
                            <i class="bi bi-pencil-square"></i> Chỉnh sửa hồ sơ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>