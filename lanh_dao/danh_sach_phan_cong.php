<?php
/**
 * DANH SÁCH PHÂN CÔNG CHẤM - VERSION 2 (Modal chọn GV)
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$pageTitle = 'Danh sách phân công chấm - Lãnh đạo';

$dangKyModel = new DangKyDeTaiModel();
$giangVienModel = new GiangVienModel();

// Lấy danh sách tất cả giảng viên để chọn
$danhSachGiangVien = $giangVienModel->getAllWithStats();

// Xử lý loại đề tài - mặc định là CSN
$loaiDeTai = isset($_GET['loai']) ? trim($_GET['loai']) : 'co_so_nganh';
if (!in_array($loaiDeTai, ['co_so_nganh', 'chuyen_nganh'])) {
    $loaiDeTai = 'co_so_nganh';
}

// Lấy danh sách phân công chấm
try {
    $danhSachPhanCongFull = $dangKyModel->getDanhSachPhanCong();
    if (!is_array($danhSachPhanCongFull)) {
        $danhSachPhanCongFull = [];
    }
    
    // Lọc theo loại đề tài
    $danhSachPhanCong = array_filter($danhSachPhanCongFull, function($pc) use ($loaiDeTai) {
        return isset($pc['he_dao_tao']) && $pc['he_dao_tao'] === $loaiDeTai;
    });
} catch (Exception $e) {
    $danhSachPhanCongFull = [];
    $danhSachPhanCong = [];
    error_log("Error getDanhSachPhanCong: " . $e->getMessage());
}

// Đếm tổng số theo loại
$tongCSN = count(array_filter($danhSachPhanCongFull, function($pc) {
    return isset($pc['he_dao_tao']) && $pc['he_dao_tao'] === 'co_so_nganh';
}));
$tongCN = count(array_filter($danhSachPhanCongFull, function($pc) {
    return isset($pc['he_dao_tao']) && $pc['he_dao_tao'] === 'chuyen_nganh';
}));

// Thống kê theo giảng viên
try {
    $thongKePhanCong = $dangKyModel->getThongKePhanCongTheoGiangVien();
    if (!is_array($thongKePhanCong)) {
        $thongKePhanCong = [];
    }
    
    // Sắp xếp theo tên giảng viên A-Z
    usort($thongKePhanCong, function($a, $b) {
        $tenA = $a['ten_giang_vien'] ?? '';
        $tenB = $b['ten_giang_vien'] ?? '';
        return strcoll($tenA, $tenB);
    });
} catch (Exception $e) {
    $thongKePhanCong = [];
    error_log("Error getThongKePhanCongTheoGiangVien: " . $e->getMessage());
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
                <a class="nav-link" href="duyet_de_tai.php">
                    <i class="bi bi-journal-check"></i> Duyệt đề tài
                </a>
                <a class="nav-link" href="de_tai_da_duyet.php">
                    <i class="bi bi-journal-check"></i> Đề tài đã duyệt
                </a>
                <a class="nav-link active" href="danh_sach_phan_cong.php">
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
                                Danh sách phân công chấm báo cáo
                                <strong><?= $loaiDeTai === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?></strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Quản lý và phân công giảng viên chấm đề tài cho sinh viên.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['xem_gv'])): 
                $giangVienId = (int)$_GET['xem_gv'];
                $giangVienInfo = null;
                foreach ($thongKePhanCong as $gv) {
                    if (isset($gv['id']) && $gv['id'] == $giangVienId) {
                        $giangVienInfo = $gv;
                        break;
                    }
                }
                
                if ($giangVienInfo):
                    $sinhVienCuaGV = [];
                    $maGiangVien = $giangVienInfo['ma_giang_vien'];
                    
                    foreach ($danhSachPhanCong as $item) {
                        if (isset($item['ma_giang_vien']) && $item['ma_giang_vien'] == $maGiangVien) {
                            $sinhVienCuaGV[] = $item;
                        }
                    }
                    ?>
                    
                    <div class="card">
                        <div class="card-header bg-<?= $loaiDeTai === 'co_so_nganh' ? 'primary' : 'success' ?> text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Giảng viên: <?= htmlspecialchars($giangVienInfo['ten_giang_vien'])?></h5>
                                    <h6 class="mb-0 opacity-75">Danh sách sinh viên hiện có (<?= count($sinhVienCuaGV) ?>)</h6>
                                </div>
                                
                                <!-- Nút chuyển tab nhanh -->
                                <?php if ($loaiDeTai === 'co_so_nganh'): ?>
                                    <a href="?xem_gv=<?= $giangVienId ?>&loai=chuyen_nganh" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-left-right"></i> Chuyển sang CN
                                    </a>
                                <?php else: ?>
                                    <a href="?xem_gv=<?= $giangVienId ?>&loai=co_so_nganh" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-left-right"></i> Chuyển sang CSN
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($sinhVienCuaGV)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2 mb-0">Chưa có sinh viên</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 50px;">STT</th>
                                                <th style="width: 160px;">Họ tên</th>
                                                <th style="width: 125px;">MSSV</th>
                                                <th style="width: 125px;">Lớp</th>
                                                <th>Tên đề tài</th>
                                                <th style="width: 250px;">Giảng viên chấm</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $idx = 1; 
                                            $db = Database::getInstance()->getConnection();
                                            foreach ($sinhVienCuaGV as $sv): 
                                                // Lấy thông tin giảng viên chấm
                                                $stmt = $db->prepare("SELECT dk.id, dk.giang_vien_cham_id, nd.ho_ten, gv.ma_giang_vien
                                                                     FROM dang_ky_de_tai dk
                                                                     LEFT JOIN giang_vien gv ON dk.giang_vien_cham_id = gv.id
                                                                     LEFT JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                                                                     WHERE dk.sinh_vien_id = (SELECT id FROM sinh_vien WHERE ma_sinh_vien = :mssv)
                                                                     AND dk.de_tai_id = (SELECT id FROM de_tai WHERE tieu_de = :de_tai LIMIT 1)
                                                                     LIMIT 1");
                                                $stmt->execute([
                                                    'mssv' => $sv['ma_sinh_vien'],
                                                    'de_tai' => $sv['ten_de_tai']
                                                ]);
                                                $dangKyInfo = $stmt->fetch();
                                                $dangKyId = $dangKyInfo['id'] ?? 0;
                                                $giangVienChamId = $dangKyInfo['giang_vien_cham_id'] ?? null;
                                                $tenGVCham = $dangKyInfo['ho_ten'] ?? null;
                                                $maGVCham = $dangKyInfo['ma_giang_vien'] ?? null;
                                            ?>
                                                <tr class="sinh-vien-row" 
                                                    data-dang-ky-id="<?= $dangKyId ?>"
                                                    data-sinh-vien="<?= htmlspecialchars($sv['ten_sinh_vien']) ?>"
                                                    data-gv-cham-id="<?= $giangVienChamId ?>"
                                                    data-gv-cham-ten="<?= htmlspecialchars($tenGVCham ?? '') ?>">
                                                    <td class="text-center"><?= $idx++ ?></td>
                                                    <td><?= htmlspecialchars($sv['ten_sinh_vien']) ?></td>
                                                    <td><?= $sv['ma_sinh_vien'] ?></td>
                                                    <td><?= isset($sv['lop']) ? htmlspecialchars($sv['lop']) : 'N/A' ?></td>
                                                    <td><?= htmlspecialchars($sv['ten_de_tai']) ?></td>
                                                    <td class="gv-cham-cell">
                                                        <div class="custom-dropdown" 
                                                             data-dang-ky-id="<?= $dangKyId ?>"
                                                             data-sinh-vien="<?= htmlspecialchars($sv['ten_sinh_vien']) ?>">
                                                            <div class="dropdown-selected">
                                                                <span class="selected-text">
                                                                    <?php if ($giangVienChamId): ?>
                                                                        <?= htmlspecialchars($tenGVCham) ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">-- Chọn giảng viên --</span>
                                                                    <?php endif; ?>
                                                                </span>
                                                                <i class="bi bi-chevron-down dropdown-arrow"></i>
                                                            </div>
                                                            <div class="dropdown-menu-custom">
                                                                <div class="dropdown-search">
                                                                    <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm...">
                                                                </div>
                                                                <div class="dropdown-options">
                                                                    <div class="dropdown-option disabled" data-value="">
                                                                        <span class="text-muted">-- Chọn giảng viên --</span>
                                                                    </div>
                                                                    <?php foreach ($danhSachGiangVien as $gv): ?>
                                                                        <?php if ($gv['id'] != $giangVienId): ?>
                                                                        <div class="dropdown-option <?= ($giangVienChamId == $gv['id']) ? 'selected' : '' ?>" 
                                                                             data-value="<?= $gv['id'] ?>"
                                                                             data-text="<?= htmlspecialchars($gv['ho_ten']) ?>">
                                                                            <?= htmlspecialchars($gv['ho_ten']) ?>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
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
                    
            <div class="mt-3">
                <a href="danh_sach_phan_cong.php?loai=<?= $loaiDeTai ?>" class="btn btn-sm text-white" style="background-color: #0066ffff;">
                    <i class="bi bi-chevron-double-left"></i> Quay lại
                </a>
            </div>
                <?php else: ?>
                    <div class="alert alert-warning">Không tìm thấy thông tin giảng viên</div>
                <?php endif; ?>
            
            <?php else: ?>
                <!-- Card chứa Tabs và Bảng -->
                <div class="card shadow-sm border-0">
                    <!-- Tabs chọn loại đề tài -->
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
                    
                    <!-- Bảng thống kê -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">STT</th>
                                        <th>HỌ TÊN GIẢNG VIÊN</th>
                                        <th class="text-center" style="width: 260px;">SINH VIÊN ĐÃ DUYỆT</th>
                                        <th class="text-center" style="width: 200px;">PHÂN CÔNG GIẢNG VIÊN</th>
                                        <th class="text-center" style="width: 150px;">TRẠNG THÁI</th>
                                        <th class="text-center" style="width: 120px;">THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Tính số lượng sinh viên đã được phân công giảng viên chấm
                                    $db = Database::getInstance()->getConnection();
                                    foreach ($thongKePhanCong as $index => $tk): 
                                        // Đếm số sinh viên đã được phân công giảng viên chấm
                                        $stmt = $db->prepare("
                                            SELECT COUNT(DISTINCT dk.id) as so_luong_phan_cong
                                            FROM dang_ky_de_tai dk
                                            JOIN de_tai dt ON dk.de_tai_id = dt.id
                                            WHERE dt.giang_vien_id = :giang_vien_id
                                            AND dk.trang_thai = 'da_duyet'
                                            AND dt.he_dao_tao = :he_dao_tao
                                            AND dk.giang_vien_cham_id IS NOT NULL
                                            AND dk.giang_vien_cham_id != 0
                                        ");
                                        $stmt->execute([
                                            'giang_vien_id' => $tk['id'],
                                            'he_dao_tao' => $loaiDeTai
                                        ]);
                                        $resultPhanCong = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $soLuongPhanCong = $resultPhanCong['so_luong_phan_cong'] ?? 0;
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td><?= isset($tk['ten_giang_vien']) ? htmlspecialchars($tk['ten_giang_vien']) : 'N/A' ?></td>
                                            <td class="text-center">
                                                <span class="text-dark fs-5 px-3 py-1">
                                                    <?= $loaiDeTai === 'co_so_nganh' ? ($tk['sv_csn'] ?? 0) : ($tk['sv_cn'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-dark fs-5 px-3 py-1">
                                                    <?= $soLuongPhanCong ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                $soSinhVien = $loaiDeTai === 'co_so_nganh' ? ($tk['sv_csn'] ?? 0) : ($tk['sv_cn'] ?? 0);
                                                ?>
                                                <?php if ($soSinhVien > 0 && $soSinhVien == $soLuongPhanCong): ?>
                                                    <span class="text-success fs-5 fw-bold">Hoàn thành</span>
                                                <?php else: ?>
                                                    <span class="text-danger fs-5 fw-bold">Chưa đủ</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="?xem_gv=<?= $tk['id'] ?>&loai=<?= $loaiDeTai ?>" class="btn btn-info btn-sm px-4 py-2">
                                                  <i class="bi bi-person-plus"></i>
                                                </a>
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

<!-- Modal chọn giảng viên chấm -->
<div class="modal fade" id="modalChonGiangVien" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title mb-0">
                    Phân công giảng viên chấm cho sinh viên - 
                    <span id="tenSinhVienModal"></span>
                    <span id="sttSinhVienBadge" class="badge bg-light text-dark ms-2"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Tìm kiếm -->
                <div class="mb-4">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-primary"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" id="searchGiangVien" 
                               placeholder=" Tìm kiếm theo tên hoặc mã giảng viên..."
                               style="font-size: 1rem;">
                    </div>
                </div>
                
                <!-- Danh sách giảng viên -->
                <div id="danhSachGiangVienModal" style="max-height: 450px; overflow-y: auto;" class="pe-2">
                    <?php foreach ($danhSachGiangVien as $gv): ?>
                        <div class="giang-vien-item mb-2" 
                             data-ten="<?= strtolower(htmlspecialchars($gv['ho_ten'])) ?>"
                             data-ma="<?= strtolower($gv['ma_giang_vien'] ?? '') ?>">
                            <button class="btn btn-light w-100 text-start chon-gv-item p-2 border" 
                                    data-gv-id="<?= $gv['id'] ?>"
                                    data-gv-ten="<?= htmlspecialchars($gv['ho_ten']) ?>"
                                    style="transition: all 0.2s;">
                                <i class="bi bi-person me-2"></i>
                                <?= htmlspecialchars($gv['ho_ten']) ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="noResultMessage" class="text-center text-muted py-5" style="display: none;">
                    <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3 mb-0">Không tìm thấy giảng viên phù hợp</p>
                    <small>Thử tìm kiếm với từ khóa khác</small>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-outline-primary" id="btnSinhVienTruoc" disabled>
                    <i class="bi bi-chevron-double-left"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnSinhVienSau">
                    <i class="bi bi-chevron-double-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết -->
<div class="modal fade" id="modalXemChiTiet" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    Thông tin phân công chấm
                    <span id="sttXemChiTiet" class="badge bg-light text-dark ms-2"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Sinh viên:</div>
                    <div class="col-8" id="detailSinhVien"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">MSSV:</div>
                    <div class="col-8" id="detailMSSV"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Mã lớp:</div>
                    <div class="col-8" id="detailLop"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Tên đề tài:</div>
                    <div class="col-8" id="detailDeTai"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Giảng viên chấm:</div>
                    <div class="col-8 text-dark" id="detailGVCham"></div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnXemTruoc">
                    <i class="bi bi-chevron-double-left"></i>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnXemSau">
                    <i class="bi bi-chevron-double-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.chon-gv-item:hover {
    background-color: #e3f2fd !important;
    border-color: #2196f3 !important;
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(33, 150, 243, 0.2);
}

.chon-gv-item:active {
    transform: translateX(5px) scale(0.98);
}

/* Custom Dropdown */
.custom-dropdown {
    position: relative;
    width: 100%;
}

.dropdown-selected {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.dropdown-selected:hover {
    border-color: #0d6efd;
    background: #f8f9fa;
}

.dropdown-selected.active {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.selected-text {
    flex: 1;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dropdown-arrow {
    margin-left: 8px;
    transition: transform 0.2s;
    font-size: 0.8rem;
}

.dropdown-selected.active .dropdown-arrow {
    transform: rotate(180deg);
}

.dropdown-menu-custom {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 4px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1060;
    display: none;
    max-height: 300px;
    overflow: hidden;
}

.dropdown-menu-custom.show {
    display: block;
}

.dropdown-search {
    padding: 8px;
    border-bottom: 1px solid #dee2e6;
}

.dropdown-search input {
    width: 100%;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 0.85rem;
}

.dropdown-search input:focus {
    outline: none;
    border-color: #0d6efd;
}

.dropdown-options {
    max-height: 250px;
    overflow-y: auto;
}

.dropdown-option {
    padding: 8px 12px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.15s;
}

.dropdown-option:hover {
    background: #f8f9fa;
}

.dropdown-option.selected {
    background: #e7f3ff;
    color: #0d6efd;
    font-weight: 500;
}

.dropdown-option.hidden {
    display: none;
}

.dropdown-option.disabled {
    cursor: not-allowed;
    opacity: 0.5;
    pointer-events: none;
}

/* Scrollbar cho dropdown */
.dropdown-options::-webkit-scrollbar {
    width: 6px;
}

.dropdown-options::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.dropdown-options::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.dropdown-options::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Fix dropdown z-index */
.gv-cham-cell {
    position: relative;
}

/* Fix dropdown position */
.table-responsive {
    overflow: visible !important;
}

td {
    position: relative;
}

#danhSachGiangVienModal::-webkit-scrollbar {
    width: 8px;
}

#danhSachGiangVienModal::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#danhSachGiangVienModal::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#danhSachGiangVienModal::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    padding: 1.5rem;
}

#searchGiangVien:focus {
    box-shadow: none;
    border-color: #2196f3;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Custom Dropdown Handler
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.custom-dropdown');
    
    dropdowns.forEach(dropdown => {
        const selected = dropdown.querySelector('.dropdown-selected');
        const menu = dropdown.querySelector('.dropdown-menu-custom');
        const searchInput = dropdown.querySelector('.dropdown-search input');
        const options = dropdown.querySelectorAll('.dropdown-option');
        const selectedText = dropdown.querySelector('.selected-text');
        
        // Toggle dropdown
        selected.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Đóng tất cả dropdown khác
            document.querySelectorAll('.dropdown-menu-custom.show').forEach(m => {
                if (m !== menu) {
                    m.classList.remove('show');
                    m.parentElement.querySelector('.dropdown-selected').classList.remove('active');
                }
            });
            
            // Toggle dropdown hiện tại
            menu.classList.toggle('show');
            selected.classList.toggle('active');
            
            if (menu.classList.contains('show')) {
                searchInput.focus();
            }
        });
        
        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    option.classList.remove('hidden');
                } else {
                    option.classList.add('hidden');
                }
            });
        });
        
        // Select option
        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const text = this.getAttribute('data-text') || this.textContent;
                const dangKyId = dropdown.getAttribute('data-dang-ky-id');
                const sinhVienTen = dropdown.getAttribute('data-sinh-vien');
                
                // Nếu click vào option đã chọn → Hủy phân công
                if (this.classList.contains('selected') && value !== '') {
                    // Update UI về trạng thái chưa chọn
                    selectedText.innerHTML = '<span class="text-muted">-- Chọn giảng viên --</span>';
                    
                    // Remove selected class
                    options.forEach(opt => opt.classList.remove('selected'));
                    
                    // Close dropdown
                    menu.classList.remove('show');
                    selected.classList.remove('active');
                    searchInput.value = '';
                    options.forEach(opt => opt.classList.remove('hidden'));
                    
                    // Hủy phân công (gửi value rỗng)
                    if (dangKyId) {
                        saveGiangVienCham(dangKyId, '', sinhVienTen, '');
                    }
                    return;
                }
                
                // Update UI
                selectedText.innerHTML = text || '<span class="text-muted">-- Chọn giảng viên --</span>';
                
                // Remove selected class from all options
                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                // Close dropdown
                menu.classList.remove('show');
                selected.classList.remove('active');
                searchInput.value = '';
                options.forEach(opt => opt.classList.remove('hidden'));
                
                // Save to server
                if (dangKyId) {
                    saveGiangVienCham(dangKyId, value, sinhVienTen, text);
                }
            });
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu-custom.show').forEach(menu => {
            menu.classList.remove('show');
            menu.parentElement.querySelector('.dropdown-selected').classList.remove('active');
        });
    });
});

// Save giảng viên chấm
function saveGiangVienCham(dangKyId, giangVienId, sinhVienTen, tenGV) {
    fetch('xu_ly_phan_cong.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=phan_cong&dang_ky_id=${dangKyId}&giang_vien_id=${giangVienId}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi kết nối. Vui lòng thử lại.');
    });
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

<?php include_once __DIR__ . '/includes/modal_quan_ly_tai_khoan.php'; ?>

<script src="js_phan_cong_cham.js?v=<?= time() ?>"></script>

</body>
</html>
