<?php
/**
 * DANH SÁCH SINH VIÊN ĐÃ DUYỆT
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$pageTitle = 'Sinh viên của tôi - Giảng viên';

$giangVienModel = new GiangVienModel();
$dangKyModel = new DangKyDeTaiModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

if (!$giangVien) {
    setFlashMessage('error', 'Không tìm thấy thông tin giảng viên');
    redirect('../index.php');
}

// Xử lý loại đề tài - mặc định là CSN
$loaiDeTai = isset($_GET['loai']) ? trim($_GET['loai']) : 'co_so_nganh';
if (!in_array($loaiDeTai, ['co_so_nganh', 'chuyen_nganh'])) {
    $loaiDeTai = 'co_so_nganh';
}

// Lấy danh sách sinh viên đã duyệt
$danhSachSinhVien = $dangKyModel->getDanhSachDangKy($giangVien['id'], ['trang_thai' => STATUS_DA_DUYET]);

// Phân loại theo hệ đào tạo
$sinhVienCSN = array_filter($danhSachSinhVien, fn($sv) => $sv['he_dao_tao'] === 'co_so_nganh');
$sinhVienCN = array_filter($danhSachSinhVien, fn($sv) => $sv['he_dao_tao'] === 'chuyen_nganh');

// Lấy danh sách hiển thị theo tab
$danhSachHienThi = $loaiDeTai === 'co_so_nganh' ? $sinhVienCSN : $sinhVienCN;

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <div class="nav-section-title">QUẢN LÝ HỆ THỐNG</div>
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Trang chủ
                </a>
                <a class="nav-link" href="chon_loai_de_tai.php">
                    <i class="bi bi-plus-circle"></i> Tạo đề tài mới
                </a>
                <a class="nav-link" href="danh_sach_de_tai.php">
                    <i class="bi bi-journal-text"></i> Danh sách đề tài
                </a>
                <a class="nav-link" href="duyet_sinh_vien.php">
                    <i class="bi bi-person-add"></i> Duyệt sinh viên
                </a>
                <a class="nav-link active" href="danh_sach_sinh_vien.php">
                    <i class="bi bi-people"></i> Sinh viên của tôi
                </a>
                
                <? if($_SESSION['vai_tro'] == ROLE_LANH_DAO){ ?>
                <div class="nav-section-title">CHỨC NĂNG LÃNH ĐẠO</div>
                <a class="nav-link" href="../lanh_dao/dashboard.php">
                    <i class="bi bi-shield-check"></i> Chế độ Lãnh đạo
                </a>
                <? } ?>
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
                                Danh sách sinh viên đã duyệt 
                                <strong><?= $loaiDeTai === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?></strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Quản lý sinh viên đã được phê duyệt thực hiện đề tài của bạn.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card chứa Tabs và Bảng -->
            <div class="card shadow-sm border-0">
                <!-- Tabs chọn loại đề tài -->
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs mb-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $loaiDeTai === 'co_so_nganh' ? 'active' : '' ?>" 
                               href="?loai=co_so_nganh">
                                <i class="bi bi-journal-code"></i> Cơ sở ngành
                                <span class="badge bg-<?= $loaiDeTai === 'co_so_nganh' ? 'primary' : 'secondary' ?> ms-1">
                                    <?= count($sinhVienCSN) ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $loaiDeTai === 'chuyen_nganh' ? 'active' : '' ?>" 
                               href="?loai=chuyen_nganh">
                                <i class="bi bi-mortarboard"></i> Chuyên ngành
                                <span class="badge bg-<?= $loaiDeTai === 'chuyen_nganh' ? 'success' : 'secondary' ?> ms-1">
                                    <?= count($sinhVienCN) ?>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Bảng danh sách sinh viên -->
                <div class="card-body p-0">
                    <?php if (empty($danhSachHienThi)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Chưa có sinh viên <?= getHeDaoTaoLabel($loaiDeTai) ?> nào được duyệt</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">STT</th>
                                        <th style="width: 130px;">MSSV</th>
                                        <th style="width: 200px;">HỌ TÊN</th>
                                        <th style="width: 120px;">LỚP</th>
                                        <th>TÊN ĐỀ TÀI</th>
                                        <th style="width: 150px;">NGÀY DUYỆT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $index = 1;
                                    foreach ($danhSachHienThi as $sv): 
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $index++ ?></td>
                                            <td><?= $sv['ma_sinh_vien'] ?></td>
                                            <td><strong><?= htmlspecialchars($sv['ten_sinh_vien']) ?></strong></td>
                                            <td><?= htmlspecialchars($sv['lop']) ?></td>
                                            <td><?= htmlspecialchars($sv['tieu_de']) ?></td>
                                            <td><?= formatDate($sv['ngay_duyet'], 'd/m/Y H:i') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
