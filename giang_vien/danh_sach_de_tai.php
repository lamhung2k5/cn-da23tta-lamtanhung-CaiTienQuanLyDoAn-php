<?php
/**
 * DANH SÁCH ĐỀ TÀI CỦA GIẢNG VIÊN
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$pageTitle = 'Danh sách đề tài - Giảng viên';

$giangVienModel = new GiangVienModel();
$deTaiModel = new DeTaiModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

if (!$giangVien) {
    setFlashMessage('error', 'Không tìm thấy thông tin giảng viên');
    redirect('../index.php');
}

// Lọc
$filters = [];
if (!empty($_GET['trang_thai'])) {
    $filters['trang_thai'] = sanitize($_GET['trang_thai']);
}

// Xử lý loại đề tài - mặc định là CSN
$loaiDeTai = isset($_GET['loai']) ? trim($_GET['loai']) : 'co_so_nganh';
if (!in_array($loaiDeTai, ['co_so_nganh', 'chuyen_nganh'])) {
    $loaiDeTai = 'co_so_nganh';
}
$filters['he_dao_tao'] = $loaiDeTai;

$danhSachDeTai = $deTaiModel->getDeTaiByGiangVien($giangVien['id'], $filters);

// Đếm tổng số đề tài theo loại (không filter trạng thái)
$allFilters = ['he_dao_tao' => 'co_so_nganh'];
$tongCSN = count($deTaiModel->getDeTaiByGiangVien($giangVien['id'], $allFilters));
$allFilters = ['he_dao_tao' => 'chuyen_nganh'];
$tongCN = count($deTaiModel->getDeTaiByGiangVien($giangVien['id'], $allFilters));

// Kiểm tra có đề tài nháp không
$hasNhap = false;
foreach ($danhSachDeTai as $dt) {
    if ($dt['trang_thai'] === 'nhap') {
        $hasNhap = true;
        break;
    }
}

// Không còn kiểm tra giới hạn đề tài nữa - giảng viên có thể tạo không giới hạn

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
                            <h3 class="mb-2 text-dark">
                                Danh sách đề tài
                                <strong><?= $loaiDeTai === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?></strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Quản lý tất cả đề tài của bạn và theo dõi trạng thái duyệt.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs chọn loại đề tài -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $loaiDeTai === 'co_so_nganh' ? 'active' : '' ?>"
                        href="?loai=co_so_nganh<?= !empty($_GET['trang_thai']) ? '&trang_thai=' . urlencode($_GET['trang_thai']) : '' ?>">
                        <i class="bi bi-journal-code"></i> Cơ sở ngành
                        <span
                            class="badge bg-<?= $loaiDeTai === 'co_so_nganh' ? 'primary' : 'secondary' ?> ms-1"><?= $tongCSN ?></span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $loaiDeTai === 'chuyen_nganh' ? 'active' : '' ?>"
                        href="?loai=chuyen_nganh<?= !empty($_GET['trang_thai']) ? '&trang_thai=' . urlencode($_GET['trang_thai']) : '' ?>">
                        <i class="bi bi-mortarboard"></i> Chuyên ngành
                        <span
                            class="badge bg-<?= $loaiDeTai === 'chuyen_nganh' ? 'success' : 'secondary' ?> ms-1"><?= $tongCN ?></span>
                    </a>
                </li>
            </ul>

            <!-- Bộ lọc -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <input type="hidden" name="loai" value="<?= $loaiDeTai ?>">
                        <div class="col-md-6">
                            <label class="form-label">Trạng thái</label>
                            <select name="trang_thai" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Tất cả --</option>
                                <option value="nhap" <?= ($filters['trang_thai'] ?? '') === 'nhap' ? 'selected' : '' ?>>
                                    Nháp</option>
                                <option value="cho_duyet" <?= ($filters['trang_thai'] ?? '') === 'cho_duyet' ? 'selected' : '' ?>>Chờ duyệt</option>
                                <option value="da_duyet" <?= ($filters['trang_thai'] ?? '') === 'da_duyet' ? 'selected' : '' ?>>Đã duyệt</option>
                                <option value="tu_choi" <?= ($filters['trang_thai'] ?? '') === 'tu_choi' ? 'selected' : '' ?>>Từ chối</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <a href="?loai=<?= $loaiDeTai ?>" class="btn btn-danger text-white">
                                    <i class="bi bi-arrow-clockwise"></i> Làm mới
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (empty($danhSachDeTai)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">Chưa có đề tài <?= getHeDaoTaoLabel($loaiDeTai) ?></p>
                        <a href="tao_de_tai.php?loai=<?= $loaiDeTai ?>" class="btn btn-primary">
                            Tạo đề tài <?= $loaiDeTai === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Không còn cảnh báo giới hạn -->

                <form id="bulkApprovalForm" method="POST" action="gui_duyet.php">
                    <input type="hidden" name="loai" value="<?= $loaiDeTai ?>">
                    <div class="card">
                        <div
                            class="card-header bg-<?= $loaiDeTai === 'co_so_nganh' ? 'primary' : 'success' ?> text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Đề tài: <?= getHeDaoTaoLabel($loaiDeTai) ?>
                            </h5>
                            <span class="badge bg-light text-<?= $loaiDeTai === 'co_so_nganh' ? 'primary' : 'success' ?>">
                                <?= count($danhSachDeTai) ?> đề tài
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <?php if ($hasNhap): ?>
                                                <th width="50" class="text-center">
                                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                                </th>
                                            <?php endif; ?>
                                            <th class="text-center">STT</th>
                                            <th>Tên đề tài</th>
                                            <th class="text-center">Số lượng sinh viên</th>
                                            <th class="text-center">Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $index = 1;
                                        foreach ($danhSachDeTai as $dt):
                                            ?>
                                            <tr>
                                                <?php if ($hasNhap): ?>
                                                    <td class="text-center">
                                                        <?php if ($dt['trang_thai'] === 'nhap'): ?>
                                                            <input type="checkbox" name="selected_topics[]" value="<?= $dt['id'] ?>"
                                                                class="form-check-input topic-checkbox">
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
                                                <td class="text-center"><?= $index++ ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($dt['tieu_de']) ?></strong>
                                                    <?php if ($dt['trang_thai'] === 'tu_choi' && !empty($dt['ly_do_tu_choi'])): ?>
                                                        <br><small class="text-danger">
                                                            Lý do: <?= htmlspecialchars($dt['ly_do_tu_choi']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $daDuSoLuong = ($dt['so_luong_da_dang_ky'] >= $dt['so_luong_sv']);
                                                    $badgeClass = $daDuSoLuong ? 'bg-danger' : 'bg-info';
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>">
                                                        <?= $dt['so_luong_da_dang_ky'] ?>/<?= $dt['so_luong_sv'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center"><?= getStatusBadge($dt['trang_thai']) ?></td>
                                                <td><?= formatDate($dt['created_at']) ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="chi_tiet_de_tai.php?id=<?= $dt['id'] ?>" class="btn btn-info"
                                                            title="Chi tiết">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <?php if ($dt['trang_thai'] === 'nhap' || $dt['trang_thai'] === 'tu_choi'): ?>
                                                            <a href="sua_de_tai.php?id=<?= $dt['id'] ?>" class="btn btn-warning"
                                                                title="Sửa">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="xoa_de_tai.php?id=<?= $dt['id'] ?>" class="btn btn-danger"
                                                                onclick="return confirm('Bạn có chắc muốn xóa đề tài này?')"
                                                                title="Xóa">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($dt['trang_thai'] === 'nhap'): ?>
                                                            <a href="gui_duyet.php?id=<?= $dt['id'] ?>" class="btn btn-success"
                                                                onclick="return confirm('Gửi đề tài này chờ duyệt?')"
                                                                title="Gửi duyệt">
                                                                <i class="bi bi-send"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php if ($hasNhap): ?>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-danger text-white" id="btnGuiDuyet" style="display: none;">
                                <i class="bi bi-send"></i> Gửi duyệt đã chọn
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .form-check-input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.topic-checkbox');
        const btnGuiDuyet = document.getElementById('btnGuiDuyet');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = this.checked);
                toggleButton();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    const checkedCount = document.querySelectorAll('.topic-checkbox:checked').length;
                    selectAll.checked = checkedCount === checkboxes.length;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
                    toggleButton();
                });
            });
        }

        function toggleButton() {
            const checkedCount = document.querySelectorAll('.topic-checkbox:checked').length;
            if (btnGuiDuyet) {
                btnGuiDuyet.style.display = checkedCount > 0 ? 'inline-block' : 'none';
            }
        }

        if (btnGuiDuyet) {
            btnGuiDuyet.addEventListener('click', function () {
                const checkedCount = document.querySelectorAll('.topic-checkbox:checked').length;
                if (checkedCount > 0 && confirm(`Gửi duyệt ${checkedCount} đề tài đã chọn?`)) {
                    const form = document.getElementById('bulkApprovalForm');
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'bulk_approval';
                    input.value = '1';
                    form.appendChild(input);
                    form.submit();
                }
            });
        }
    });
</script>
</body>

</html>