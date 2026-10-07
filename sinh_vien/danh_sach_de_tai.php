<?php
/**
 * DANH SÁCH ĐỀ TÀI CÓ THỂ ĐĂNG KÝ
 */

require_once '../bootstrap.php';
requireRole(ROLE_SINH_VIEN);

$user = getCurrentUser();
$pageTitle = 'Danh sách đề tài - Sinh viên';

$sinhVienModel = new SinhVienModel();
$deTaiModel = new DeTaiModel();

$sinhVien = $sinhVienModel->getByNguoiDungId($user['id']);

// Kiểm tra sinh viên đã đủ số lượng đề tài chưa
$daDuSoLuong = $sinhVienModel->daDuSoLuongDeTai($sinhVien['id']);

// Kiểm tra từng hệ
$daDuCSN = $sinhVienModel->daDuDeTaiTheoHe($sinhVien['id'], 'co_so_nganh');
$daDuCN = $sinhVienModel->daDuDeTaiTheoHe($sinhVien['id'], 'chuyen_nganh');

// Lọc - xử lý cẩn thận để tránh lỗi parameter
$filters = [];

// Xử lý loại đề tài - mặc định là CSN
$loaiDeTai = isset($_GET['loai']) ? trim($_GET['loai']) : 'co_so_nganh';
if (!in_array($loaiDeTai, ['co_so_nganh', 'chuyen_nganh'])) {
    $loaiDeTai = 'co_so_nganh';
}
$filters['he_dao_tao'] = $loaiDeTai;

// Xử lý search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $filters['search'] = sanitize($search);
}

$danhSachDeTai = $deTaiModel->getDeTaiDaDuyet($filters);

// Phân loại đề tài: còn chỗ và đã đủ
$deTaiConCho = [];
$deTaiDaDu = [];

foreach ($danhSachDeTai as $dt) {
    if ($dt['con_lai'] > 0) {
        $deTaiConCho[] = $dt;
    } else {
        $deTaiDaDu[] = $dt;
    }
}

// Gộp lại: đề tài còn chỗ lên trên, đề tài đã đủ xuống dưới
$danhSachDeTai = array_merge($deTaiConCho, $deTaiDaDu);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Custom button styles cho nút xem chi tiết và đăng ký */
.btn-ocean {
    background-color: #0052a8 !important;
    border-color: #0052a8 !important;
    color: #ffffffff !important;
}

.btn-ocean i {
    color: #ffffffff !important;
}

.btn-ocean:hover {
    background-color: #0052a8 !important;
    border-color: #0052a8 !important;
    color: #ffffffff !important;
}

.btn-ocean:hover i {
    color: #ffffffff !important;
}

.btn-ocean:active,
.btn-ocean:focus {
    background-color: #002affff !important;
    border-color: #002affff !important;
    color: #ffffffff !important;
    box-shadow: none !important;
}

/* Custom tabs styling */
.tabs-container {
    max-width: 800px;
    margin: 0 auto;
}

.custom-tabs {
    border: none;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 2px solid #000000;
}

.custom-tabs .nav-item {
    flex: 1;
}

.custom-tabs .nav-link {
    border: none;
    border-radius: 8px;
    padding: 18px 24px;
    font-size: 1.1rem;
    font-weight: 600;
    color: #6c757d;
    transition: all 0.3s ease;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: transparent;
    margin: 0 4px;
}

.custom-tabs .nav-link i {
    font-size: 1.3rem;
}

.custom-tabs .nav-link .tab-text {
    font-size: 1.1rem;
}

.custom-tabs .nav-link:hover {
    background: #e9ecef;
    color: #495057;
    transform: translateY(-2px);
}

.custom-tabs .nav-link.active i {
    color: #ffffff;
}

.custom-tabs .nav-link .badge {
    font-size: 0.75rem;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
}

/* Màu xanh dương đậm cho Cơ sở ngành */
.custom-tabs .nav-item:first-child .nav-link.active {
    background: linear-gradient(135deg, #0052a8 0%, #003d82 100%);
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(0, 82, 168, 0.4);
}

.custom-tabs .nav-item:first-child .nav-link.active .badge {
    background: #ffffff !important;
    color: #0052a8 !important;
}

/* Màu xanh lá sáng cho Chuyên ngành */
.custom-tabs .nav-item:last-child .nav-link.active {
    background: linear-gradient(135deg, #2db173ff 0%, #2db173ff 100%);
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(28, 200, 138, 0.4);
}

.custom-tabs .nav-item:last-child .nav-link.active .badge {
    background: #ffffff !important;
    color: #009c63ff !important;
}

@media (max-width: 768px) {
    .tabs-container {
        max-width: 100%;
    }
    
    .custom-tabs .nav-link {
        padding: 14px 16px;
        font-size: 0.95rem;
        flex-direction: column;
        gap: 4px;
    }
    
    .custom-tabs .nav-link i {
        font-size: 1.5rem;
    }
    
    .custom-tabs .nav-link .tab-text {
        font-size: 0.9rem;
    }
}

/* Compact search form */
.compact-search-card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.compact-search-card .card-body {
    padding: 0.75rem 1rem;
}

.compact-search-card .form-label {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.compact-search-card .form-control {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.compact-search-card .btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
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
            <!-- Welcome Card -->
            <div class="card mb-4 fade-in-up border-dark" style="border-width: 2px !important;">
                <div class="card-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-8">
                            <h3 class="mb-2 text-dark">Danh sách đề tài <strong><?= getHeDaoTaoLabel($loaiDeTai) ?></strong></h3>
                            <p class="mb-0 text-muted">Tìm kiếm và đăng ký đề tài phù hợp với bạn.</p>
                        </div>
                    </div>
                    
                    <!-- Form tìm kiếm -->
                    <div class="row">
                        <div class="col-md-8">
                            <form method="GET" action="" id="filterForm">
                                <input type="hidden" name="loai" value="<?= $loaiDeTai ?>">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Nhập tên đề tài hoặc tên giảng viên..."
                                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    <?php if (!empty($search)): ?>
                                        <a href="?loai=<?= $loaiDeTai ?>" class="btn btn-danger" title="Xóa tìm kiếm">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($search)): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Tìm thấy <strong><?= count($danhSachDeTai) ?></strong> kết quả cho "<strong><?= htmlspecialchars($search) ?></strong>"
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs chọn loại đề tài -->
            <div class="tabs-container mb-4">
                <ul class="nav nav-tabs nav-justified custom-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $loaiDeTai === 'co_so_nganh' ? 'active' : '' ?>" 
                           href="?loai=co_so_nganh<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                            <i class="bi bi-journal-code"></i> 
                            <span class="tab-text">Cơ sở ngành</span>
                            <?php if ($daDuCSN): ?>
                                <span class="badge bg-danger ms-2">Bạn đã có đề tài</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $loaiDeTai === 'chuyen_nganh' ? 'active' : '' ?>" 
                           href="?loai=chuyen_nganh<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                            <i class="bi bi-mortarboard"></i> 
                            <span class="tab-text">Chuyên ngành</span>
                            <?php if ($daDuCN): ?>
                                <span class="badge bg-danger ms-2">Bạn đã có đề tài</span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Danh sách đề tài -->
            <div class="row">
                <?php if (empty($danhSachDeTai)): ?>
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Không tìm thấy đề tài nào</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                    <?php foreach ($danhSachDeTai as $dt): ?>
                        <div class="col-md-12 mb-4">
                            <div class="card h-100 shadow-sm border-0 hover-shadow transition <?= $dt['con_lai'] <= 0 ? 'opacity-75' : '' ?>">
                                <!-- Header với gradient -->
                                <div class="card-header bg-gradient-<?= $dt['he_dao_tao'] === 'co_so_nganh' ? 'primary' : 'success' ?> border-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="mb-0 fw-bold text-danger" style="font-size: 1rem; line-height: 1.4; color: #ffff !important; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                            <?= htmlspecialchars($dt['tieu_de']) ?>
                                            <?php if ($dt['con_lai'] <= 0): ?>
                                                <span class="badge bg-danger text-white ms-2">ĐÃ ĐỦ SỐ LƯỢNG SINH VIÊN ĐĂNG KÝ</span>
                                            <?php endif; ?>
                                        </h5>
                                        <span class="badge bg-white text-<?= $dt['he_dao_tao'] === 'co_so_nganh' ? 'primary' : 'success' ?> ms-2 flex-shrink-0">
                                            <?= getHeDaoTaoLabel($dt['he_dao_tao']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Body -->
                                <div class="card-body" style="padding: 2rem 1rem 0.5rem 1rem;">
                                    <!-- Giảng viên -->
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-person-circle text-primary me-2" style="font-size: 1.1rem;"></i>
                                        <span class="text-muted me-1" style="font-size: 0.85rem;">Giảng viên:</span>
                                        <strong style="font-size: 0.9rem;"><?= htmlspecialchars($dt['ten_giang_vien']) ?></strong>
                                    </div>
                                    
                                    <!-- Công nghệ -->
                                    <?php if (!empty($dt['cong_nghe'])): ?>
                                        <div class="d-flex align-items-center flex-wrap mb-2">
                                            <i class="bi bi-code-slash text-success me-2" style="font-size: 1.1rem;"></i>
                                            <span class="text-muted me-2" style="font-size: 0.85rem;">Công nghệ sử dụng:</span>
                                            <?php 
                                            $congNghes = explode(',', $dt['cong_nghe']);
                                            foreach (array_slice($congNghes, 0, 3) as $cn): 
                                            ?>
                                                <span class="badge bg-light text-dark border me-1" style="font-size: 0.75rem;">
                                                    <?= htmlspecialchars(trim($cn)) ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (count($congNghes) > 3): ?>
                                                <span class="badge bg-light text-muted border" style="font-size: 0.75rem;">
                                                    +<?= count($congNghes) - 3 ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Số lượng -->
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-people-fill text-info me-1" style="font-size: 1.1rem;"></i>
                                        <span class="text-muted me-0" style="font-size: 0.85rem;">Số lượng:</span>
                                        <span class="badge bg-white text-dark px-1 py-0" style="font-size: 0.85rem;">
                                            Còn <?= $dt['con_lai'] ?>/<?= $dt['so_luong_sv'] ?> chỗ
                                        </span>
                                    </div>
                                    
                                    <!-- Ghi chú -->
                                    <?php if (!empty($dt['ghi_chu'])): ?>
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-sticky text-warning me-2" style="font-size: 1.1rem;"></i>
                                            <div>
                                                <span class="text-muted me-0" style="font-size: 0.85rem;">Ghi chú:</span>
                                                <span style="font-size: 0.85rem;"><?= htmlspecialchars($dt['ghi_chu']) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Footer -->
                                <div class="card-footer bg-light border-0">
                                    <div class="d-grid gap-2">
                                        <?php if ($dt['con_lai'] <= 0): ?>
                                        <?php else: ?>
                                            <!-- Đề tài còn chỗ - hiển thị nút bình thường -->
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="chi_tiet_de_tai.php?id=<?= $dt['id'] ?>" 
                                                   class="btn btn-ocean btn-sm">
                                                    <i class="bi bi-eye-fill me-2"></i>Xem chi tiết
                                                </a>
                                                <?php 
                                                // Kiểm tra điều kiện đăng ký theo hệ
                                                $khongTheDangKy = false;
                                                $lyDo = '';
                                                
                                                if ($loaiDeTai === 'co_so_nganh' && $daDuCSN) {
                                                    $khongTheDangKy = true;
                                                    $lyDo = 'Bạn đã có đề tài Cơ sở ngành được duyệt, không thể đăng ký đề tài này';
                                                } elseif ($loaiDeTai === 'chuyen_nganh' && $daDuCN) {
                                                    $khongTheDangKy = true;
                                                    $lyDo = 'Bạn đã có đề tài Chuyên ngành được duyệt, không thể đăng ký đề tài này';
                                                }
                                                ?>
                                                
                                                <?php 
                                                //Cài đặt thời hạn đăng ký đề tài
                                                $openTime = strtotime('2026-09-22 07:00:00');
                                                $closeTime = strtotime('2026-09-23 23:59:59');
                                                if ($loaiDeTai == 'chuyen_nganh')
                                                {
                                                    $openTime = strtotime('2026-09-24 07:00:00');
                                                    $closeTime = strtotime('2026-09-25 23:59:59');
                                                }
                                                $currentTime = time();

                                                if ($currentTime < $openTime) {
                                                    $lyDo = "Chưa đến ngày đăng ký";
                                                }
                                                
                                                if ($currentTime > $closeTime) {
                                                    $lyDo = "Đã quá hạn đăng ký";
                                                }
                                                
                                                if (($khongTheDangKy) || ($currentTime < $openTime) || ($currentTime > $closeTime)): ?>
                                                    <button class="btn btn-danger btn-sm" title="<?= $lyDo ?>">
                                                        <i class="bi bi-lock-fill me-1"></i>Đăng ký ngay
                                                    </button>
                                                <?php else: ?>
                                                    <a href="dang_ky_de_tai.php?id=<?= $dt['id'] ?>" 
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Bạn có chắc muốn đăng ký đề tài này?')">
                                                        <i class="bi bi-bookmark-plus-fill me-1"></i>Đăng ký ngay
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto search với debounce
let searchTimeout;
const searchInput = document.querySelector('input[name="search"]');
const filterForm = document.getElementById('filterForm');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            filterForm.submit();
        }, 500); // Đợi 500ms sau khi người dùng ngừng gõ
    });
}

// Xóa các tham số rỗng trước khi submit form
filterForm.addEventListener('submit', function(e) {
    const inputs = this.querySelectorAll('input[name], select[name]');
    inputs.forEach(input => {
        if (input.value === '' || input.value === null) {
            input.removeAttribute('name');
        }
    });
});
</script>
</body>
</html>
