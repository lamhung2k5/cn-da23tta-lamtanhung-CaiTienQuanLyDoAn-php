<?php
/**
 * CHI TIẾT ĐỀ TÀI
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$pageTitle = 'Chi tiết đề tài - Giảng viên';

$giangVienModel = new GiangVienModel();
$deTaiModel = new DeTaiModel();
$dangKyModel = new DangKyDeTaiModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

// Lấy ID đề tài
$deTaiId = (int)($_GET['id'] ?? 0);

if (!$deTaiId) {
    setFlashMessage('error', 'Đề tài không tồn tại');
    redirect('giang_vien/danh_sach_de_tai.php');
}

$deTai = $deTaiModel->findById($deTaiId);

if (!$deTai || $deTai['giang_vien_id'] != $giangVien['id']) {
    setFlashMessage('error', 'Bạn không có quyền xem đề tài này');
    redirect('giang_vien/danh_sach_de_tai.php');
}

// Lấy danh sách sinh viên đã đăng ký
$danhSachSinhVien = $dangKyModel->getDanhSachDangKy($giangVien['id'], ['de_tai_id' => $deTaiId]);

// Lấy danh sách sinh viên đã có đề tài được duyệt theo từng loại
$sql = "SELECT DISTINCT dk.sinh_vien_id, dt.he_dao_tao
        FROM dang_ky_de_tai dk
        JOIN de_tai dt ON dk.de_tai_id = dt.id
        WHERE dk.trang_thai = :trang_thai";
$stmt = $dangKyModel->query($sql, [
    'trang_thai' => STATUS_DA_DUYET
]);

// Tạo mảng sinh viên đã có đề tài được duyệt theo loại
$sinhVienDaDuCSN = [];
$sinhVienDaDuCN = [];
foreach ($stmt as $row) {
    if ($row['he_dao_tao'] === 'co_so_nganh') {
        $sinhVienDaDuCSN[] = $row['sinh_vien_id'];
    } else {
        $sinhVienDaDuCN[] = $row['sinh_vien_id'];
    }
}

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
                <a class="nav-link active" href="danh_sach_de_tai.php">
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
                            <h3 class="mb-2 text-dark">Chi tiết đề tài <strong><?= getHeDaoTaoLabel($deTai['he_dao_tao']) ?></strong></h3>
                            <p class="mb-0 text-muted">Xem thông tin chi tiết và quản lý sinh viên đăng ký đề tài.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="danh_sach_de_tai.php?loai=<?= $deTai['he_dao_tao'] ?>" class="btn text-white" style="background-color: #0d6efd;">
                                <i class="bi bi-chevron-double-left"></i> Quay lại
                            </a>
                            <?php if ($deTai['trang_thai'] === 'nhap' || $deTai['trang_thai'] === 'tu_choi'): ?>
                                <a href="sua_de_tai.php?id=<?= $deTai['id'] ?>" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i> Sửa
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin đề tài -->
            <div class="card mb-4">
                <div class="card-header bg-primary">
                    <h5 class="mb-0" style="color: white !important;">Thông tin đề tài</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h4><?= htmlspecialchars($deTai['tieu_de']) ?></h4>
                        </div>
                        <div class="col-md-4 text-end">
                            <?= getStatusBadge($deTai['trang_thai']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Loại đề tài:</strong> 
                            <span class="badge bg-<?= $deTai['he_dao_tao'] === 'co_so_nganh' ? 'primary' : 'success' ?>">
                                <?= getHeDaoTaoLabel($deTai['he_dao_tao']) ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Số lượng sinh viên:</strong> 
                            <?php 
                            $daDuSoLuong = ($deTai['so_luong_da_dang_ky'] >= $deTai['so_luong_sv']);
                            $badgeClass = $daDuSoLuong ? 'bg-danger' : 'bg-info';
                            ?>
                            <span class="badge <?= $badgeClass ?>">
                                <?= $deTai['so_luong_da_dang_ky'] ?>/<?= $deTai['so_luong_sv'] ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($deTai['chuyen_nganh'])): ?>
                        <div class="mb-3">
                            <strong>Ngành:</strong> <?= htmlspecialchars($deTai['chuyen_nganh']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <strong>Mô tả:</strong>
                        <p class="mt-2"><?= nl2br(htmlspecialchars($deTai['mo_ta'])) ?></p>
                    </div>

                    <?php if (!empty($deTai['cong_nghe'])): ?>
                        <div class="mb-3">
                            <strong>Công nghệ sử dụng:</strong>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($deTai['cong_nghe'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($deTai['yeu_cau_sinh_vien'])): ?>
                        <div class="mb-3">
                            <strong>Yêu cầu sinh viên:</strong>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($deTai['yeu_cau_sinh_vien'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($deTai['ghi_chu'])): ?>
                        <div class="mb-3">
                            <strong>Ghi chú:</strong>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($deTai['ghi_chu'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($deTai['trang_thai'] === 'tu_choi' && !empty($deTai['ly_do_tu_choi'])): ?>
                        <div class="alert alert-danger">
                            <strong>Lý do từ chối:</strong>
                            <?= nl2br(htmlspecialchars($deTai['ly_do_tu_choi'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Danh sách sinh viên đăng ký -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="color: black !important;">Sinh viên đăng ký (<?= count($danhSachSinhVien) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($danhSachSinhVien)): ?>
                        <p class="text-muted text-center py-3">Chưa có sinh viên đăng ký</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">STT</th>
                                        <th>Họ tên</th>
                                        <th>MSSV</th>
                                        <th>Lớp</th>
                                        <th>Ngày đăng ký</th>
                                        <th class="text-start">Trạng thái</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($danhSachSinhVien as $index => $sv): ?>
                                        <?php
                                        // Kiểm tra sinh viên đã có đề tài được duyệt cùng loại chưa
                                        $sinhVienDaDu = false;
                                        if ($deTai['he_dao_tao'] === 'co_so_nganh') {
                                            $sinhVienDaDu = in_array($sv['sinh_vien_id'], $sinhVienDaDuCSN);
                                        } else {
                                            $sinhVienDaDu = in_array($sv['sinh_vien_id'], $sinhVienDaDuCN);
                                        }
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($sv['ten_sinh_vien']) ?></td>
                                            <td><?= $sv['ma_sinh_vien'] ?></td>
                                            <td><?= htmlspecialchars($sv['lop']) ?></td>
                                            <td><?= formatDate($sv['ngay_dang_ky']) ?></td>
                                            <td class="text-start">
                                                <?php if ($sinhVienDaDu && $sv['trang_thai'] === STATUS_CHO_DUYET): ?>
                                                    <span class="badge bg-danger text-white fs-6 px-3 py-2">
                                                        Sinh viên đã có đề tài: <?= getHeDaoTaoLabel($deTai['he_dao_tao']) ?> được duyệt
                                                    </span>
                                                <?php else: ?>
                                                    <?= getStatusBadge($sv['trang_thai']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($sv['trang_thai'] === 'tu_choi'): ?>
                                                    <button class="btn btn-danger btn-sm py-0" 
                                                            onclick="xoaSinhVien(<?= $sv['id'] ?>, '<?= htmlspecialchars($sv['ten_sinh_vien']) ?>')"
                                                            title="Xóa sinh viên">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php elseif ($sinhVienDaDu && $sv['trang_thai'] === STATUS_CHO_DUYET): ?>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="xoaDangKy(<?= $sv['id'] ?>)"
                                                            title="Xóa đăng ký này">
                                                        <i class="bi bi-trash-fill"></i> Xóa
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
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

<script>
function xoaSinhVien(dangKyId, tenSinhVien) {
    if (confirm('Bạn có chắc muốn xóa đăng ký của sinh viên "' + tenSinhVien + '"?\n\nHành động này không thể hoàn tác.')) {
        window.location.href = 'xu_ly_xoa_dang_ky.php?id=' + dangKyId + '&de_tai_id=<?= $deTaiId ?>';
    }
}

function xoaDangKy(dangKyId) {
    if (confirm('Bạn có chắc chắn muốn xóa đăng ký này?\n\nSinh viên này đã có đề tài được duyệt cùng loại, xóa đăng ký sẽ giúp giảm số lượng sinh viên chờ duyệt.')) {
        window.location.href = 'xoa_dang_ky_gv.php?id=' + dangKyId + '&redirect=chi_tiet&de_tai_id=<?= $deTaiId ?>';
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
