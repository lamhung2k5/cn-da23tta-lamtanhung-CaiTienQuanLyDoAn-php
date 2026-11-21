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
$search = is_string($_GET['search'] ?? null) ? trim($_GET['search']) : '';
$teacherOptions = [];
$technologyOptions = [];
foreach ($deTaiModel->getDeTaiDaDuyet($filters) as $topic) {
    $teacherOptions[(int)$topic['giang_vien_id']] = $topic['ten_giang_vien'];
    $technologyOptions += DeTaiModel::technologyTokens($topic['cong_nghe'] ?? '');
}
asort($teacherOptions, SORT_NATURAL | SORT_FLAG_CASE);
asort($technologyOptions, SORT_NATURAL | SORT_FLAG_CASE);
$technology = is_string($_GET['cong_nghe'] ?? null) ? DeTaiModel::normalizeTechnology($_GET['cong_nghe']) : '';
if (!array_key_exists($technology, $technologyOptions)) { $technology = ''; }
$teacherId = filter_var($_GET['giang_vien'] ?? null, FILTER_VALIDATE_INT) ?: 0;
if (!isset($teacherOptions[$teacherId])) { $teacherId = 0; }
$followed = is_string($_GET['quan_tam'] ?? null) && in_array($_GET['quan_tam'], ['saved', 'unsaved'], true) ? $_GET['quan_tam'] : '';
$sort = is_string($_GET['sap_xep'] ?? null) && in_array($_GET['sap_xep'], ['newest', 'oldest', 'title'], true) ? $_GET['sap_xep'] : 'newest';
$filters += ['title_teacher_only'=>true, 'giang_vien_id'=>$teacherId, 'followed'=>$followed, 'student_id'=>(int)$sinhVien['id'], 'sort'=>$sort, 'technology'=>$technology];
if ($search !== '') {
    $filters['search'] = $search;
}
$tabQuery = ['search'=>$search, 'giang_vien'=>$teacherId ?: '', 'quan_tam'=>$followed, 'sap_xep'=>$sort, 'cong_nghe'=>$technology];
$tabQuery = array_filter($tabQuery, static function ($value) { return $value !== ''; });
$tabUrl = static function ($type) use ($tabQuery) { return '?' . http_build_query(['loai'=>$type] + $tabQuery); };

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

$quanTamModel = new DeTaiQuanTamModel();
$quanTamSavedMap = $quanTamModel->savedMap($sinhVien['id']);
$registrationContext = $quanTamModel->registrationContext($sinhVien['id']);
$registrationWindow = new DangKyThoiGian();

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
            <?php include __DIR__ . '/includes/quan_tam_sidebar.php'; ?>
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
                    

                </div>
            </div>

            <!-- Tabs chọn loại đề tài -->
            <div class="tabs-container mb-4">
                <ul class="nav nav-tabs nav-justified custom-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $loaiDeTai === 'co_so_nganh' ? 'active' : '' ?>" 
                           href="<?= e($tabUrl('co_so_nganh')) ?>">
                            <i class="bi bi-journal-code"></i> 
                            <span class="tab-text">Cơ sở ngành</span>
                            <?php if ($daDuCSN): ?>
                                <span class="badge bg-danger ms-2">Bạn đã có đề tài</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $loaiDeTai === 'chuyen_nganh' ? 'active' : '' ?>" 
                           href="<?= e($tabUrl('chuyen_nganh')) ?>">
                            <i class="bi bi-mortarboard"></i> 
                            <span class="tab-text">Chuyên ngành</span>
                            <?php if ($daDuCN): ?>
                                <span class="badge bg-danger ms-2">Bạn đã có đề tài</span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card compact-search-card mb-4">
                <div class="card-body">
                    <form method="GET" id="filterForm" class="row g-3 align-items-end">
                        <input type="hidden" name="loai" value="<?= e($loaiDeTai) ?>">
                        <div class="col-12 col-lg-6">
                            <label for="topic-search" class="form-label">Tìm kiếm đề tài</label>
                            <input id="topic-search" type="search" name="search" class="form-control" placeholder="Nhập tên đề tài hoặc tên giảng viên…" value="<?= e($search) ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="topic-teacher" class="form-label">Giảng viên hướng dẫn</label>
                            <select id="topic-teacher" name="giang_vien" class="form-select">
                                <option value="">Tất cả giảng viên</option>
                                <?php foreach ($teacherOptions as $id=>$name): ?>
                                    <option value="<?= (int)$id ?>" <?= $teacherId === $id ? 'selected' : '' ?>><?= e($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="topic-technology" class="form-label">Công nghệ sử dụng</label>
                            <select id="topic-technology" name="cong_nghe" class="form-select" <?= !$technologyOptions ? 'aria-describedby="technology-help"' : '' ?>>
                                <option value="">Tất cả công nghệ</option>
                                <?php foreach ($technologyOptions as $key=>$label): ?>
                                    <option value="<?= e($key) ?>" <?= $technology === (string)$key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$technologyOptions): ?>
                                <div id="technology-help" class="form-text">Chưa có dữ liệu công nghệ trong hệ này.</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="topic-followed" class="form-label">Quan tâm</label>
                            <select id="topic-followed" name="quan_tam" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="saved" <?= $followed === 'saved' ? 'selected' : '' ?>>Đã quan tâm</option>
                                <option value="unsaved" <?= $followed === 'unsaved' ? 'selected' : '' ?>>Chưa quan tâm</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="topic-sort" class="form-label">Sắp xếp</label>
                            <select id="topic-sort" name="sap_xep" class="form-select">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                                <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Tên đề tài A–Z</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Tìm kiếm</button>
                            <a class="btn btn-outline-secondary" href="?loai=<?= e($loaiDeTai) ?>">Xóa bộ lọc</a>
                        </div>
                    </form>
                    <p class="text-muted small mt-3 mb-0" role="status">Tìm thấy <strong><?= count($danhSachDeTai) ?></strong> đề tài <?= e(getHeDaoTaoLabel($loaiDeTai)) ?><?= $search !== '' ? ' cho từ khóa “' . e($search) . '”' : '' ?>.</p>
                </div>
            </div>

            <!-- Danh sách đề tài -->
            <div class="row">
                <?php if (empty($danhSachDeTai)): ?>
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Không tìm thấy đề tài phù hợp. Hãy đổi từ khóa hoặc xóa bộ lọc.</p>
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
                                            Còn <?= e($dt['con_lai']) ?>/<?= e($dt['so_luong_sv']) ?> chỗ
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
                                
                                <!-- Footer: keep the existing registration controls; add the follow action. -->
                                <div class="card-footer bg-light border-0">
                                    <?php
                                    $quanTamTopicId = (int)$dt['id'];
                                    $quanTamSavedId = $quanTamSavedMap[$quanTamTopicId] ?? null;
                                    $quanTamReturn = 'list';
                                    $quanTamType = $loaiDeTai;
                                    $registrationTopicId = $quanTamTopicId;
                                    $registrationReturn = 'list';
                                    $registrationState = DeTaiQuanTamService::registrationState($dt, $registrationContext, $registrationWindow);
                                    ?>
                                    <div class="d-flex flex-wrap justify-content-center gap-2 align-items-center">
                                        <a href="chi_tiet_de_tai.php?id=<?= (int)$dt['id'] ?>" class="btn btn-ocean btn-sm"><i class="bi bi-eye-fill me-1"></i>Xem chi tiết</a>
                                        <?php include __DIR__ . '/includes/quan_tam_button.php'; ?>
                                        <?php include __DIR__ . '/includes/dang_ky_button.php'; ?>
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

</body>
</html>
