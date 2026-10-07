<?php
/**
 * DUYỆT ĐỀ TÀI
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$pageTitle = 'Duyệt đề tài - Lãnh đạo';

$deTaiModel = new DeTaiModel();
$thongBaoModel = new ThongBaoModel();

// Xử lý loại đề tài - mặc định là CSN
$loaiDeTai = isset($_GET['loai']) ? trim($_GET['loai']) : 'co_so_nganh';
if (!in_array($loaiDeTai, ['co_so_nganh', 'chuyen_nganh'])) {
    $loaiDeTai = 'co_so_nganh';
}

// Lấy danh sách đề tài chờ duyệt theo loại
$deTaiChoDuyetFull = $deTaiModel->getDeTaiChoDuyet();
$deTaiChoDuyet = array_filter($deTaiChoDuyetFull, function($dt) use ($loaiDeTai) {
    return $dt['he_dao_tao'] === $loaiDeTai;
});

// Đếm tổng số đề tài theo loại
$tongCSN = count(array_filter($deTaiChoDuyetFull, function($dt) {
    return $dt['he_dao_tao'] === 'co_so_nganh';
}));
$tongCN = count(array_filter($deTaiChoDuyetFull, function($dt) {
    return $dt['he_dao_tao'] === 'chuyen_nganh';
}));

// Lấy danh sách giảng viên có đề tài chờ duyệt (theo loại)
$giangVienList = [];
foreach ($deTaiChoDuyet as $dt) {
    $gvId = $dt['giang_vien_id'];
    if (!isset($giangVienList[$gvId])) {
        $giangVienList[$gvId] = [
            'id' => $gvId,
            'ten' => $dt['ten_giang_vien'],
            'ma' => $dt['ma_giang_vien'],
            'so_luong' => 0
        ];
    }
    $giangVienList[$gvId]['so_luong']++;
}

// Lọc theo giảng viên
$filterGiangVien = isset($_GET['giang_vien']) ? (int)$_GET['giang_vien'] : 0;
if ($filterGiangVien > 0) {
    $deTaiChoDuyet = array_filter($deTaiChoDuyet, function($dt) use ($filterGiangVien) {
        return $dt['giang_vien_id'] == $filterGiangVien;
    });
}

// Xử lý xem chi tiết đề tài cụ thể
$xemChiTietId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$deTaiXemChiTiet = null;
if ($xemChiTietId) {
    $deTaiXemChiTiet = array_filter($deTaiChoDuyet, function($dt) use ($xemChiTietId) {
        return $dt['id'] == $xemChiTietId;
    });
    $deTaiXemChiTiet = !empty($deTaiXemChiTiet) ? array_values($deTaiXemChiTiet)[0] : null;
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
    /* Custom button styles cho nút xanh nước biển */
    .btn-ocean {
        background-color: #17a2b8 !important;
        border-color: #17a2b8 !important;
        color: #ffffff !important;
    }
    
    .btn-ocean i {
        color: #ffffff !important;
    }
    
    .btn-ocean:hover {
        background-color: #138496 !important;
        border-color: #117a8b !important;
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
    
    /* Ẩn thanh cuộn của table-responsive */
    .table-responsive {
        overflow: visible !important;
    }
    
    .table-responsive::-webkit-scrollbar {
        display: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <div class="nav-section-title">QUẢN LÝ HỆ THỐNG</div>
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Trang chủ
                </a>
                <a class="nav-link active" href="duyet_de_tai.php">
                    <i class="bi bi-journal-check"></i> Duyệt đề tài
                </a>
                <a class="nav-link" href="de_tai_da_duyet.php">
                    <i class="bi bi-journal-check"></i> Đề tài đã duyệt
                </a>
                <a class="nav-link" href="danh_sach_phan_cong.php">
                    <i class="bi bi-person-check"></i> Phân công chấm
                </a>
                <a class="nav-link" href="xuat_bao_cao.php">
                    <i class="bi bi-file-earmark-text"></i> Xuất danh sách
                </a>
                <a class="nav-link" href="cai_dat_thong_so.php">
                    <i class="bi bi-gear"></i> Cài đặt thông số
                </a>
                <a class="nav-link" href="quan_ly_nguoi_dung.php">
                    <i class="bi bi-people"></i> Quản lý tài khoản
                </a>
                
                <div class="nav-section-title">QUẢN LÝ NỘI DUNG</div>
                <a class="nav-link" href="quan_ly_noi_dung_do_an.php">
                    <i class="bi bi-file-earmark-text"></i> Thông báo đồ án
                </a>
                <a class="nav-link" href="quan_ly_thong_bao.php">
                    <i class="bi bi-megaphone"></i> Thông báo chung
                </a>
                <a class="nav-link" href="cau_hinh_menu.php">
                    <i class="bi bi-link-45deg"></i> Cập nhật liên kết
                </a>
                
                <div class="nav-section-title">CHỨC NĂNG GIẢNG VIÊN</div>
                <a class="nav-link" href="../giang_vien/dashboard.php">
                    <i class="bi bi-person-workspace"></i> Chế độ Giảng viên
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="col-md-10 p-4">
            <!-- Welcome Card -->
            <div class="card mb-4 fade-in-up border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2 text-dark">
                                Duyệt đề tài 
                                <?php if ($loaiDeTai === 'co_so_nganh'): ?>
                                    <strong>Cơ sở ngành</strong>
                                <?php else: ?>
                                    <strong>Chuyên ngành</strong>
                                <?php endif; ?>
                            </h3>
                            <p class="mb-0 text-muted">
                                Xem xét và phê duyệt các đề tài do giảng viên đề xuất.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="badge bg-white fs-5 px-3 py-2 border border-dark">
                                <span style="color: #dc3545; font-weight: 700;">Có: <?= count($deTaiChoDuyetFull) ?></span>
                                <span style="color: #dc3545;"> đề tài chờ duyệt</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($giangVienList)): ?>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <ul class="nav nav-tabs card-header-tabs mb-0" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $loaiDeTai === 'co_so_nganh' ? 'active' : '' ?>" 
                                   href="?loai=co_so_nganh">
                                    <i class="bi bi-journal-code"></i> Cơ sở ngành
                                    <span class="badge bg-<?= $loaiDeTai === 'co_so_nganh' ? 'primary' : 'secondary' ?> ms-1"><?= $tongCSN ?></span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $loaiDeTai === 'chuyen_nganh' ? 'active' : '' ?>" 
                                   href="?loai=chuyen_nganh">
                                    <i class="bi bi-mortarboard"></i> Chuyên ngành
                                    <span class="badge bg-<?= $loaiDeTai === 'chuyen_nganh' ? 'success' : 'secondary' ?> ms-1"><?= $tongCN ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3 mb-0">Không có đề tài chờ duyệt</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Card chứa Tabs và Bảng -->
                <div class="card shadow-sm border-0">
                    <!-- Tabs chọn loại đề tài -->
                    <div class="card-header bg-white border-bottom">
                        <ul class="nav nav-tabs card-header-tabs mb-0" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $loaiDeTai === 'co_so_nganh' ? 'active' : '' ?>" 
                                   href="?loai=co_so_nganh<?= $filterGiangVien > 0 ? '&giang_vien=' . $filterGiangVien : '' ?>">
                                    <i class="bi bi-journal-code"></i> Cơ sở ngành
                                    <span class="badge bg-<?= $loaiDeTai === 'co_so_nganh' ? 'primary' : 'secondary' ?> ms-1"><?= $tongCSN ?></span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $loaiDeTai === 'chuyen_nganh' ? 'active' : '' ?>" 
                                   href="?loai=chuyen_nganh<?= $filterGiangVien > 0 ? '&giang_vien=' . $filterGiangVien : '' ?>">
                                    <i class="bi bi-mortarboard"></i> Chuyên ngành
                                    <span class="badge bg-<?= $loaiDeTai === 'chuyen_nganh' ? 'success' : 'secondary' ?> ms-1"><?= $tongCN ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Bảng thống kê -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">STT</th>
                                        <th style="width: 150px;">MÃ GIẢNG VIÊN</th>
                                        <th>HỌ TÊN GIẢNG VIÊN</th>
                                        <th class="text-center" style="width: 180px;">SỐ ĐỀ TÀI CHỜ DUYỆT</th>
                                        <th class="text-center" style="width: 110px;">THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $index = 1; foreach ($giangVienList as $gv): ?>
                                        <tr>
                                            <td class="text-center"><?= $index++ ?></td>
                                            <td><?= htmlspecialchars($gv['ma']) ?></td>
                                            <td><?= htmlspecialchars($gv['ten']) ?></td>
                                            <td class="text-center">
                                                <span class="text-dark fs-4 px-4 py-3"><?= $gv['so_luong'] ?> </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-info btn-sm" style="width: 55px;"
                                                        onclick="xemDanhSachDeTai(<?= $gv['id'] ?>)"><i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal danh sách đề tài của giảng viên -->
<div class="modal fade" id="danhSachDeTaiModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="danhSachDeTaiTitle">Danh sách đề tài</h5>
                <button type="button" class="btn btn-sm ms-auto" id="btnChuyenTab" 
                        onclick="chuyenTabNhanh()" 
                        style="display: none;">
                    <i class="bi bi-arrow-left-right"></i>
                    <span id="btnChuyenTabText">Chuyển sang CN</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">STT</th>
                                <th>TÊN ĐỀ TÀI</th>
                                <th class="text-center" style="width: 100px;">LOẠI</th>
                                <th class="text-center" style="width: 120px;">SỐ LƯỢNG SV</th>
                                <th class="text-center" style="width: 100px;">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <input type="checkbox" id="checkAllDuyet" onchange="toggleAllDuyet()" 
                                               style="width: 18px; height: 18px; cursor: pointer;" 
                                               title="Duyệt nhanh tất cả">
                                        <span>DUYỆT</span>
                                    </div>
                                </th>
                                <th class="text-center" style="width: 80px;">TỪ CHỐI</th>
                                <th class="text-center" style="width: 150px;">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody id="danhSachDeTaiContent">
                            <!-- Nội dung sẽ được load bằng JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 2px solid #dee2e6; padding: 1.5rem;">
              <button type="button" class="btn btn-success ms-auto" onclick="xuLyHangLoat()" style="padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 600;">
                <i class="bi bi-check-circle me-2"></i>Duyệt đã chọn
              </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết -->
<div class="modal fade" id="chiTietModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border: none; border-radius: 12px; overflow: hidden; box-shadow: none !important;">
            <div class="modal-body p-0" id="chiTietContent">
                <!-- Nội dung sẽ được load bằng JavaScript -->
            </div>
            <div class="modal-footer" style="border-top: 2px solid #dee2e6; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-success" id="btnDuyetModal" style="padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-check-circle me-2"></i>Duyệt đề tài
                    </button>
                    <button type="button" class="btn btn-danger" id="btnTuChoiModal" style="padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-x-circle me-2"></i>Từ chối
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal từ chối -->
<div class="modal fade" id="tuChoiModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fs-4">
                    Từ chối đề tài
                </h5>
            </div>
            <form id="tuChoiForm" method="POST" action="xu_ly_duyet.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="tu_choi">
                    <input type="hidden" name="de_tai_id" id="tuChoiDeTaiId">
                    <input type="hidden" name="loai" id="tuChoiLoai">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectParam) ?>">
                    <input type="hidden" name="gv_id" value="<?= isset($_GET['gv_id']) ? (int)$_GET['gv_id'] : '' ?>">
                    
                    <div class="alert alert-light border mb-4 p-3">
                        <strong class="fs-5">Tên đề tài:</strong> <span id="tenDeTaiTuChoi" class="text-dark fs-5"></span>
                    </div>                  
                    <div class="mb-3">
                        <textarea name="ly_do" id="lyDoTuChoi" class="form-control fs-5" rows="6" required 
                                  placeholder="Nhập lý do từ chối..." style="min-height: 150px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem !important; display: flex !important; justify-content: flex-end !important; gap: 0.5rem !important;">
                    <button type="button" class="btn btn-primary btn-lg" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger btn-lg">
                        Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const deTaiData = <?= json_encode(array_values($deTaiChoDuyet)) ?>;
const deTaiDataFull = <?= json_encode(array_values($deTaiChoDuyetFull)) ?>;
const loaiDeTaiHienTai = '<?= $loaiDeTai ?>';
let currentDeTaiId = null;
let currentGiangVienId = null;

function xemDanhSachDeTai(giangVienId) {
    currentGiangVienId = giangVienId;
    currentFilterHe = 'all';
    
    const deTaiCuaGV = deTaiData.filter(dt => dt.giang_vien_id == giangVienId);
    if (deTaiCuaGV.length === 0) return;
    
    // Cập nhật tiêu đề modal
    document.getElementById('danhSachDeTaiTitle').textContent = 
        `Danh sách đề tài - ${deTaiCuaGV[0].ten_giang_vien} (${deTaiCuaGV[0].ma_giang_vien})`;
    
    // Kiểm tra xem giảng viên có đề tài ở tab khác không
    const loaiHienTai = loaiDeTaiHienTai;
    const loaiKhac = loaiHienTai === 'co_so_nganh' ? 'chuyen_nganh' : 'co_so_nganh';
    const deTaiTabKhac = deTaiDataFull.filter(dt => 
        dt.giang_vien_id == giangVienId && dt.he_dao_tao === loaiKhac
    );
    
    // Hiển thị/ẩn button chuyển tab
    const btnChuyenTab = document.getElementById('btnChuyenTab');
    const btnChuyenTabText = document.getElementById('btnChuyenTabText');
    
    if (deTaiTabKhac.length > 0) {
        btnChuyenTab.style.display = 'block';
        const tenTabKhac = loaiKhac === 'co_so_nganh' ? 'CSN' : 'CN';
        const colorClass = loaiKhac === 'co_so_nganh' ? 'btn-primary' : 'btn-success';
        btnChuyenTab.className = `btn btn-sm ${colorClass} ms-auto me-2`;
        btnChuyenTabText.textContent = `Chuyển sang ${tenTabKhac} (${deTaiTabKhac.length})`;
    } else {
        btnChuyenTab.style.display = 'none';
    }
    
    renderDanhSachDeTai(deTaiCuaGV);
    new bootstrap.Modal(document.getElementById('danhSachDeTaiModal')).show();
}

function renderDanhSachDeTai(deTaiList) {
    let html = '';
    deTaiList.forEach((dt, index) => {
        const heBadge = dt.he_dao_tao === 'co_so_nganh' 
            ? '<span class="badge bg-primary">CSN</span>' 
            : (dt.he_dao_tao === 'chuyen_nganh' 
                ? '<span class="badge bg-success">CN</span>'
                : `<span class="badge bg-secondary">${escapeHtml(dt.he_dao_tao || 'N/A')}</span>`);
        
        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td><strong class="text-primary">${escapeHtml(dt.tieu_de)}</strong></td>
                <td class="text-center">${heBadge}</td>
                <td class="text-center">
                    <span class="badge bg-warning text-dark">${dt.so_luong_sv}</span>
                </td>
                <td class="text-center">
                    <div class="form-check d-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" 
                               id="duyet_${dt.id}" 
                               onchange="handleDuyetChange(${dt.id})"
                               style="width: 24px; height: 24px; cursor: pointer;">
                    </div>
                </td>
                <td class="text-center">
                    <div class="form-check d-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" 
                               id="tuchoi_${dt.id}" 
                               onchange="handleTuChoiChange(${dt.id})"
                               style="width: 24px; height: 24px; cursor: pointer;">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-ocean btn-sm" 
                            onclick="xemChiTiet(${dt.id})" 
                            title="Xem chi tiết"
                            style="min-width: 100px;">
                        <i class="bi bi-eye-fill"></i> Chi tiết
                    </button>
                </td>
            </tr>
        `;
    });
    
    if (html === '') {
        html = '<tr><td colspan="7" class="text-center text-muted py-3">Không có đề tài</td></tr>';
    }
    
    document.getElementById('danhSachDeTaiContent').innerHTML = html;
}

function toggleAllDuyet() {
    const checkAllDuyet = document.getElementById('checkAllDuyet');
    const isChecked = checkAllDuyet.checked;
    
    // Lấy tất cả checkbox duyệt hiện đang hiển thị
    deTaiData.forEach(dt => {
        const duyetCheckbox = document.getElementById(`duyet_${dt.id}`);
        const tuChoiCheckbox = document.getElementById(`tuchoi_${dt.id}`);
        
        if (duyetCheckbox) {
            duyetCheckbox.checked = isChecked;
            // Nếu tích duyệt thì bỏ tích từ chối
            if (isChecked && tuChoiCheckbox) {
                tuChoiCheckbox.checked = false;
            }
        }
    });
    
    // Hiển thị thông báo
    if (isChecked) {
        const count = deTaiData.length;
        showToast('success', `Đã chọn duyệt tất cả đề tài. Nhấn "Duyệt đã chọn" để xác nhận.`);
    }
}

function handleDuyetChange(deTaiId) {
    const duyetCheckbox = document.getElementById(`duyet_${deTaiId}`);
    const tuChoiCheckbox = document.getElementById(`tuchoi_${deTaiId}`);
    
    if (duyetCheckbox.checked) {
        // Bỏ chọn từ chối nếu đang chọn
        tuChoiCheckbox.checked = false;
    }
    
    // Cập nhật trạng thái checkbox "Duyệt tất cả"
    updateCheckAllDuyetState();
}

function updateCheckAllDuyetState() {
    const checkAllDuyet = document.getElementById('checkAllDuyet');
    const allDuyetCheckboxes = deTaiData.map(dt => document.getElementById(`duyet_${dt.id}`));
    const allChecked = allDuyetCheckboxes.every(cb => cb && cb.checked);
    const someChecked = allDuyetCheckboxes.some(cb => cb && cb.checked);
    
    checkAllDuyet.checked = allChecked;
    checkAllDuyet.indeterminate = !allChecked && someChecked;
}

function showToast(type, message) {
    // Tạo toast notification đơn giản
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function handleTuChoiChange(deTaiId) {
    const duyetCheckbox = document.getElementById(`duyet_${deTaiId}`);
    const tuChoiCheckbox = document.getElementById(`tuchoi_${deTaiId}`);
    
    if (tuChoiCheckbox.checked) {
        // Bỏ chọn duyệt nếu đang chọn
        duyetCheckbox.checked = false;
        
        // Cập nhật trạng thái checkbox "Duyệt tất cả"
        updateCheckAllDuyetState();
        
        // Tìm thông tin đề tài
        const deTai = deTaiData.find(dt => dt.id == deTaiId);
        
        // Mở modal nhập lý do từ chối ngay
        currentDeTaiId = deTaiId;
        document.getElementById('tuChoiDeTaiId').value = deTaiId;
        document.getElementById('tenDeTaiTuChoi').textContent = deTai ? deTai.tieu_de : '';
        document.getElementById('lyDoTuChoi').value = '';
        
        const loai = new URLSearchParams(window.location.search).get('loai') || 'co_so_nganh';
        document.getElementById('tuChoiLoai').value = loai;
        
        const modal = new bootstrap.Modal(document.getElementById('tuChoiModal'));
        modal.show();
        
        // Focus vào textarea sau khi modal hiển thị
        document.getElementById('tuChoiModal').addEventListener('shown.bs.modal', function () {
            document.getElementById('lyDoTuChoi').focus();
        }, { once: true });
        
        // Reset checkbox khi đóng modal mà không submit
        const tuChoiModal = document.getElementById('tuChoiModal');
        tuChoiModal.addEventListener('hidden.bs.modal', function () {
            if (!tuChoiModal.submitted) {
                tuChoiCheckbox.checked = false;
            }
            tuChoiModal.submitted = false;
        }, { once: true });
    }
}

function xuLyHangLoat() {
    const duyetList = [];
    
    // Chỉ lấy danh sách đề tài được chọn duyệt
    deTaiData.forEach(dt => {
        const duyetCheckbox = document.getElementById(`duyet_${dt.id}`);
        
        if (duyetCheckbox && duyetCheckbox.checked) {
            duyetList.push({id: dt.id, tieu_de: dt.tieu_de});
        }
    });
    
    if (duyetList.length === 0) {
        alert('Vui lòng chọn ít nhất một đề tài để duyệt!');
        return;
    }
    
    // Xử lý duyệt hàng loạt
    const duyetMsg = `Duyệt ${duyetList.length} đề tài:\n` + 
                    duyetList.map(dt => `- ${dt.tieu_de}`).join('\n');
    
    if (confirm(duyetMsg + '\n\nBạn có chắc chắn?')) {
        // Chuyển hướng với danh sách ID
        const ids = duyetList.map(dt => dt.id).join(',');
        const loai = new URLSearchParams(window.location.search).get('loai') || 'co_so_nganh';
        window.location.href = `xu_ly_duyet.php?action=duyet_hang_loat&ids=${ids}&loai=${loai}`;
    }
}

let currentTuChoiList = [];

// Đánh dấu khi form từ chối được submit
document.getElementById('tuChoiForm').addEventListener('submit', function() {
    document.getElementById('tuChoiModal').submitted = true;
});

function xemChiTiet(deTaiId) {
    const deTai = deTaiData.find(dt => dt.id == deTaiId);
    if (!deTai) return;
    
    currentDeTaiId = deTaiId;
    
    const isCSN = deTai.he_dao_tao === 'co_so_nganh';
    const headerClass = isCSN ? 'csn' : 'cn';
    const badgeClass = isCSN ? 'csn' : 'cn';
    
    // Tách công nghệ thành các badge
    let congNgheHtml = '';
    if (deTai.cong_nghe) {
        const congNghes = deTai.cong_nghe.split(',');
        congNgheHtml = congNghes.map(cn => 
            `<span class="tech-badge ${badgeClass}">${escapeHtml(cn.trim())}</span>`
        ).join('');
    }
    
    let html = `
        <style>
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
                color: #000000ff;
            }
            
            .detail-badge.cn {
                color: #000000ff;
            }
            
            .detail-body {
                padding: 2rem;
            }
            
            .info-row {
                display: flex;
                align-items: center; /* Căn giữa theo chiều dọc */
                padding: 1rem;
                background: #f8f9fa;
                border-radius: 8px;
                border-left: 4px solid #0052a8;
                height: 100%;
                min-height: 80px; /* Đảm bảo chiều cao tối thiểu */
                width: 100%;
                box-sizing: border-box;
                margin: 0; /* Loại bỏ margin */
            }
            
            .info-row.cn {
                border-left-color: #1cc88a;
            }
            
            .info-icon {
                font-size: 1.5rem;
                margin-right: 1rem;
                color: #000000ff;
                flex-shrink: 0;
            }
            
            .info-icon.cn {
                color: #000000ff;
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
                word-wrap: break-word; /* Đảm bảo text không bị tràn */
            }
            
            /* Đảm bảo row luôn hiển thị ngang hàng */
            .detail-body .row.mb-4 {
                display: flex !important;
                flex-wrap: nowrap !important; /* Không cho phép xuống hàng */
                gap: 0.75rem; /* Giảm khoảng cách giữa các cột */
                margin-bottom: 1.5rem !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            
            .detail-body .row.mb-4 .col-6 {
                flex: 1 !important; /* Chia đều không gian */
                min-width: 0 !important; /* Cho phép co lại nếu cần */
                padding-left: 0 !important; /* Loại bỏ padding mặc định của Bootstrap */
                padding-right: 0 !important;
            }
            
            .detail-body .row.mb-4 .col-6:first-child {
                margin-right: 0.375rem; /* Khoảng cách bên phải cho cột đầu tiên */
            }
            
            .detail-body .row.mb-4 .col-6:last-child {
                margin-left: 0.375rem; /* Khoảng cách bên trái cho cột cuối */
            }
            
            /* Cải thiện hiển thị các phần tử bên trong info-row */
            
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
        </style>
        
        <!-- Header -->
        <div class="detail-header ${headerClass}">
            <div class="d-flex justify-content-between align-items-start">
                <h1 class="detail-title">${escapeHtml(deTai.tieu_de)}</h1>
                <span class="detail-badge ${badgeClass}">
                    ${isCSN ? 'Cơ sở ngành' : 'Chuyên ngành'}
                </span>
            </div>
        </div>
        
        <!-- Body -->
        <div class="detail-body">
            <!-- Thông tin cơ bản - 2 cột ngang hàng -->
            <div class="row mb-4">
                <div class="col-6">
                    <div class="info-row ${isCSN ? '' : 'cn'}">
                        <i class="bi bi-person-circle info-icon ${isCSN ? '' : 'cn'}"></i>
                        <div>
                            <span class="info-label">Giảng viên hướng dẫn:</span>
                            <div class="info-value">${escapeHtml(deTai.ten_giang_vien)}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="info-row ${isCSN ? '' : 'cn'}">
                        <i class="bi bi-people-fill info-icon ${isCSN ? '' : 'cn'}"></i>
                        <div>
                            <span class="info-label">Số lượng sinh viên:</span>
                            <div class="info-value">${deTai.so_luong_sv} sinh viên</div>
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
                <div class="section-content">${escapeHtml(deTai.mo_ta).replace(/\n/g, '<br>')}</div>
            </div>
    `;
    
    // Công nghệ
    if (deTai.cong_nghe) {
        html += `
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-code-slash"></i>
                    Công nghệ sử dụng
                </div>
                <div>${congNgheHtml}</div>
            </div>
        `;
    }
    
    // Yêu cầu sinh viên
    if (deTai.yeu_cau_sinh_vien) {
        html += `
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-list-check"></i>
                    Yêu cầu sinh viên
                </div>
                <div class="section-content">${escapeHtml(deTai.yeu_cau_sinh_vien).replace(/\n/g, '<br>')}</div>
            </div>
        `;
    }
    
    // Ghi chú
    if (deTai.ghi_chu) {
        html += `
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-sticky"></i>
                    Ghi chú
                </div>
                <div class="section-content">${escapeHtml(deTai.ghi_chu).replace(/\n/g, '<br>')}</div>
            </div>
        `;
    }
    
    html += `
        </div>
    `;
    
    document.getElementById('chiTietContent').innerHTML = html;
    new bootstrap.Modal(document.getElementById('chiTietModal')).show();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('btnDuyetModal').addEventListener('click', function() {
    if (currentDeTaiId && confirm('Bạn có chắc muốn duyệt đề tài này?')) {
        const loai = new URLSearchParams(window.location.search).get('loai') || 'co_so_nganh';
        window.location.href = 'xu_ly_duyet.php?action=duyet&id=' + currentDeTaiId + '&loai=' + loai;
    }
});

document.getElementById('btnTuChoiModal').addEventListener('click', function() {
    if (currentDeTaiId) {
        tuChoiDeTai(currentDeTaiId);
    }
});

function duyetDeTai(deTaiId) {
    if (confirm('Bạn có chắc muốn duyệt đề tài này?')) {
        const loai = new URLSearchParams(window.location.search).get('loai') || 'co_so_nganh';
        window.location.href = 'xu_ly_duyet.php?action=duyet&id=' + deTaiId + '&loai=' + loai;
    }
}

function duyetNhanh(deTaiId) {
    const deTai = deTaiData.find(dt => dt.id == deTaiId);
    if (!deTai) return;
    
    if (confirm(`Duyệt đề tài: "${deTai.tieu_de}"?`)) {
        const loai = new URLSearchParams(window.location.search).get('loai') || 'co_so_nganh';
        window.location.href = 'xu_ly_duyet.php?action=duyet&id=' + deTaiId + '&loai=' + loai;
    }
}

function tuChoiNhanh(deTaiId) {
    currentDeTaiId = deTaiId;
    document.getElementById('tuChoiDeTaiId').value = deTaiId;
    new bootstrap.Modal(document.getElementById('tuChoiModal')).show();
}

function tuChoiDeTai(deTaiId) {
    const deTai = deTaiData.find(dt => dt.id == deTaiId);
    
    document.getElementById('tuChoiDeTaiId').value = deTaiId;
    document.getElementById('tenDeTaiTuChoi').textContent = deTai ? deTai.tieu_de : '';
    document.getElementById('lyDoTuChoi').value = '';
    
    const loai = new URLSearchParams(window.location.search).get('loai') || 'co_so_nganh';
    document.getElementById('tuChoiLoai').value = loai;
    
    const modal = new bootstrap.Modal(document.getElementById('tuChoiModal'));
    modal.show();
    
    // Focus vào textarea sau khi modal hiển thị
    document.getElementById('tuChoiModal').addEventListener('shown.bs.modal', function () {
        document.getElementById('lyDoTuChoi').focus();
    }, { once: true });
}

// Auto-open chi tiết modal khi có parameter id
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const idParam = urlParams.get('id');
    const giangVienParam = urlParams.get('giang_vien');
    
    // Nếu có parameter giang_vien, tự động mở modal danh sách đề tài
    if (giangVienParam && !idParam) {
        const giangVienId = parseInt(giangVienParam);
        setTimeout(function() {
            xemDanhSachDeTai(giangVienId);
        }, 300);
    }
    
    if (idParam) {
        const deTaiId = parseInt(idParam);
        const deTai = deTaiData.find(dt => dt.id == deTaiId);
        
        if (deTai) {
            // Scroll to top first
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Tự động mở modal danh sách đề tài của giảng viên trước
            setTimeout(function() {
                xemDanhSachDeTai(deTai.giang_vien_id);
                
                // Sau đó mở modal chi tiết đề tài
                setTimeout(function() {
                    xemChiTiet(deTaiId);
                    
                    // Thêm hiệu ứng highlight cho modal (không thay đổi kích thước)
                    setTimeout(() => {
                        const modalContent = document.querySelector('#chiTietModal .modal-content');
                        if (modalContent) {
                            // Chỉ thay đổi màu viền nhẹ, không scale
                            modalContent.style.border = '2px solid rgba(13, 110, 253, 0.5)';
                            modalContent.style.transition = 'border 0.3s ease';
                            
                            // Remove highlight after 3 seconds
                            setTimeout(function() {
                                modalContent.style.border = 'none';
                            }, 3000);
                        }
                    }, 200);
                    
                    // Thêm thông báo nhỏ
                    showAutoOpenNotification(deTai.tieu_de);
                }, 500);
            }, 100);
        }
    }
});

// Hiển thị thông báo khi tự động mở chi tiết
function showAutoOpenNotification(tenDeTai) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-info position-fixed';
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 350px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-left: 4px solid #ff0000ff;
    `;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-back me-2"></i>   
                <strong>Đã mở chi tiết đề tài</strong><br>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        notification.style.transition = 'all 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Hàm chuyển tab nhanh
function chuyenTabNhanh() {
    if (!currentGiangVienId) return;
    
    // Đóng modal hiện tại
    const currentModal = bootstrap.Modal.getInstance(document.getElementById('danhSachDeTaiModal'));
    if (currentModal) {
        currentModal.hide();
    }
    
    // Chuyển sang tab khác
    const loaiHienTai = loaiDeTaiHienTai;
    const loaiMoi = loaiHienTai === 'co_so_nganh' ? 'chuyen_nganh' : 'co_so_nganh';
    
    // Chuyển hướng với giảng viên được giữ nguyên
    window.location.href = `?loai=${loaiMoi}&giang_vien=${currentGiangVienId}`;
}
</script>

<!-- Modal Quản lý tài khoản -->
<div class="modal fade" id="quanLyNguoiDungModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Quản lý tài khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <p class="text-muted">Tạo tài khoản mới trong hệ thống</p>
                    <div class="mt-2">
                        <a href="../auth/import_sinh_vien.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-upload"></i> Import sinh viên
                        </a>
                        <a href="../auth/import_giang_vien.php" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-upload"></i> Import giảng viên
                        </a>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <form method="POST" action="xu_ly_tao_nguoi_dung.php" id="createUserForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select name="vai_tro" id="vaiTroModal" class="form-select" required>
                                <option value="">-- Chọn vai trò --</option>
                                <option value="giang_vien">Giảng viên</option>
                                <option value="sinh_vien">Sinh viên</option>
                                <option value="lanh_dao">Lãnh đạo</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" name="ho_ten" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="mat_khau" class="form-control" minlength="6" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="xac_nhan_mat_khau" class="form-control" required>
                        </div>
                    </div>

                    <!-- Giảng viên fields -->
                    <div id="giangVienFieldsModal" class="additional-fields-modal">
                        <hr>
                        <h6>Thông tin giảng viên</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã giảng viên <span class="text-danger">*</span></label>
                                <input type="text" name="ma_giang_vien" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Khoa</label>
                                <input type="text" name="khoa" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chuyên môn</label>
                                <input type="text" name="chuyen_mon" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="so_dien_thoai" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Sinh viên fields -->
                    <div id="sinhVienFieldsModal" class="additional-fields-modal">
                        <hr>
                        <h6>Thông tin sinh viên</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã sinh viên <span class="text-danger">*</span></label>
                                <input type="text" name="ma_sinh_vien" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lớp</label>
                                <input type="text" name="lop" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Khóa học</label>
                                <input type="text" name="khoa_hoc" class="form-control" placeholder="VD: 2021-2025">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chuyên ngành</label>
                                <input type="text" name="chuyen_nganh" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai_sv" class="form-control">
                        </div>
                    </div>

                    <!-- Lãnh đạo fields -->
                    <div id="lanhDaoFieldsModal" class="additional-fields-modal">
                        <hr>
                        <h6>Thông tin lãnh đạo</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã lãnh đạo <span class="text-danger">*</span></label>
                                <input type="text" name="ma_lanh_dao" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chức vụ</label>
                                <input type="text" name="chuc_vu" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Khoa</label>
                                <input type="text" name="khoa_ld" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="so_dien_thoai_ld" class="form-control">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-person-plus"></i> Tạo tài khoản
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.additional-fields-modal {
    display: none;
    margin-top: 15px;
    padding: 15px;
    background: rgba(248, 250, 252, 0.8);
    border-radius: 10px;
    border: 1px solid #f1f5f9;
}

.additional-fields-modal h6 {
    color: #334155;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 6px;
    border-bottom: 2px solid #e2e8f0;
}

#quanLyNguoiDungModal .modal-dialog {
    max-width: 700px;
}

#quanLyNguoiDungModal .modal-content {
    border-radius: 16px;
    border: none;
}

#quanLyNguoiDungModal .modal-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0052a8 100%);
    color: white;
    border-radius: 16px 16px 0 0;
}

#quanLyNguoiDungModal .modal-title {
    font-size: 1.5rem;
    font-weight: 700;
}

#quanLyNguoiDungModal .btn-close {
    filter: brightness(0) invert(1);
}

#quanLyNguoiDungModal .modal-body {
    padding: 2rem;
}

#quanLyNguoiDungModal .form-control,
#quanLyNguoiDungModal .form-select {
    height: 44px;
    border: 1.5px solid #f1f5f9;
    border-radius: 10px;
    padding: 0 14px;
}

#quanLyNguoiDungModal .form-control:focus,
#quanLyNguoiDungModal .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
}

#quanLyNguoiDungModal .btn-primary {
    height: 48px;
    background: linear-gradient(135deg, #0d6efd 0%, #0052a8 100%);
    border: none;
    border-radius: 10px;
    font-weight: 600;
}

#quanLyNguoiDungModal .btn-primary:hover {
    background: linear-gradient(135deg, #0052a8 0%, #003d82 100%);
    transform: translateY(-2px);
}

#quanLyNguoiDungModal .text-center p {
    color: #64748b;
    font-size: 14px;
}
</style>

<script>
document.getElementById('vaiTroModal').addEventListener('change', function() {
    const selectedRole = this.value;
    
    document.querySelectorAll('.additional-fields-modal').forEach(el => {
        el.style.display = 'none';
        el.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
    });
    
    if (selectedRole === 'giang_vien') {
        let fields = document.getElementById('giangVienFieldsModal');
        fields.style.display = 'block';
        fields.querySelector('[name="ma_giang_vien"]').setAttribute('required', 'required');
    } else if (selectedRole === 'sinh_vien') {
        let fields = document.getElementById('sinhVienFieldsModal');
        fields.style.display = 'block';
        fields.querySelector('[name="ma_sinh_vien"]').setAttribute('required', 'required');
    } else if (selectedRole === 'lanh_dao') {
        let fields = document.getElementById('lanhDaoFieldsModal');
        fields.style.display = 'block';
        fields.querySelector('[name="ma_lanh_dao"]').setAttribute('required', 'required');
    }
});

function showQuanLyTaiKhoan() {
    const modal = new bootstrap.Modal(document.getElementById('quanLyNguoiDungModal'));
    modal.show();
}
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
