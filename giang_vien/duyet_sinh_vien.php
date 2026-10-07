<?php
/**
 * DUYỆT ĐỀ TÀI CHỜ DUYỆT
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$pageTitle = 'Duyệt đề tài - Giảng viên';

$giangVienModel = new GiangVienModel();
$dangKyModel = new DangKyDeTaiModel();
$thongBaoModel = new ThongBaoModel();
$deTaiModel = new DeTaiModel();

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

// Lấy danh sách sinh viên chờ duyệt
$danhSachChoDuyet = $dangKyModel->getDanhSachDangKy($giangVien['id'], ['trang_thai' => STATUS_CHO_DUYET]);

// Lọc theo loại đề tài và nhóm theo đề tài
$deTaiChoDuyet = [];
$thongTinDeTai = [];

foreach ($danhSachChoDuyet as $dk) {
    $deTaiId = $dk['de_tai_id'];
    
    // Lấy thông tin đề tài nếu chưa có
    if (!isset($thongTinDeTai[$deTaiId])) {
        $thongTinDeTai[$deTaiId] = $deTaiModel->findById($deTaiId);
    }
    
    // Chỉ lấy đề tài theo loại đang chọn
    if ($thongTinDeTai[$deTaiId]['he_dao_tao'] !== $loaiDeTai) {
        continue;
    }
    
    // Nhóm sinh viên theo đề tài
    if (!isset($deTaiChoDuyet[$deTaiId])) {
        $deTaiChoDuyet[$deTaiId] = [
            'de_tai' => $thongTinDeTai[$deTaiId],
            'sinh_vien' => []
        ];
    }
    
    $deTaiChoDuyet[$deTaiId]['sinh_vien'][] = $dk;
}

// Đếm tổng số theo loại (loại trừ sinh viên đã có đề tài được duyệt cùng loại)
$tongCSN = 0;
$tongCN = 0;
foreach ($danhSachChoDuyet as $dk) {
    $deTaiId = $dk['de_tai_id'];
    if (!isset($thongTinDeTai[$deTaiId])) {
        $thongTinDeTai[$deTaiId] = $deTaiModel->findById($deTaiId);
    }
    
    // Kiểm tra sinh viên đã có đề tài được duyệt cùng loại chưa
    $heDaoTao = $thongTinDeTai[$deTaiId]['he_dao_tao'];
    $sinhVienDaDu = false;
    
    if ($heDaoTao === 'co_so_nganh') {
        $sinhVienDaDu = in_array($dk['sinh_vien_id'], $sinhVienDaDuCSN);
    } else {
        $sinhVienDaDu = in_array($dk['sinh_vien_id'], $sinhVienDaDuCN);
    }
    
    // Chỉ đếm nếu sinh viên chưa có đề tài được duyệt cùng loại
    if (!$sinhVienDaDu) {
        if ($heDaoTao === 'co_so_nganh') {
            $tongCSN++;
        } else {
            $tongCN++;
        }
    }
}

// Xử lý xem chi tiết đề tài
$xemChiTiet = isset($_GET['xem']) ? (int)$_GET['xem'] : null;
$deTaiXem = null;
$sinhVienCuaDeTai = [];
$soSinhVienThucTe = 0; // Số sinh viên chưa có đề tài được duyệt cùng loại

if ($xemChiTiet && isset($deTaiChoDuyet[$xemChiTiet])) {
    $deTaiXem = $deTaiChoDuyet[$xemChiTiet]['de_tai'];
    $sinhVienCuaDeTai = $deTaiChoDuyet[$xemChiTiet]['sinh_vien'];
    
    // Đếm số sinh viên thực tế (loại trừ sinh viên đã có đề tài được duyệt cùng loại)
    foreach ($sinhVienCuaDeTai as $dk) {
        $sinhVienDaDu = false;
        if ($loaiDeTai === 'co_so_nganh') {
            $sinhVienDaDu = in_array($dk['sinh_vien_id'], $sinhVienDaDuCSN);
        } else {
            $sinhVienDaDu = in_array($dk['sinh_vien_id'], $sinhVienDaDuCN);
        }
        
        if (!$sinhVienDaDu) {
            $soSinhVienThucTe++;
        }
    }
    
    // Áp dụng tìm kiếm trong chi tiết đề tài
    if (!empty($_GET['search_mssv']) || !empty($_GET['search_ten']) || !empty($_GET['search_lop'])) {
        $sinhVienCuaDeTai = array_filter($sinhVienCuaDeTai, function($dk) {
            // Tìm theo MSSV
            if (!empty($_GET['search_mssv'])) {
                if (stripos($dk['ma_sinh_vien'], $_GET['search_mssv']) === false) {
                    return false;
                }
            }
            
            // Tìm theo tên sinh viên
            if (!empty($_GET['search_ten'])) {
                if (stripos($dk['ten_sinh_vien'], $_GET['search_ten']) === false) {
                    return false;
                }
            }
            
            // Tìm theo lớp
            if (!empty($_GET['search_lop'])) {
                if (stripos($dk['lop'], $_GET['search_lop']) === false) {
                    return false;
                }
            }
            
            return true;
        });
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
.table-warning {
    background-color: #fff3cd !important;
}

.row-checkbox:disabled {
    opacity: 0.5;
}

.btn-group .btn {
    margin-right: 2px;
}

.card-header .row {
    margin: 0;
}

.badge {
    font-size: 0.8em;
}

.table td {
    vertical-align: middle;
}

.modal-dialog.modal-lg {
    max-width: 800px;
}

.alert ul {
    padding-left: 1.2rem;
}

/* Search form styling */
#searchForm .form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.25rem;
}

#searchForm .form-control-sm, 
#searchForm .form-select-sm {
    font-size: 0.875rem;
}

#searchForm .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}

#searchForm .card-body {
    padding: 1rem;
}

.search-result-info {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.de-tai-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.de-tai-item:hover {
    background-color: #f8f9fa;
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
                <a class="nav-link" href="chon_loai_de_tai.php">
                    <i class="bi bi-plus-circle"></i> Tạo đề tài mới
                </a>
                <a class="nav-link" href="danh_sach_de_tai.php">
                    <i class="bi bi-journal-text"></i> Danh sách đề tài
                </a>
                <a class="nav-link active" href="duyet_sinh_vien.php">
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
                                Duyệt sinh viên đăng ký đề tài 
                                <strong><?= $loaiDeTai === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?></strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Xem xét và phê duyệt sinh viên đăng ký đề tài của bạn.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($xemChiTiet && $deTaiXem): ?>
                <!-- Chi tiết đề tài và danh sách sinh viên -->
                <!-- Thông tin đề tài -->
                <div class="card mb-4" id="chi-tiet-de-tai-<?= $xemChiTiet ?>">
                    <div class="card-header bg-secondary text-white text-start">
                        <h5 class="mb-0">Chi tiết đề tài và sinh viên đăng ký</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <h3><strong>Tên đề tài:</strong> <?= htmlspecialchars($deTaiXem['tieu_de']) ?></h3>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5><strong>Mô tả:</strong> <?= htmlspecialchars($deTaiXem['mo_ta']) ?></h5>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5><strong>Số lượng:</strong> 
                                <strong class="<?= ($deTaiXem['so_luong_da_dang_ky'] >= $deTaiXem['so_luong_sv']) ? 'text-danger' : '' ?>">
                                    <?= $deTaiXem['so_luong_da_dang_ky'] ?>/<?= $deTaiXem['so_luong_sv'] ?>
                                </strong></h5>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5><strong>Loại đề tài:</strong> <?= getHeDaoTaoLabel($deTaiXem['he_dao_tao']) ?></h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <h5><strong>Sinh viên chờ duyệt:</strong> <span class=" text-black fs-4"><?= $soSinhVienThucTe ?></span></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danh sách sinh viên -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Danh sách sinh viên đăng ký</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Thanh tìm kiếm -->
                        <form method="GET" action="" id="searchForm" class="mb-3">
                            <input type="hidden" name="xem" value="<?= $xemChiTiet ?>">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm" name="search_mssv" 
                                           placeholder="Tìm theo MSSV..." value="<?= htmlspecialchars($_GET['search_mssv'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm" name="search_ten" 
                                           placeholder="Tên sinh viên..." value="<?= htmlspecialchars($_GET['search_ten'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm" name="search_lop" 
                                           placeholder="Lớp..." value="<?= htmlspecialchars($_GET['search_lop'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <div class="btn-group w-100" role="group">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-search"></i> Tìm kiếm
                                        </button>
                                        <a href="duyet_sinh_vien.php?xem=<?= $xemChiTiet ?>" class="btn btn-danger btn-sm">
                                            <i class="bi bi-arrow-clockwise"></i> Làm mới
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Hiển thị kết quả tìm kiếm -->
                        <?php if (!empty($_GET['search_mssv']) || !empty($_GET['search_ten']) || !empty($_GET['search_lop'])): ?>
                            <div class="alert alert-info mb-3">
                                Tìm thấy <strong><?= count($sinhVienCuaDeTai) ?></strong> kết quả từ <strong><?= count($deTaiChoDuyet[$xemChiTiet]['sinh_vien']) ?></strong> sinh viên
                                <?php if (count($sinhVienCuaDeTai) === 0): ?>
                                    <br><small>Thử điều chỉnh điều kiện tìm kiếm hoặc <a href="duyet_sinh_vien.php?xem=<?= $xemChiTiet ?>">xóa bộ lọc</a></small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($sinhVienCuaDeTai)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Không có sinh viên nào</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">STT</th>
                                            <th>Sinh viên</th>
                                            <th>MSSV</th>
                                            <th>Lớp</th>
                                            <th>Loại</th>
                                            <th>Ngày đăng ký</th>
                                            <th>Trạng thái</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $index = 1;
                                        $daDuSoLuong = ($deTaiXem['so_luong_da_dang_ky'] >= $deTaiXem['so_luong_sv']);
                                        foreach ($sinhVienCuaDeTai as $dk): 
                                        ?>
                                            <tr class="<?= $daDuSoLuong ? 'table-warning' : '' ?>">
                                                <td class="text-center"><?= $index++ ?></td>
                                                <td><strong><?= htmlspecialchars($dk['ten_sinh_vien']) ?></strong></td>
                                                <td><?= $dk['ma_sinh_vien'] ?></td>
                                                <td><?= htmlspecialchars($dk['lop']) ?></td>
                                                <td><?= getHeDaoTaoLabel($dk['he_dao_tao']) ?></td>
                                                <td><?= formatDate($dk['ngay_dang_ky']) ?></td>
                                                <td>
                                                    <?php 
                                                    // Kiểm tra sinh viên đã có đề tài được duyệt cùng loại chưa
                                                    $sinhVienDaDu = false;
                                                    if ($loaiDeTai === 'co_so_nganh') {
                                                        $sinhVienDaDu = in_array($dk['sinh_vien_id'], $sinhVienDaDuCSN);
                                                    } else {
                                                        $sinhVienDaDu = in_array($dk['sinh_vien_id'], $sinhVienDaDuCN);
                                                    }
                                                    ?>
                                                    
                                                    <?php if ($sinhVienDaDu): ?>
                                                        <span class="badge bg-danger text-white fs-6 px-3 py-2">
                                                            Sinh viên đã có đề tài: <?= getHeDaoTaoLabel($loaiDeTai) ?> được duyệt
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-white fs-6 px-2 py-2">Chờ duyệt</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($sinhVienDaDu): ?>
                                                        <!-- Sinh viên đã có đề tài được duyệt cùng loại -->
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="xoaDangKy(<?= $dk['id'] ?>)"
                                                                title="Xóa đăng ký này">
                                                            <i class="bi bi-trash-fill"></i> Xóa
                                                        </button>
                                                    <?php else: ?>
                                                        <!-- Sinh viên chưa có đề tài được duyệt cùng loại -->
                                                        <?php if (!$daDuSoLuong): ?>
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="duyetSinhVien(<?= $dk['id'] ?>)">
                                                                <i class="bi bi-check-circle"></i> Duyệt
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="tuChoiSinhVien(<?= $dk['id'] ?>)">
                                                            <i class="bi bi-x-circle"></i> Từ chối
                                                        </button>
                                                        
                                                        <?php if ($daDuSoLuong): ?>
                                                            <small class="text-muted">
                                                                <i class="bi bi-info-circle"></i> Đề tài đã đủ
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Nút Quay lại -->
                <div class="mt-3 d-flex justify-content-start">
                    <a href="duyet_sinh_vien.php?loai=<?= $loaiDeTai ?>" class="btn btn-sm text-white" style="background-color: #0d6efd;">
                        <i class="bi bi-chevron-double-left"></i> Quay lại
                    </a>
                </div>
                
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
                    
                    <!-- Bảng danh sách đề tài -->
                    <div class="card-body p-0">
                        <?php if (empty($deTaiChoDuyet)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Không có đề tài <?= $loaiDeTai === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?> nào chờ duyệt</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">STT</th>
                                            <th>Tên đề tài</th>
                                            <th>Loại đề tài</th>
                                            <th class="text-center">Sinh viên chờ duyệt</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $index = 1;
                                        foreach ($deTaiChoDuyet as $deTaiId => $item): 
                                            $deTai = $item['de_tai'];
                                            
                                            // Đếm số sinh viên thực tế (loại trừ sinh viên đã có đề tài được duyệt cùng loại)
                                            $soSinhVienThucTe = 0;
                                            foreach ($item['sinh_vien'] as $dk) {
                                                $sinhVienDaDu = false;
                                                if ($loaiDeTai === 'co_so_nganh') {
                                                    $sinhVienDaDu = in_array($dk['sinh_vien_id'], $sinhVienDaDuCSN);
                                                } else {
                                                    $sinhVienDaDu = in_array($dk['sinh_vien_id'], $sinhVienDaDuCN);
                                                }
                                                
                                                if (!$sinhVienDaDu) {
                                                    $soSinhVienThucTe++;
                                                }
                                            }
                                            
                                            $daDuSoLuong = ($deTai['so_luong_da_dang_ky'] >= $deTai['so_luong_sv']);
                                        ?>
                                            <tr class="de-tai-item <?= $daDuSoLuong ? 'table-warning' : '' ?>">
                                                <td class="text-center"><?= $index++ ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($deTai['tieu_de']) ?></strong>
                                                </td>
                                                <td><?= getHeDaoTaoLabel($deTai['he_dao_tao']) ?></td>
                                                <td class="text-center">
                                                    <span class="text-black fs-6"><?= $soSinhVienThucTe ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="?xem=<?= $deTaiId ?>&loai=<?= $loaiDeTai ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal từ chối -->
<div class="modal fade" id="tuChoiModal" tabindex="-1">
    <div class="modal-dialog" style="max-width: 850px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fs-4">Từ chối sinh viên</h5>
            </div>
            <form id="tuChoiForm" method="POST" action="xu_ly_duyet.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="tu_choi">
                    <input type="hidden" name="dang_ky_id" id="tuChoiDangKyId">
                    <div class="alert alert-light border mb-4 p-3">
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

<script>
function duyetSinhVien(dangKyId) {
    if (confirm('Bạn có chắc muốn duyệt sinh viên này?')) {
        const urlParams = new URLSearchParams(window.location.search);
        const loai = urlParams.get('loai') || 'co_so_nganh';
        const xem = urlParams.get('xem');
        window.location.href = 'xu_ly_duyet.php?action=duyet&id=' + dangKyId + '&loai=' + loai + (xem ? '&xem=' + xem : '');
    }
}

function tuChoiSinhVien(dangKyId) {
    document.getElementById('tuChoiDangKyId').value = dangKyId;
    
    // Tìm thông tin sinh viên từ bảng
    const row = document.querySelector(`button[onclick="tuChoiSinhVien(${dangKyId})"]`).closest('tr');
    if (row) {
        const tenSV = row.querySelector('td:nth-child(2) strong')?.textContent || '';
        const mssv = row.querySelector('td:nth-child(3)')?.textContent || '';
        document.getElementById('tenSinhVienTuChoi').textContent = tenSV + ' - ' + mssv;
    }
    
    new bootstrap.Modal(document.getElementById('tuChoiModal')).show();
}

function xoaDangKy(dangKyId) {
    if (confirm('Bạn có chắc chắn muốn xóa đăng ký này?\n\nSinh viên này đã có đề tài được duyệt cùng loại, xóa đăng ký sẽ giúp giảm số lượng sinh viên chờ duyệt.')) {
        const urlParams = new URLSearchParams(window.location.search);
        const loai = urlParams.get('loai') || 'co_so_nganh';
        const xem = urlParams.get('xem');
        window.location.href = 'xoa_dang_ky_gv.php?id=' + dangKyId + '&loai=' + loai + (xem ? '&xem=' + xem : '');
    }
}

// Thêm loại đề tài vào form từ chối
document.getElementById('tuChoiForm').addEventListener('submit', function(e) {
    const urlParams = new URLSearchParams(window.location.search);
    const loai = urlParams.get('loai') || 'co_so_nganh';
    const xem = urlParams.get('xem');
    
    const loaiInput = document.createElement('input');
    loaiInput.type = 'hidden';
    loaiInput.name = 'loai';
    loaiInput.value = loai;
    this.appendChild(loaiInput);
    
    if (xem) {
        const xemInput = document.createElement('input');
        xemInput.type = 'hidden';
        xemInput.name = 'xem';
        xemInput.value = xem;
        this.appendChild(xemInput);
    }
});

// Auto scroll to chi tiết đề tài khi có parameter xem
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const xemParam = urlParams.get('xem');
    
    if (xemParam) {
        const targetElement = document.getElementById('chi-tiet-de-tai-' + xemParam);
        if (targetElement) {
            // Scroll to element with smooth animation
            setTimeout(function() {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Add highlight effect
                targetElement.style.boxShadow = '0 0 20px rgba(13, 110, 253, 0.5)';
                targetElement.style.transition = 'box-shadow 0.3s ease';
                
                // Remove highlight after 2 seconds
                setTimeout(function() {
                    targetElement.style.boxShadow = '';
                }, 2000);
            }, 100);
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
