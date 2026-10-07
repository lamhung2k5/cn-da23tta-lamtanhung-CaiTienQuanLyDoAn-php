<?php
/**
 * TRANG THÔNG BÁO
 * Hiển thị tất cả thông báo của người dùng
 */

require_once 'bootstrap.php';
requireLogin();

$user = getCurrentUser();
$pageTitle = 'Thông báo';

$thongBaoModel = new ThongBaoModel();

// Xử lý đánh dấu đã đọc
if (isset($_GET['action']) && $_GET['action'] === 'mark_read' && isset($_GET['id'])) {
    $thongBaoId = (int)$_GET['id'];
    $thongBao = $thongBaoModel->findById($thongBaoId);
    
    if ($thongBao && $thongBao['nguoi_nhan_id'] == $user['id']) {
        $thongBaoModel->danhDauDaDoc($thongBaoId);
        
        // Redirect đến link nếu có
        if (!empty($thongBao['link'])) {
            redirect($thongBao['link']);
        }
    }
    redirect('thong_bao.php');
}

// Xử lý đánh dấu đã đọc tất cả
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE thong_bao SET da_doc = 1 
            WHERE nguoi_nhan_id = :nguoi_nhan_id AND da_doc = 0");
    $stmt->execute(['nguoi_nhan_id' => $user['id']]);
    
    setFlashMessage('success', 'Đã đánh dấu tất cả thông báo là đã đọc');
    redirect('thong_bao.php');
}



// Lọc
$filter = $_GET['filter'] ?? 'all';
$danhSachThongBao = [];

if ($filter === 'unread') {
    // Chỉ lấy thông báo chưa đọc
    $sql = "SELECT * FROM thong_bao 
            WHERE nguoi_nhan_id = :nguoi_nhan_id AND da_doc = 0
            ORDER BY created_at DESC";
    $danhSachThongBao = $thongBaoModel->query($sql, ['nguoi_nhan_id' => $user['id']]);
} else {
    // Lấy tất cả thông báo (không giới hạn)
    $sql = "SELECT * FROM thong_bao 
            WHERE nguoi_nhan_id = :nguoi_nhan_id
            ORDER BY created_at DESC";
    $danhSachThongBao = $thongBaoModel->query($sql, ['nguoi_nhan_id' => $user['id']]);
}

$soThongBaoChuaDoc = $thongBaoModel->countChuaDoc($user['id']);

// Include header
include_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Card hover effect */
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Notification card with border */
    .border-start.border-danger {
        border-left-width: 4px !important;
    }
    
    /* Link hover effect */
    .card a:hover {
        text-decoration: none;
    }
    
    .card:hover .text-primary {
        text-decoration: underline;
    }
    
    /* Badge animation */
    .badge {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }
    
    /* Smooth transitions */
    .btn-group .btn {
        transition: all 0.3s ease;
    }
    
    /* Icon background */
    .rounded-circle {
        transition: all 0.3s ease;
    }
    
    .card:hover .rounded-circle {
        transform: scale(1.1);
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar dựa theo vai trò -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <?php 
                // Kiểm tra chế độ hiện tại dựa vào referer hoặc tham số
                $currentMode = $user['vai_tro'];
                $referer = $_SERVER['HTTP_REFERER'] ?? '';
                
                // Nếu đến từ trang giảng viên hoặc có tham số mode=giang_vien
                if (strpos($referer, '/giang_vien/') !== false || ($_GET['mode'] ?? '') === 'giang_vien') {
                    $currentMode = ROLE_GIANG_VIEN;
                }
                ?>
                
                <?php if ($currentMode === ROLE_GIANG_VIEN): ?>
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
                    
                    <?php if ($user['vai_tro'] === ROLE_LANH_DAO): ?>
                        <div class="nav-section-title">CHỨC NĂNG LÃNH ĐẠO</div>
                        <a class="nav-link" href="lanh_dao/dashboard.php">
                            <i class="bi bi-shield-check"></i> Chế độ Lãnh đạo
                        </a>
                    <?php endif; ?>
                <?php elseif ($user['vai_tro'] === ROLE_SINH_VIEN): ?>
                    <a class="nav-link" href="sinh_vien/dashboard.php">
                        <i class="bi bi-house-door"></i> Trang chủ
                    </a>
                    <a class="nav-link" href="sinh_vien/danh_sach_de_tai.php">
                        <i class="bi bi-journal-text"></i> Đề tài có thể đăng ký
                    </a>
                    <a class="nav-link" href="sinh_vien/de_tai_cua_toi.php">
                        <i class="bi bi-bookmark-check"></i> Đề tài của tôi
                    </a>
                <?php elseif ($user['vai_tro'] === ROLE_LANH_DAO): ?>
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
            <!-- Welcome Card -->
            <div class="card mb-4 fade-in-up border-dark" style="border-width: 2px !important;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2 text-dark">
                                <i class="bi bi-bell-fill me-2" style="color: #ff0000ff;"></i>
                                <strong>Thông báo</strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Xem tất cả thông báo và cập nhật từ hệ thống.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if ($soThongBaoChuaDoc > 0): ?>
                                <a href="?action=mark_all_read" 
                                   class="btn btn-outline-primary"
                                   onclick="return confirm('Bạn có chắc muốn đánh dấu tất cả thông báo là đã đọc?')">
                                    <i class="bi bi-check-square"></i> Đọc tất cả
                                </a>
                            <?php else: ?>
                                <div class="badge bg-white fs-5 px-3 py-2 border border-dark">
                                    <span style="color: #6c757d; font-weight: 700;">
                                        <i class="bi bi-check-circle-fill text-success"></i> Đã đọc hết
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bộ lọc -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body">
                    <div class="btn-group" role="group">
                        <a href="?filter=all" class="btn btn-<?= $filter === 'all' ? 'primary' : 'outline-primary' ?>">
                            <i class="bi bi-list-ul"></i> Tất cả 
                            <span class="badge bg-<?= $filter === 'all' ? 'white text-primary' : 'primary' ?> ms-1">
                                <?php
                                // Đếm tổng số thông báo
                                $sqlCount = "SELECT COUNT(*) as total FROM thong_bao WHERE nguoi_nhan_id = :nguoi_nhan_id";
                                $resultCount = $thongBaoModel->queryOne($sqlCount, ['nguoi_nhan_id' => $user['id']]);
                                echo $resultCount['total'] ?? 0;
                                ?>
                            </span>
                        </a>
                        <a href="?filter=unread" class="btn btn-<?= $filter === 'unread' ? 'danger' : 'outline-danger' ?>">
                            <i class="bi bi-envelope-fill"></i> Chưa đọc 
                            <span class="badge bg-<?= $filter === 'unread' ? 'white text-danger' : 'danger' ?> ms-1">
                                <?= $soThongBaoChuaDoc ?>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Danh sách thông báo -->
            <?php if (empty($danhSachThongBao)): ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 5rem; opacity: 0.2;"></i>
                        </div>
                        <h5 class="text-muted mb-2">Không có thông báo</h5>
                        <p class="text-muted small mb-0">
                            <?= $filter === 'unread' ? 'Bạn đã đọc tất cả thông báo' : 'Chưa có thông báo nào' ?>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($danhSachThongBao as $tb): ?>
                        <div class="col-12">
                            <div class="card shadow-sm border-0 h-100 <?= $tb['da_doc'] == 0 ? 'border-start border-danger border-4' : '' ?>" 
                                 style="transition: all 0.3s ease;">
                                <div class="card-body">
                                    <a href="?action=mark_read&id=<?= $tb['id'] ?>" 
                                       class="text-decoration-none text-dark">
                                        <div class="d-flex align-items-start">
                                            <!-- Icon -->
                                            <div class="me-3 mt-1">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 48px; height: 48px; background: <?= $tb['loai'] === 'success' ? '#d4edda' : ($tb['loai'] === 'danger' ? '#f8d7da' : ($tb['loai'] === 'warning' ? '#fff3cd' : '#d1ecf1')) ?>;">
                                                    <i class="bi bi-<?= $tb['loai'] === 'success' ? 'check-circle-fill text-success' : ($tb['loai'] === 'danger' ? 'x-circle-fill text-danger' : ($tb['loai'] === 'warning' ? 'exclamation-triangle-fill text-warning' : 'info-circle-fill text-info')) ?>" 
                                                       style="font-size: 1.5rem;"></i>
                                                </div>
                                            </div>
                                            
                                            <!-- Nội dung -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0 <?= $tb['da_doc'] == 0 ? 'fw-bold' : '' ?>">
                                                        <?= htmlspecialchars($tb['tieu_de']) ?>
                                                        <?php if ($tb['da_doc'] == 0): ?>
                                                            <span class="badge bg-danger ms-2" style="font-size: 0.7rem;">MỚI</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                </div>
                                                
                                                <p class="mb-2 text-muted" style="font-size: 0.9rem;">
                                                    <?= nl2br(htmlspecialchars($tb['noi_dung'])) ?>
                                                </p>
                                                
                                                <div class="d-flex align-items-center text-muted" style="font-size: 0.85rem;">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <span><?= formatDate($tb['created_at'], 'd/m/Y H:i') ?></span>
                                                    
                                                    <?php if (!empty($tb['link'])): ?>
                                                        <span class="mx-2">•</span>
                                                        <i class="bi bi-link-45deg me-1"></i>
                                                        <span class="text-primary">Xem chi tiết</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
