<?php
/**
 * CHỌN LOẠI ĐỀ TÀI
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$pageTitle = 'Chọn loại đề tài - Giảng viên';

$giangVienModel = new GiangVienModel();
$deTaiModel = new DeTaiModel();
$caiDatModel = new CaiDatModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);
$soDeTaiHienTai = $giangVien ? $deTaiModel->countDeTaiByGiangVien($giangVien['id']) : 0;
$soDeTaiToiDa = $caiDatModel->getSoDeTaiToiDaGV();
//$soDeTaiToiDa = 20;

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
                <a class="nav-link active" href="chon_loai_de_tai.php">
                    <i class="bi bi-plus-circle"></i> Tạo đề tài mới
                </a>
                <a class="nav-link" href="danh_sach_de_tai.php">
                    <i class="bi bi-journal-text"></i> Danh sách đề tài
                </a>
                <a class="nav-link" href="duyet_sinh_vien.php">
                    <i class="bi bi-person-add"></i> Duyệt sinh viên
                </a>
                <a class="nav-link" href="danh_sach_sinh_vien.php">
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
                                <strong>Chọn loại đề tài</strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Chọn loại đề tài bạn muốn tạo: Cơ sở ngành hoặc Chuyên ngành.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($soDeTaiHienTai >= $soDeTaiToiDa): ?>
                <div class="alert alert-warning" role="alert">
                    <strong>Thông báo:</strong> Bạn đã đạt giới hạn số lượng đề tài được phép tạo là (<?= $soDeTaiToiDa ?>
                    đề tài). Vui lòng liên hệ lãnh đạo nếu cần tăng số lượng đề tài.
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Cơ sở ngành -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm hover-card">
                        <div class="card-body text-center p-5">
                            <div class="mb-4">
                                <i class="bi bi-journal-code text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <?php if ($soDeTaiHienTai >= $soDeTaiToiDa): ?>
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="bi bi-lock"></i> Đã đạt giới hạn
                                </button>
                            <?php else: ?>
                                <a href="tao_de_tai.php?loai=co_so_nganh" class="btn btn-primary btn-lg">
                                    Tạo đề tài Cơ sở ngành
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Chuyên ngành -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm hover-card">
                        <div class="card-body text-center p-5">
                            <div class="mb-4">
                                <i class="bi bi-mortarboard text-success" style="font-size: 4rem;"></i>
                            </div>
                            <?php if ($soDeTaiHienTai >= $soDeTaiToiDa): ?>
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="bi bi-lock"></i> Đã đạt giới hạn
                                </button>
                            <?php else: ?>
                                <a href="tao_de_tai.php?loai=chuyen_nganh" class="btn btn-success btn-lg">
                                    Tạo đề tài Chuyên ngành
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .hover-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>