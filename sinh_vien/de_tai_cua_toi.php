<?php
/**
 * ĐỀ TÀI CỦA TÔI
 */

require_once '../bootstrap.php';
requireRole(ROLE_SINH_VIEN);

$user = getCurrentUser();
$pageTitle = 'Đề tài của tôi - Sinh viên';

$sinhVienModel = new SinhVienModel();
$sinhVien = $sinhVienModel->getByNguoiDungId($user['id']);

// Lấy đề tài đã đăng ký
$deTaiDaDangKy = $sinhVienModel->getDeTaiDaDangKy($sinhVien['id']);

// Kiểm tra và tự động từ chối các đề tài chờ duyệt nếu sinh viên đã có đề tài được duyệt cùng loại
$dangKyModel = new DangKyDeTaiModel();
$deTaiModel = new DeTaiModel();

foreach ($deTaiDaDangKy as $dt) {
    if ($dt['trang_thai'] === 'da_duyet') {
        // Sinh viên có đề tài được duyệt, tự động từ chối các đề tài chờ duyệt cùng loại
        $sql = "UPDATE dang_ky_de_tai dk
                JOIN de_tai dt ON dk.de_tai_id = dt.id
                SET dk.trang_thai = :trang_thai_tu_choi,
                    dk.ly_do_tu_choi = :ly_do
                WHERE dk.sinh_vien_id = :sinh_vien_id
                AND dk.trang_thai = :trang_thai_cho_duyet
                AND dt.he_dao_tao = :he_dao_tao";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'trang_thai_tu_choi' => STATUS_TU_CHOI,
            'ly_do' => 'Sinh viên đã có đề tài ' . getHeDaoTaoLabel($dt['he_dao_tao']) . ' được duyệt',
            'sinh_vien_id' => $sinhVien['id'],
            'trang_thai_cho_duyet' => STATUS_CHO_DUYET,
            'he_dao_tao' => $dt['he_dao_tao']
        ]);
    }
}

// Lấy lại danh sách sau khi cập nhật
$deTaiDaDangKy = $sinhVienModel->getDeTaiDaDangKy($sinhVien['id']);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Trang chủ
                </a>
                <a class="nav-link" href="danh_sach_de_tai.php">
                    <i class="bi bi-journal-text"></i> Đề tài có thể đăng ký
                </a>
                <a class="nav-link active" href="de_tai_cua_toi.php">
                    <i class="bi bi-bookmark-check"></i> Đề tài của tôi
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Đề tài của tôi</h2>
            <div class="card-body">
                    <?php if (empty($deTaiDaDangKy)): ?>
                        <div class="text-center py-5 border border-secondary rounded">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Bạn chưa đăng ký đề tài nào</p>
                            <a href="danh_sach_de_tai.php" class="btn btn-primary">
                                <i class="bi bi-search"></i> Tìm đề tài
                            </a>
                        </div>
            <?php else: ?>
                <?php foreach ($deTaiDaDangKy as $dt): ?>
                    <div class="card mb-4 shadow-sm border-0 hover-shadow transition">
                        <div class="card-header bg-gradient-<?= $dt['he_dao_tao'] === 'co_so_nganh' ? 'primary' : 'success' ?> border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0" style="color: #ffff !important; font-weight: 700; text-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                    <?= htmlspecialchars($dt['tieu_de']) ?>
                                </h5>
                                <?php if ($dt['trang_thai'] === 'da_duyet'): ?>
                                    <span class="badge bg-white text-dark border border-dark">Đã duyệt</span>
                                <?php else: ?>
                                    <?= getStatusBadge($dt['trang_thai']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 1.5rem;">
                            <div class="d-flex align-items-center flex-wrap mb-3">
                                <div class="d-flex align-items-center me-4 mb-2">
                                    <i class="bi bi-person-circle text-primary me-2" style="font-size: 1.2rem;"></i>
                                    <span class="text-muted me-2">Giảng viên:</span>
                                    <strong><?= htmlspecialchars($dt['ten_giang_vien']) ?></strong>
                                </div>
                                <div class="d-flex align-items-center me-4 mb-2">
                                    <i class="bi bi-bookmark-fill text-<?= $dt['he_dao_tao'] === 'co_so_nganh' ? 'primary' : 'success' ?> me-2" style="font-size: 1.2rem;"></i>
                                    <span class="text-muted me-2">Hệ:</span>
                                    <span><?= getHeDaoTaoLabel($dt['he_dao_tao']) ?></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar text-info me-2" style="font-size: 1.2rem;"></i>
                                    <span class="text-muted me-2">Ngày đăng ký:</span>
                                    <span><?= formatDate($dt['ngay_dang_ky']) ?></span>
                                </div>
                            </div>
                            
                            <!-- Trạng thái -->
                            <?php if ($dt['trang_thai'] === 'tu_choi' && !empty($dt['ly_do_tu_choi'])): ?>
                                <div class="border-start border-danger border-5 bg-light p-3 mb-3">
                                    <div class="d-flex align-items-start">
                                        
                                        <div class="flex-grow-1">
                                            <h6 class="text-danger mb-2 fw-bold">ĐỀ TÀI BỊ TỪ CHỐI</h6>
                                            <div class="border border-dark rounded p-2 bg-white mb-2">
                                                <strong>Lý do: <class="mb-0 mt-1 text-dark"><?= nl2br(htmlspecialchars($dt['ly_do_tu_choi'])) ?></strong>
                                            </div>
                                            <?php 
                                            // Chỉ hiển thị thông báo "Bạn có thể đăng ký đề tài khác" nếu KHÔNG phải lý do "đã có đề tài được duyệt"
                                            $isDaDuDeTai = (stripos($dt['ly_do_tu_choi'], 'đã có đề tài') !== false && stripos($dt['ly_do_tu_choi'], 'được duyệt') !== false);
                                            ?>
                                            <?php if (!$isDaDuDeTai): ?>
                                                <div class="alert alert-info mb-0 py-2 text-white" style="background-color: #17a2b8 !important; border-color: #17a2b8 !important;">
                                                    <strong>Bạn có thể đăng ký đề tài khác.</strong>
                                                    <a href="danh_sach_de_tai.php" class="text-white text-decoration-underline ms-2">Xem danh sách đề tài</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($dt['trang_thai'] === 'cho_duyet'): ?>
                                <div class="border-start border-warning border-5 bg-light p-3">
                                    <div class="d-flex align-items-center">
                                        
                                        <div class="flex-grow-1">
                                            <h6 class="text-warning mb-2 fw-bold">CHỜ DUYỆT</h6>
                                            <div class="border border-dark rounded p-2 bg-white">
                                                <p class="mb-0 text-dark fw-bold">Đăng ký của bạn đang chờ giảng viên xem xét và phê duyệt.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($dt['trang_thai'] === 'da_duyet'): ?>
                                <div class="border-start border-success border-5 bg-light p-3">
                                    <div class="d-flex align-items-center">
                                        
                                        <div class="flex-grow-1">
                                            <h6 class="text-success mb-2 fw-bold">ĐÃ ĐƯỢC DUYỆT</h6>
                                            <div class="border border-dark rounded p-2 bg-white">
                                                <p class="mb-0 text-dark fw-bold">Chúc mừng! Đăng ký của bạn đã được giảng viên phê duyệt.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
