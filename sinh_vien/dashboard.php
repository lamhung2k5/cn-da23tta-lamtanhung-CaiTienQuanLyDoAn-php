<?php
/**
 * DASHBOARD SINH VIÊN
 */

require_once '../bootstrap.php';
requireRole(ROLE_SINH_VIEN);

$user = getCurrentUser();
$pageTitle = 'Dashboard - Sinh viên';

$sinhVienModel = new SinhVienModel();
$deTaiModel = new DeTaiModel();

$sinhVien = $sinhVienModel->getByNguoiDungId($user['id']);

// Kiểm tra đã đăng ký đề tài chưa
$daDangKy = $sinhVienModel->daDangKyDeTai($sinhVien['id']);

// Lấy đề tài đã đăng ký
$deTaiDaDangKy = $sinhVienModel->getDeTaiDaDangKy($sinhVien['id']);

// Thống kê đề tài có thể đăng ký
$tongDeTaiCSN = count($deTaiModel->getDeTaiDaDuyet(['he_dao_tao' => 'co_so_nganh']));
$tongDeTaiCN = count($deTaiModel->getDeTaiDaDuyet(['he_dao_tao' => 'chuyen_nganh']));

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
    /* Custom button styles cho nút chi tiết */
    .btn-ocean {
        background-color: #2986ffff !important;
        border-color: #2986ffff !important;
        color: #ffffff !important;
    }

    .btn-ocean i {
        color: #ffffff !important;
    }

    .btn-ocean:hover {
        background-color: #004c94ff !important;
        border-color: #004c94ff !important;
        color: #ffffff !important;
    }

    .btn-ocean:hover i {
        color: #ffffff !important;
    }

    .btn-ocean:active,
    .btn-ocean:focus {
        background-color: #117a8b !important;
        border-color: #10707f !important;
        color: #ffffff !important;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.5) !important;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Trang chủ
                </a>
                <a class="nav-link" href="danh_sach_de_tai.php">
                    <i class="bi bi-journal-text"></i> Đề tài có thể đăng ký
                </a>
                <a class="nav-link" href="de_tai_cua_toi.php">
                    <i class="bi bi-bookmark-check"></i> Đề tài của tôi
                </a>
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
                                Xin chào, <strong><?= htmlspecialchars($user['ho_ten']) ?></strong>.
                            </h3>
                            <p class="mb-0 text-muted">
                                Chào mừng bạn đến với hệ thống quản lý đề tài cơ sở ngành và chuyên ngành.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin sinh viên -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Thông tin cá nhân</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Họ tên</strong></td>
                                    <td><?= htmlspecialchars($user['ho_ten']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>MSSV</strong></td>
                                    <td><?= $sinhVien['ma_sinh_vien'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Lớp</strong></td>
                                    <td><?= htmlspecialchars($sinhVien['lop']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Khóa học</strong></td>
                                    <td><?= htmlspecialchars($sinhVien['khoa_hoc']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Ngành</strong></td>
                                    <td><?= htmlspecialchars($sinhVien['chuyen_nganh']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td><?= $user['email'] ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4 border-start">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <img id="avatarPreview" src="../assets/images/hinh.png" alt="Avatar"
                                    class="rounded-circle" style="width: 220px; height: 220px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Đề tài đã đăng ký -->
            <div class="card">
                <div class="card-header">
                    Đề tài bạn đã đăng ký
                </div>
                <div class="card-body">
                    <?php if (empty($deTaiDaDangKy)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Bạn chưa đăng ký đề tài nào</p>
                            <a href="danh_sach_de_tai.php" class="btn btn-primary">
                                <i class="bi bi-search"></i> Tìm đề tài
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tên đề tài</th>
                                        <th>Giảng viên Hướng Dẫn</th>
                                        <th>Loại đề tài</th>
                                        <th>Ngày đăng ký</th>
                                        <th>Trạng thái</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deTaiDaDangKy as $dt): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($dt['tieu_de']) ?></strong>
                                                <?php if ($dt['trang_thai'] === 'tu_choi' && !empty($dt['ly_do_tu_choi'])): ?>

                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($dt['ten_giang_vien']) ?><br>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-<?= $dt['he_dao_tao'] === 'co_so_nganh' ? 'primary' : 'success' ?>">
                                                    <?= getHeDaoTaoLabel($dt['he_dao_tao']) ?>
                                                </span>
                                            </td>
                                            <td><?= formatDate($dt['ngay_dang_ky']) ?></td>
                                            <td><?= getStatusBadge($dt['trang_thai']) ?></td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="chi_tiet_de_tai.php?id=<?= $dt['de_tai_id'] ?>"
                                                        class="btn btn-ocean btn-sm" title="Xem chi tiết">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </a>
                                                    <?php if ($dt['trang_thai'] === 'tu_choi'): ?>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="xoaDangKy(<?= $dt['id'] ?>)" title="Xóa đăng ký">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
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
<script>
    function xoaDangKy(dangKyId) {
        if (confirm('Bạn có chắc chắn muốn xóa đăng ký đề tài này?')) {
            fetch('xoa_dang_ky.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'dang_ky_id=' + dangKyId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Xóa đăng ký thành công');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Có lỗi xảy ra: ' + error);
                });
        }
    }
</script>
</body>

</html>