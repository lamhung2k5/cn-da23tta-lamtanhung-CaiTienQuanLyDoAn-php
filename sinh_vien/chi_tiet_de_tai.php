<?php
/**
 * CHI TIẾT ĐỀ TÀI (SINH VIÊN)
 */

require_once '../bootstrap.php';
requireRole(ROLE_SINH_VIEN);

$user = getCurrentUser();
$pageTitle = 'Chi tiết đề tài - Sinh viên';

$deTaiModel = new DeTaiModel();
$sinhVienModel = new SinhVienModel();

$sinhVien = $sinhVienModel->getByNguoiDungId($user['id']);
$daDuSoLuong = $sinhVienModel->daDuSoLuongDeTai($sinhVien['id']);

// Kiểm tra từng hệ
$daDuCSN = $sinhVienModel->daDuDeTaiTheoHe($sinhVien['id'], 'co_so_nganh');
$daDuCN = $sinhVienModel->daDuDeTaiTheoHe($sinhVien['id'], 'chuyen_nganh');

// Lấy ID đề tài
$deTaiId = (int)($_GET['id'] ?? 0);

if (!$deTaiId) {
    setFlashMessage('error', 'Đề tài không tồn tại');
    redirect('sinh_vien/danh_sach_de_tai.php');
}

// Lấy thông tin đề tài
$sql = "SELECT dt.*, 
               gv.ma_giang_vien,
               nd.ho_ten as ten_giang_vien,
               nd.email as email_giang_vien,
               (dt.so_luong_sv - dt.so_luong_da_dang_ky) as con_lai
        FROM de_tai dt
        JOIN giang_vien gv ON dt.giang_vien_id = gv.id
        JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
        WHERE dt.id = :id";

$deTai = $deTaiModel->queryOne($sql, ['id' => $deTaiId]);

if (!$deTai) {
    setFlashMessage('error', 'Đề tài không tồn tại');
    redirect('sinh_vien/danh_sach_de_tai.php');
}

// Kiểm tra xem sinh viên đã đăng ký đề tài này chưa
$dangKyModel = new DangKyDeTaiModel();
$dangKy = $dangKyModel->findOneBy([
    'sinh_vien_id' => $sinhVien['id'],
    'de_tai_id' => $deTaiId
]);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Custom styles */
.detail-container {
    width: 100%;
}

.detail-card {
    border: 2px solid #000;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.detail-header {
    padding: 2rem;
    color: white;
    border-bottom: 2px solid #000;
}

.detail-header.csn {
    background: linear-gradient(135deg, #0052a8 0%, #003d82 100%);
}

.detail-header.cn {
    background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
}

.detail-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.detail-badge {
    background: white;
    padding: 0.5rem 1.2rem;
    border-radius: 20px;
    font-size: 0.95rem;
    font-weight: 600;
}

.detail-badge.csn {
    color: #0052a8;
}

.detail-badge.cn {
    color: #1cc88a;
}

.detail-body {
    padding: 2rem;
}

.info-row {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #0052a8;
    height: 100%;
}

.info-row.cn {
    border-left-color: #1cc88a;
}

.info-icon {
    font-size: 1.5rem;
    margin-right: 1rem;
    color: #0052a8;
    flex-shrink: 0;
}

.info-icon.cn {
    color: #1cc88a;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.9rem;
    display: block;
    margin-bottom: 0.25rem;
}

.info-value {
    font-weight: 700;
    color: #000000;
    font-size: 1.1rem;
}

.section-box {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #dee2e6;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-content {
    color: #212529;
    line-height: 1.6;
    white-space: pre-line;
    font-weight: 600;
    font-size: 1.05rem;
}

.tech-badge {
    display: inline-block;
    background: white;
    border: 2px solid #0052a8;
    color: #0052a8;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin: 0.25rem;
}

.tech-badge.cn {
    border-color: #1cc88a;
    color: #1cc88a;
}

.action-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #dee2e6;
}

.btn-back {
    background: #6c757d;
    border: none;
    color: white;
    padding: 0.6rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-back-bottom {
    background: #0d6efd;
    border: none;
    color: white;
    padding: 0.8rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-back-bottom:hover {
    background: #0b5ed7;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
    text-decoration: none;
}

.btn-back-bottom i {
    color: white;
}

.btn-register {
    background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
    border: none;
    color: white;
    padding: 0.8rem 2rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 700;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-register:hover {
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(28, 200, 138, 0.4);
}

.status-alert {
    background: white;
    border: 2px solid #17a2b8;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.status-alert.success {
    border-color: #28a745;
}

.status-alert.danger {
    border-color: #dc3545;
}

.status-alert.warning {
    border-color: #ffc107;
}
</style>

<div class="container-fluid">

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Trang chủ
                </a>
                <a class="nav-link active" href="danh_sach_de_tai.php">
                    <i class="bi bi-journal-text"></i> Đề tài có thể đăng ký
                </a>
                <a class="nav-link" href="de_tai_cua_toi.php">
                    <i class="bi bi-bookmark-check"></i> Đề tài của tôi
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="col-md-10 p-4">
            <!-- Nút quay lại - phía trên bên trái -->
            <div class="mb-3">
                <a href="danh_sach_de_tai.php?loai=<?= $deTai['he_dao_tao'] ?>" class="btn-back-bottom">
                    <i class="bi bi-chevron-double-left"></i> Quay lại
                </a>
            </div>
            
            <div class="detail-container">
                <!-- Card chi tiết -->
                <div class="detail-card">
                    <!-- Header -->
                    <div class="detail-header <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? 'csn' : 'cn' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <h1 class="detail-title"><?= htmlspecialchars($deTai['tieu_de']) ?></h1>
                            <span class="detail-badge <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? 'csn' : 'cn' ?>">
                                <?= getHeDaoTaoLabel($deTai['he_dao_tao']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Body -->
                    <div class="detail-body">
                        <!-- Thông tin cơ bản - 2 cột ngang hàng -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-row <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? '' : 'cn' ?>">
                                    <i class="bi bi-person-circle info-icon <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? '' : 'cn' ?>"></i>
                                    <div>
                                        <span class="info-label">Giảng viên hướng dẫn:</span>
                                        <div class="info-value"><?= htmlspecialchars($deTai['ten_giang_vien']) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? '' : 'cn' ?>">
                                    <i class="bi bi-people-fill info-icon <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? '' : 'cn' ?>"></i>
                                    <div>
                                        <span class="info-label">Số lượng sinh viên:</span>
                                        <div class="info-value">
                                            Còn <strong><?= $deTai['con_lai'] ?></strong> / <?= $deTai['so_luong_sv'] ?> chỗ
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mô tả -->
                        <div class="section-box">
                            <div class="section-title">
                                <i class="bi bi-file-text"></i>
                                Mô tả đề tài
                            </div>
                            <div class="section-content">
                                <?= htmlspecialchars($deTai['mo_ta']) ?>
                            </div>
                        </div>

                        <!-- Công nghệ -->
                        <?php if (!empty($deTai['cong_nghe'])): ?>
                            <div class="section-box">
                                <div class="section-title">
                                    <i class="bi bi-code-slash"></i>
                                    Công nghệ sử dụng
                                </div>
                                <div>
                                    <?php 
                                    $congNghes = explode(',', $deTai['cong_nghe']);
                                    foreach ($congNghes as $cn): 
                                    ?>
                                        <span class="tech-badge <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? '' : 'cn' ?>">
                                            <?= htmlspecialchars(trim($cn)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Yêu cầu sinh viên -->
                        <?php if (!empty($deTai['yeu_cau_sinh_vien'])): ?>
                            <div class="section-box">
                                <div class="section-title">
                                    <i class="bi bi-list-check"></i>
                                    Yêu cầu sinh viên
                                </div>
                                <div class="section-content">
                                    <?= htmlspecialchars($deTai['yeu_cau_sinh_vien']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Ghi chú -->
                        <?php if (!empty($deTai['ghi_chu'])): ?>
                            <div class="section-box">
                                <div class="section-title">
                                    <i class="bi bi-sticky"></i>
                                    Ghi chú
                                </div>
                                <div class="section-content">
                                    <?= htmlspecialchars($deTai['ghi_chu']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Action Section -->
                        <div class="action-section">
                            <?php if ($dangKy): ?>
                                <!-- Đã đăng ký -->
                                <div class="status-alert success">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-bookmark-check-fill text-success me-3" style="font-size: 2rem;"></i>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2">Bạn đã đăng ký đề tài này</h5>
                                            <div class="mb-2">
                                                <strong>Trạng thái:</strong> <?= getStatusBadge($dangKy['trang_thai']) ?>
                                            </div>
                                            <?php if ($dangKy['trang_thai'] === 'tu_choi' && !empty($dangKy['ly_do_tu_choi'])): ?>
                                                <div class="alert alert-danger mt-2 mb-0">
                                                    <strong>Lý do từ chối:</strong> <?= htmlspecialchars($dangKy['ly_do_tu_choi']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <a href="de_tai_cua_toi.php" class="btn btn-primary btn-lg">
                                        <i class="bi bi-bookmark-check-fill"></i> Xem trạng thái đề tài
                                    </a>
                                </div>
                            <?php elseif ($deTai['trang_thai'] !== 'da_duyet'): ?>
                                <!-- Chưa được duyệt -->
                                <div class="status-alert warning">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-exclamation-triangle-fill text-warning me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h5 class="mb-0">Đề tài này chưa được lãnh đạo phê duyệt</h5>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Kiểm tra điều kiện đăng ký -->
                                <?php 
                                $khongTheDangKy = false;
                                $lyDo = '';
                                
                                if ($deTai['he_dao_tao'] === 'co_so_nganh' && $daDuCSN) {
                                    $khongTheDangKy = true;
                                    $lyDo = 'Bạn đã có đề tài Cơ sở ngành được duyệt, không thể đăng ký đề tài này';
                                } elseif ($deTai['he_dao_tao'] === 'chuyen_nganh' && $daDuCN) {
                                    $khongTheDangKy = true;
                                    $lyDo = 'Bạn đã có đề tài Chuyên ngành được duyệt, không thể đăng ký đề tài này';
                                }
                                ?>
                                
                                <?php if ($khongTheDangKy): ?>
                                    <div class="status-alert warning">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-lock-fill text-warning me-3" style="font-size: 2rem;"></i>
                                            <div>
                                                <h5 class="mb-0"><?= $lyDo ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ($deTai['con_lai'] > 0): ?>
                                    <div class="text-end">
                                        <a href="dang_ky_de_tai.php?id=<?= $deTai['id'] ?>" 
                                           class="btn-register"
                                           onclick="return confirm('Bạn có chắc muốn đăng ký đề tài này?')">
                                            <i class="bi bi-bookmark-plus-fill"></i> Đăng ký đề tài này
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="status-alert danger">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-x-circle-fill text-danger me-3" style="font-size: 2rem;"></i>
                                            <div>
                                                <h5 class="mb-0">Đề tài này đã hết chỗ đăng ký</h5>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
