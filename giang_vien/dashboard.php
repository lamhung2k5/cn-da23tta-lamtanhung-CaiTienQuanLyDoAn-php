<?php
/**
 * DASHBOARD GIẢNG VIÊN
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$pageTitle = 'Dashboard - Giảng viên';

$giangVienModel = new GiangVienModel();
$deTaiModel = new DeTaiModel();
$dangKyModel = new DangKyDeTaiModel();

// Lấy thông tin giảng viên (lãnh đạo cũng có profile giảng viên)
$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

if (!$giangVien) {
    setFlashMessage('error', 'Không tìm thấy thông tin giảng viên');
    redirect('../index.php');
}

$thongKe = $giangVienModel->getThongKeDeTai($giangVien['id']);

// Lấy danh sách đề tài gần đây
$deTaiGanDay = $deTaiModel->getDeTaiByGiangVien($giangVien['id']);
$deTaiGanDay = array_slice($deTaiGanDay, 0, 5);

// Lấy danh sách sinh viên chờ duyệt
$svChoDuyet = $dangKyModel->getDanhSachDangKy($giangVien['id'], ['trang_thai' => STATUS_CHO_DUYET]);

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

// Lọc sinh viên chờ duyệt (loại trừ sinh viên đã có đề tài được duyệt cùng loại)
$svChoDuyet = array_filter($svChoDuyet, function($sv) use ($sinhVienDaDuCSN, $sinhVienDaDuCN) {
    if ($sv['he_dao_tao'] === 'co_so_nganh') {
        return !in_array($sv['sinh_vien_id'], $sinhVienDaDuCSN);
    } else {
        return !in_array($sv['sinh_vien_id'], $sinhVienDaDuCN);
    }
});

// Phân loại sinh viên theo hệ đào tạo
$svChoDuyetCSN = array_filter($svChoDuyet, fn($sv) => $sv['he_dao_tao'] === 'co_so_nganh');
$svChoDuyetCN = array_filter($svChoDuyet, fn($sv) => $sv['he_dao_tao'] === 'chuyen_nganh');

// Nhóm sinh viên theo đề tài
function nhomSinhVienTheoDeTai($danhSachSV) {
    $nhom = [];
    foreach ($danhSachSV as $sv) {
        $deTaiId = $sv['de_tai_id'];
        if (!isset($nhom[$deTaiId])) {
            $nhom[$deTaiId] = [
                'de_tai_id' => $deTaiId,
                'tieu_de' => $sv['tieu_de'],
                'sinh_vien' => []
            ];
        }
        $nhom[$deTaiId]['sinh_vien'][] = $sv;
    }
    return array_values($nhom);
}

$svChoDuyetCSNNhom = nhomSinhVienTheoDeTai($svChoDuyetCSN);
$svChoDuyetCNNhom = nhomSinhVienTheoDeTai($svChoDuyetCN);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <div class="nav-section-title">QUẢN LÝ HỆ THỐNG</div>
                <a class="nav-link active" href="dashboard.php">
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
            <!-- Welcome Card & Thống kê -->
            <!-- Welcome Card & Thống kê -->
            <div class="card mb-4 fade-in-up border-dark" style="border-width: 2px !important;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <h3 class="mb-2 text-dark">
                                Xin chào, <span style="color: #000; font-weight: bold;"><?= htmlspecialchars($user['ho_ten']) ?></span>.
                            </h3>
                            <p class="mb-0 text-muted">
                                Chào mừng giảng viên đến với hệ thống quản lý đề tài cơ sở ngành và chuyên ngành.
                            </p>
                        </div>
                        <div class="col-md-3 border-start border-dark border-2 ps-4">
                            <h6 class="text-muted mb-2">Tổng đề tài hiện có</h6>
                            <h2 class="mb-2"><?= $thongKe['tong_de_tai'] ?? 0 ?></h2>
                            <small class="text-muted">
                                Cơ Sở Ngành: <?= $thongKe['de_tai_csn'] ?? 0 ?> | 
                                Chuyên Ngành: <?= $thongKe['de_tai_cn'] ?? 0 ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Sinh viên chờ duyệt CSN -->
                <div class="col-md-6 mb-4">
                    <div class="card fade-in-up h-100" style="animation-delay: 0.6s;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span>
                                Sinh viên đăng ký Cơ sở ngành
                            </span>
                            <?php if (count($svChoDuyetCSN) > 0): ?>
                                <span class="badge bg-light text-dark"><?= count($svChoDuyetCSN) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php if (empty($svChoDuyetCSNNhom)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="text-muted mt-3 mb-0">Không có sinh viên đăng ký chờ duyệt</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($svChoDuyetCSNNhom, 0, 3) as $nhom): ?>
                                        <div class="mb-2 p-3 border border-dark rounded" style="background: #f8f9ff;">
                                            <!-- Tiêu đề đề tài -->
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 text-dark fw-bold"> Tên đề tài: <?= htmlspecialchars($nhom['tieu_de']) ?></h6>
                                                    <small class="text-muted"> Có <?= count($nhom['sinh_vien']) ?> sinh viên đăng ký
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="toggleSinhVien('csn-<?= $nhom['de_tai_id'] ?>')" 
                                                            title="Xổ xuống/lên">
                                                        <i class="bi bi-chevron-down" id="icon-csn-<?= $nhom['de_tai_id'] ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            onclick="xemChiTietDeTai(<?= $nhom['de_tai_id'] ?>, 'co_so_nganh')" 
                                                            title="Xem chi tiết">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Danh sách sinh viên -->
                                            <div class="row g-2 collapse" id="csn-<?= $nhom['de_tai_id'] ?>">
                                                <?php foreach ($nhom['sinh_vien'] as $sv): ?>
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center gap-2 p-2 bg-white rounded border">
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                                                 style="width: 35px; height: 35px;">
                                                                <i class="bi bi-person"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="fw-semibold"><?= htmlspecialchars($sv['ten_sinh_vien']) ?></div>
                                                                <small class="text-muted">
                                                                    <?= $sv['ma_sinh_vien'] ?>
                                                                    <?php if (!empty($sv['lop'])): ?>
                                                                        <span class="mx-1">-</span>
                                                                        <span class="text-dark" style="font-size: 0.75rem;"><?= htmlspecialchars($sv['lop']) ?></span>
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                            <div class="d-flex gap-1 flex-shrink-0">
                                                                <button class="btn btn-success btn-sm" onclick="duyetSinhVien(<?= $sv['id'] ?>)" title="Duyệt">
                                                                    <i class="bi bi-check-circle"></i>
                                                                </button>
                                                                <button class="btn btn-danger btn-sm" onclick="tuChoiSinhVien(<?= $sv['id'] ?>)" title="Từ chối">
                                                                    <i class="bi bi-x-circle"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="duyet_sinh_vien.php?loai=co_so_nganh" class="btn btn-primary btn-sm">
                                        Xem tất cả danh sách
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sinh viên chờ duyệt CN -->
                <div class="col-md-6 mb-4">
                    <div class="card fade-in-up h-100" style="animation-delay: 0.7s;">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span>
                                Sinh viên đăng ký Chuyên ngành
                            </span>
                            <?php if (count($svChoDuyetCN) > 0): ?>
                                <span class="badge bg-light text-dark"><?= count($svChoDuyetCN) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php if (empty($svChoDuyetCNNhom)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="text-muted mt-3 mb-0">Không có sinh viên đăng ký chờ duyệt</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($svChoDuyetCNNhom, 0, 3) as $nhom): ?>
                                        <div class="mb-2 p-3 border border-dark rounded" style="background: #f0fdf4;">
                                            <!-- Tiêu đề đề tài -->
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 text-dark fw-bold">Tên đề tài: <?= htmlspecialchars($nhom['tieu_de']) ?></h6>
                                                    <small class="text-muted">
                                                        Có <?= count($nhom['sinh_vien']) ?> sinh viên đăng ký
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="toggleSinhVien('cn-<?= $nhom['de_tai_id'] ?>')" 
                                                            title="Xổ xuống/lên">
                                                        <i class="bi bi-chevron-down" id="icon-cn-<?= $nhom['de_tai_id'] ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            onclick="xemChiTietDeTai(<?= $nhom['de_tai_id'] ?>, 'chuyen_nganh')" 
                                                            title="Xem chi tiết">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Danh sách sinh viên -->
                                            <div class="row g-2 collapse" id="cn-<?= $nhom['de_tai_id'] ?>">
                                                <?php foreach ($nhom['sinh_vien'] as $sv): ?>
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center gap-2 p-2 bg-white rounded border">
                                                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                                                 style="width: 35px; height: 35px;">
                                                                <i class="bi bi-person"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="fw-semibold"><?= htmlspecialchars($sv['ten_sinh_vien']) ?></div>
                                                                <small class="text-muted">
                                                                    <?= $sv['ma_sinh_vien'] ?>
                                                                    <?php if (!empty($sv['lop'])): ?>
                                                                        <span class="mx-1">-</span>
                                                                        <span class="text-dark" style="font-size: 0.75rem;"><?= htmlspecialchars($sv['lop']) ?></span>
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                            <div class="d-flex gap-1 flex-shrink-0">
                                                                <button class="btn btn-success btn-sm" onclick="duyetSinhVien(<?= $sv['id'] ?>)" title="Duyệt">
                                                                    <i class="bi bi-check-circle"></i>
                                                                </button>
                                                                <button class="btn btn-danger btn-sm" onclick="tuChoiSinhVien(<?= $sv['id'] ?>)" title="Từ chối">
                                                                    <i class="bi bi-x-circle"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="duyet_sinh_vien.php?loai=chuyen_nganh" class="btn btn-primary btn-sm">
                                        Xem tất cả danh sách
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card fade-in-up" style="animation-delay: 0.8s;">
                <div class="card-header">
                    Thao tác nhanh
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="chon_loai_de_tai.php" class="btn btn-outline-dark text-dark w-100 py-3" style="border: 2px solid #000; background-color: white;">
                                <i class="bi bi-plus-circle text-primary fs-4 d-block mb-2"></i>
                                Tạo đề tài mới
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="danh_sach_de_tai.php" class="btn btn-outline-dark text-dark w-100 py-3" style="border: 2px solid #000; background-color: white;">
                                <i class="bi bi-journal-text text-info fs-4 d-block mb-2"></i>
                                Quản lý đề tài
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="duyet_sinh_vien.php" class="btn btn-outline-dark text-dark w-100 py-3" style="border: 2px solid #000; background-color: white;">
                                <i class="bi bi-people text-warning fs-4 d-block mb-2"></i>
                                Duyệt sinh viên
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="danh_sach_sinh_vien.php" class="btn btn-outline-dark text-dark w-100 py-3" style="border: 2px solid #000; background-color: white;">
                                <i class="bi bi-list-check text-success fs-4 d-block mb-2"></i>
                                Sinh viên của tôi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Duyệt Nhanh Sinh Viên -->
<div class="modal fade" id="duyetNhanhModal" tabindex="-1" aria-labelledby="duyetNhanhModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-body p-0" id="duyetNhanhContent">
                <!-- Nội dung sẽ được load bằng JavaScript -->
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<!-- Modal Từ Chối - z-index cao hơn để chồng lên modal duyệt nhanh -->
<div class="modal fade" id="tuChoiModal" tabindex="-1" aria-labelledby="tuChoiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fs-4">Từ chối sinh viên</h5>
            </div>
            <form id="tuChoiForm" method="POST" action="xu_ly_duyet.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="tu_choi">
                    <input type="hidden" name="dang_ky_id" id="tuChoiDangKyId">
                    <input type="hidden" name="redirect" value="dashboard">
                    <div class="alert alert-light border mb-3 p-2">
                        <strong class="fs-5">Sinh viên:</strong> <span id="tenSinhVienTuChoi" class="text-dark fs-5"></span>
                    </div>
                    <div class="mb-3">
                        <textarea name="ly_do" class="form-control fs-5" rows="6" required 
                                  placeholder="Nhập lý do từ chối..." style="min-height: 150px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem !important; display: flex !important; justify-content: flex-end !important; gap: 0.5rem !important;">
                    <button type="button" class="btn btn-primary btn-lg" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger btn-lg">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
#tuChoiModal .modal-dialog {
    max-width: 850px; /* chỉnh theo ý: 800px, 1000px, 1200px */
}
/* Modal từ chối chồng lên modal duyệt nhanh */
#tuChoiModal {
    z-index: 2000 !important;
}

#tuChoiModal .modal-backdrop {
    z-index: 1055 !important;
}

.modal-backdrop.show:nth-of-type(2) {
    z-index: 1055 !important;
    opacity: 0.7 !important;
}

/* Styling đơn giản, rõ ràng */
.table {
    margin-bottom: 0;
}

.table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 0.85rem;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding: 12px 5px;
}

.table tbody td {
    vertical-align: middle;
    padding: 12px 5px;
}

.student-item:hover {
    background-color: #f8f9fa;
}

.modal-xl {
    max-width: 1100px;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.875rem;
}
</style>

<script>
// Dữ liệu sinh viên từ PHP
const danhSachSinhVien = <?= json_encode(array_values($svChoDuyet)) ?>;

function xemChiTietDeTai(deTaiId, loai) {
    // Lọc sinh viên theo đề tài
    const sinhVienDeTai = danhSachSinhVien.filter(sv => sv.de_tai_id == deTaiId);
    
    if (sinhVienDeTai.length === 0) {
        alert('Không có sinh viên nào đăng ký đề tài này');
        return;
    }
    
    const deTai = sinhVienDeTai[0];
    const loaiDeTai = loai === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành';
    const colorClass = loai === 'co_so_nganh' ? 'primary' : 'success';
    
    let html = `
        <!-- Header đề tài -->
        <div class="p-3 border-bottom bg-light position-relative">
            <h4 class="mb-2">Tên đề tài: ${escapeHtml(deTai.tieu_de)}</h4>
            <div class="d-flex gap-4">
                <span><strong>Số lượng sinh viên chờ duyệt:</strong> <span class="text-dark">${sinhVienDeTai.length}</span></span>
            </div>
        </div>
        
        <!-- Danh sách sinh viên -->
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Danh sách sinh viên đăng ký</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">STT</th>
                            <th>SINH VIÊN</th>
                            <th style="width: 120px;">MSSV</th>
                            <th style="width: 100px;">LỚP</th>
                            <th style="width: 100px;">LOẠI</th>
                            <th style="width: 150px;">NGÀY ĐĂNG KÝ</th>
                            <th class="text-center" style="width: 200px; min-width: 200px;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
    `;
    
    sinhVienDeTai.forEach((sv, index) => {
        const ngayDangKy = sv.ngay_dang_ky ? new Date(sv.ngay_dang_ky).toLocaleString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }) : 'N/A';
        
        html += `
            <tr class="student-item" data-mssv="${sv.ma_sinh_vien}" data-name="${sv.ten_sinh_vien.toLowerCase()}" data-class="${sv.lop || ''}">
                <td class="text-center">${index + 1}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-${colorClass} text-white rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 35px; height: 35px; flex-shrink: 0;">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">${escapeHtml(sv.ten_sinh_vien)}</div>
                            <small class="text-muted">${sv.email_sinh_vien || ''}</small>
                        </div>
                    </div>
                </td>
                <td><span class="text-dark">${sv.ma_sinh_vien}</span></td>
                <td>${sv.lop || 'N/A'}</td>
                <td><span class="badge bg-${colorClass}">${loaiDeTai}</span></td>
                <td><small>${ngayDangKy}</small></td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center flex-nowrap">
                        <button class="btn btn-success btn-sm text-nowrap" onclick="duyetSinhVien(${sv.id})" title="Duyệt">
                            <i class="bi bi-check-circle"></i> Duyệt
                        </button>
                        <button class="btn btn-danger btn-sm text-nowrap" onclick="tuChoiSinhVien(${sv.id})" title="Từ chối">
                            <i class="bi bi-x-circle"></i> Từ chối
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    document.getElementById('duyetNhanhContent').innerHTML = html;
    new bootstrap.Modal(document.getElementById('duyetNhanhModal')).show();
}

function filterStudents() {
    const searchStudent = document.getElementById('searchStudent').value.toLowerCase();
    const searchClass = document.getElementById('searchClass').value.toLowerCase();
    const rows = document.querySelectorAll('#studentTableBody .student-item');
    
    rows.forEach(row => {
        const mssv = row.getAttribute('data-mssv').toLowerCase();
        const name = row.getAttribute('data-name');
        const classValue = row.getAttribute('data-class').toLowerCase();
        
        const matchStudent = mssv.includes(searchStudent) || name.includes(searchStudent);
        const matchClass = classValue.includes(searchClass);
        
        if (matchStudent && matchClass) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function duyetSinhVien(dangKyId) {
    if (confirm('Bạn có chắc muốn duyệt sinh viên này?')) {
        window.location.href = 'xu_ly_duyet.php?action=duyet&id=' + dangKyId + '&redirect=dashboard';
    }
}

function tuChoiSinhVien(dangKyId) {
    // Tìm thông tin sinh viên từ danh sách
    const sinhVien = danhSachSinhVien.find(sv => sv.id == dangKyId);
    
    document.getElementById('tuChoiDangKyId').value = dangKyId;
    
    // Hiển thị tên sinh viên trong modal
    if (sinhVien) {
        document.getElementById('tenSinhVienTuChoi').textContent = sinhVien.ten_sinh_vien + ' - ' + sinhVien.ma_sinh_vien;
    }
    
    // Mở modal từ chối KHÔNG đóng modal duyệt nhanh - modal sẽ chồng lên
    new bootstrap.Modal(document.getElementById('tuChoiModal')).show();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleSinhVien(id) {
    const element = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    
    if (element.classList.contains('show')) {
        element.classList.remove('show');
        icon.classList.remove('bi-chevron-up');
        icon.classList.add('bi-chevron-down');
    } else {
        element.classList.add('show');
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-up');
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
