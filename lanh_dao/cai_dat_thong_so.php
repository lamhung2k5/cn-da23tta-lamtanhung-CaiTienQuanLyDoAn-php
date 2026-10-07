<?php
/**
 * CÀI ĐẶT THÔNG SỐ HỆ THỐNG
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$pageTitle = 'Cài đặt thông số - Lãnh đạo';

$caiDatModel = new CaiDatModel();
$settings = $caiDatModel->getAllSettings();

// Lấy danh sách giảng viên
$giangVienModel = new GiangVienModel();
$danhSachGiangVien = $giangVienModel->getAllWithStats();

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
    /* Input number styling */
    .input-number-modern {
        width: 80px;
        padding: 8px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .input-number-modern:focus {
        border-color: #0d6efd;
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    .input-number-modern:hover {
        border-color: #cbd5e0;
    }

    /* Saving states */
    .input-number-modern.saving {
        border-color: #fbbf24;
        background: #fef3c7;
        pointer-events: none;
    }

    .input-number-modern.saved {
        border-color: #10b981;
        background: #d1fae5;
    }

    .input-number-modern.error {
        border-color: #ef4444;
        background: #fee2e2;
    }

    /* Form control styling */
    .form-control-modern {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        outline: none;
    }

    /* Preview box */
    .preview-box {
        background: #e7f3ff;
        border-left: 4px solid #0d6efd;
        padding: 16px 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .preview-box strong {
        color: #0369a1;
        font-weight: 700;
    }

    .preview-box .preview-text {
        color: #075985;
        margin-top: 4px;
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
                <a class="nav-link" href="duyet_de_tai.php">
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
                <a class="nav-link active" href="cai_dat_thong_so.php">
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
                                Cài đặt <strong>thông số hệ thống</strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Quản lý giới hạn sinh viên và kế hoạch số cho hệ thống.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Card chứa Tabs và Content -->
            <div class="card shadow-sm border-0">
                <!-- Tabs Navigation -->
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs mb-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-gioi-han" data-bs-toggle="tab"
                                data-bs-target="#content-gioi-han" type="button" role="tab">
                                Giới hạn sinh viên
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-ke-hoach" data-bs-toggle="tab"
                                data-bs-target="#content-ke-hoach" type="button" role="tab">
                                Kế hoạch số
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Tabs Content -->
                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- Tab 1: Giới hạn đề tài -->
                        <div class="tab-pane fade show active" id="content-gioi-han" role="tabpanel">
                            <div class="p-4">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 60px;">STT</th>
                                                <th>HỌ TÊN GIẢNG VIÊN</th>
                                                <th class="text-center" style="width: 180px;">SỐ SINH VIÊN CSN</th>
                                                <th class="text-center" style="width: 180px;">SỐ SINH VIÊN CN</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $db = Database::getInstance()->getConnection();
                                            foreach ($danhSachGiangVien as $index => $gv):
                                                // Lấy giới hạn sinh viên từ bảng giang_vien
                                                $stmt = $db->prepare("SELECT gioi_han_sv_csn, gioi_han_sv_cn FROM giang_vien WHERE id = :id");
                                                $stmt->execute(['id' => $gv['id']]);
                                                $gioiHan = $stmt->fetch();

                                                $gioiHanCSN = $gioiHan['gioi_han_sv_csn'] ?? 10;
                                                $gioiHanCN = $gioiHan['gioi_han_sv_cn'] ?? 10;

                                                // Đếm số sinh viên đã đăng ký (bao gồm cả chờ duyệt)
                                                $stmtCountCSN = $db->prepare("SELECT COUNT(DISTINCT dk.sinh_vien_id) as total
                                                    FROM dang_ky_de_tai dk
                                                    JOIN de_tai dt ON dk.de_tai_id = dt.id
                                                    WHERE dt.giang_vien_id = :gv_id 
                                                    AND dt.he_dao_tao = 'co_so_nganh'
                                                    AND dk.trang_thai IN ('cho_duyet', 'da_duyet')");
                                                $stmtCountCSN->execute(['gv_id' => $gv['id']]);
                                                $countCSN = $stmtCountCSN->fetch();
                                                $daDangKyCSN = $countCSN['total'] ?? 0;

                                                $stmtCountCN = $db->prepare("SELECT COUNT(DISTINCT dk.sinh_vien_id) as total
                                                    FROM dang_ky_de_tai dk
                                                    JOIN de_tai dt ON dk.de_tai_id = dt.id
                                                    WHERE dt.giang_vien_id = :gv_id 
                                                    AND dt.he_dao_tao = 'chuyen_nganh'
                                                    AND dk.trang_thai IN ('cho_duyet', 'da_duyet')");
                                                $stmtCountCN->execute(['gv_id' => $gv['id']]);
                                                $countCN = $stmtCountCN->fetch();
                                                $daDangKyCN = $countCN['total'] ?? 0;
                                                ?>
                                                <tr>
                                                    <td class="text-center fw-bold text-muted"><?= $index + 1 ?></td>
                                                    <td>
                                                        <div class="fw-bold"><?= htmlspecialchars($gv['ho_ten'] ?? 'N/A') ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            Đã đăng ký (chờ duyệt + đã duyệt): CSN là <?= $daDangKyCSN ?> SV
                                                            | CN là <?= $daDangKyCN ?> SV
                                                        </small>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" class="input-number-modern auto-save-input"
                                                            data-giang-vien-id="<?= $gv['id'] ?>" data-loai="csn"
                                                            value="<?= $gioiHanCSN ?>" min="<?= $daDangKyCSN ?>" max="100"
                                                            title="Tối thiểu: <?= $daDangKyCSN ?> (đã đăng ký bao gồm chờ duyệt)"
                                                            required>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" class="input-number-modern auto-save-input"
                                                            data-giang-vien-id="<?= $gv['id'] ?>" data-loai="cn"
                                                            value="<?= $gioiHanCN ?>" min="<?= $daDangKyCN ?>" max="100"
                                                            title="Tối thiểu: <?= $daDangKyCN ?> (đã đăng ký bao gồm chờ duyệt)"
                                                            required>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert alert-info mt-4 mb-0">
                                    <strong>Một số lưu ý:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Khi thay đổi số lượng sinh viên, hệ thống sẽ tự động lưu.</li>
                                        <li>Số lượng tối thiểu được tính dựa trên tổng sinh viên đã đăng ký (bao gồm cả
                                            trạng thái "chờ duyệt" và "đã duyệt").</li>
                                        <li>Không thể giảm số lượng xuống thấp hơn số sinh viên đã đăng ký để tránh xung
                                            đột dữ liệu.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Kế hoạch số -->
                        <div class="tab-pane fade" id="content-ke-hoach" role="tabpanel">
                            <div class="p-4">
                                <p class="text-muted mb-4">
                                    Cài đặt kế hoạch số và ngày tháng hiển thị trong danh sách khi được xuất ra.
                                </p>

                                <form method="POST" action="xu_ly_cai_dat.php">
                                    <input type="hidden" name="action" value="update_ke_hoach">

                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold mb-2 d-block">
                                                Số kế hoạch đồ án cơ sở ngành
                                            </label>
                                            <input type="text" name="so_ke_hoach" class="form-control-modern"
                                                style="width: 600px;" placeholder="VD: 012"
                                                value="<?= htmlspecialchars($settings['so_ke_hoach']['value'] ?? '') ?>"
                                                required>
                                        </div>

                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold mb-2 d-block">
                                                Ngày, tháng, năm
                                            </label>
                                            <input type="date" name="ngay_ke_hoach" class="form-control-modern"
                                                style="width: 600px;"
                                                value="<?= htmlspecialchars($settings['ngay_ke_hoach']['value'] ?? '') ?>"
                                                required>
                                        </div>

                                        <div class="col-md-2 d-flex justify-content-end align-items-end">
                                            <button type="submit" class="btn btn-primary"
                                                style="width: 140px; height: 46px;">
                                                Lưu
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <?php if (!empty($settings['so_ke_hoach']['value']) && !empty($settings['ngay_ke_hoach']['value'])): ?>
                                    <div class="preview-box" id="preview-box">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-eye me-3 fs-4"></i>
                                            <div>
                                                <strong>Xem trước khi xuất danh sách</strong>
                                                <div class="preview-text mt-2" id="preview-text">
                                                    Đính kèm Kế hoạch số <strong
                                                        id="preview-so"><?= htmlspecialchars($settings['so_ke_hoach']['value']) ?></strong>/KH-KT&CN
                                                    ngày <strong
                                                        id="preview-ngay"><?= date('d/m/Y', strtotime($settings['ngay_ke_hoach']['value'])) ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <hr class="my-5">

                                <!-- Form kế hoạch đồ án chuyên ngành -->
                                <form method="POST" action="xu_ly_cai_dat.php" class="mt-4">
                                    <input type="hidden" name="action" value="update_ke_hoach_cn">

                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold mb-2 d-block">
                                                Số kế hoạch đồ án chuyên ngành
                                            </label>
                                            <input type="text" name="so_ke_hoach_cn" class="form-control-modern"
                                                style="width: 600px;" placeholder="VD: 012"
                                                value="<?= htmlspecialchars($settings['so_ke_hoach_cn']['value'] ?? '') ?>"
                                                required>
                                        </div>

                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold mb-2 d-block">
                                                Ngày, tháng, năm
                                            </label>
                                            <input type="date" name="ngay_ke_hoach_cn" class="form-control-modern"
                                                style="width: 600px;"
                                                value="<?= htmlspecialchars($settings['ngay_ke_hoach_cn']['value'] ?? '') ?>"
                                                required>
                                        </div>

                                        <div class="col-md-2 d-flex justify-content-end align-items-end">
                                            <button type="submit" class="btn btn-primary"
                                                style="width: 140px; height: 46px;">
                                                Lưu
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <?php if (!empty($settings['so_ke_hoach_cn']['value']) && !empty($settings['ngay_ke_hoach_cn']['value'])): ?>
                                    <div class="preview-box" id="preview-box-cn">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-eye me-3 fs-4"></i>
                                            <div>
                                                <strong>Xem trước khi xuất danh sách</strong>
                                                <div class="preview-text mt-2" id="preview-text-cn">
                                                    Đính kèm Kế hoạch số <strong
                                                        id="preview-so-cn"><?= htmlspecialchars($settings['so_ke_hoach_cn']['value']) ?></strong>/KH-KT&CN
                                                    ngày <strong
                                                        id="preview-ngay-cn"><?= date('d/m/Y', strtotime($settings['ngay_ke_hoach_cn']['value'])) ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Xử lý active tab khi load trang dựa vào hash
    document.addEventListener('DOMContentLoaded', function () {
        // Kiểm tra hash trong URL
        const hash = window.location.hash;

        if (hash === '#tab-gioi-han') {
            // Chuyển sang tab giới hạn
            const tabGioiHan = document.getElementById('tab-gioi-han');
            const contentGioiHan = document.getElementById('content-gioi-han');
            const tabKeHoach = document.getElementById('tab-ke-hoach');
            const contentKeHoach = document.getElementById('content-ke-hoach');

            if (tabGioiHan && contentGioiHan) {
                // Deactivate tab kế hoạch
                tabKeHoach.classList.remove('active');
                contentKeHoach.classList.remove('show', 'active');

                // Activate tab giới hạn
                tabGioiHan.classList.add('active');
                contentGioiHan.classList.add('show', 'active');

                // Scroll về đầu trang
                setTimeout(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }, 100);
            }
        } else if (hash === '#tab-ke-hoach') {
            // Chuyển sang tab kế hoạch (mặc định)
            const tabKeHoach = document.getElementById('tab-ke-hoach');
            const contentKeHoach = document.getElementById('content-ke-hoach');
            const tabGioiHan = document.getElementById('tab-gioi-han');
            const contentGioiHan = document.getElementById('content-gioi-han');

            if (tabKeHoach && contentKeHoach) {
                // Deactivate tab giới hạn
                tabGioiHan.classList.remove('active');
                contentGioiHan.classList.remove('show', 'active');

                // Activate tab kế hoạch
                tabKeHoach.classList.add('active');
                contentKeHoach.classList.add('show', 'active');

                // Scroll về đầu trang
                setTimeout(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }, 100);
            }
        }

        // Lưu tab hiện tại vào hash khi click và ngăn scroll
        const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                // Lưu vị trí scroll hiện tại
                const currentScrollPos = window.pageYOffset || document.documentElement.scrollTop;

                // Sau khi tab được hiển thị
                setTimeout(() => {
                    // Giữ nguyên vị trí scroll (không scroll xuống)
                    window.scrollTo({ top: currentScrollPos, behavior: 'instant' });
                }, 10);
            });

            tab.addEventListener('shown.bs.tab', function (e) {
                const targetId = e.target.getAttribute('data-bs-target');
                if (targetId === '#content-ke-hoach') {
                    window.location.hash = 'tab-ke-hoach';
                } else if (targetId === '#content-gioi-han') {
                    window.location.hash = 'tab-gioi-han';
                }

                // Scroll về đầu trang sau khi chuyển tab
                setTimeout(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }, 50);
            });
        });

        // Real-time preview và auto-save cho form kế hoạch
        const inputSoKeHoach = document.querySelector('input[name="so_ke_hoach"]');
        const inputNgayKeHoach = document.querySelector('input[name="ngay_ke_hoach"]');
        const previewBox = document.getElementById('preview-box');
        const previewSo = document.getElementById('preview-so');
        const previewNgay = document.getElementById('preview-ngay');
        let saveKeHoachTimeout;

        if (inputSoKeHoach && inputNgayKeHoach) {
            // Hàm cập nhật preview
            function updatePreview() {
                const soKeHoach = inputSoKeHoach.value.trim();
                const ngayKeHoach = inputNgayKeHoach.value;

                // Nếu cả 2 đều có giá trị
                if (soKeHoach && ngayKeHoach) {
                    // Hiển thị preview box
                    if (previewBox) {
                        previewBox.style.display = 'block';

                        // Cập nhật nội dung
                        if (previewSo) previewSo.textContent = soKeHoach;
                        if (previewNgay) {
                            // Format ngày
                            const date = new Date(ngayKeHoach);
                            const day = String(date.getDate()).padStart(2, '0');
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const year = date.getFullYear();
                            previewNgay.textContent = `${day}/${month}/${year}`;
                        }
                    }
                } else {
                    // Ẩn preview box nếu thiếu dữ liệu
                    if (previewBox) {
                        previewBox.style.display = 'none';
                    }
                }
            }

            // Hàm auto-save kế hoạch
            function autoSaveKeHoach() {
                clearTimeout(saveKeHoachTimeout);

                saveKeHoachTimeout = setTimeout(() => {
                    const soKeHoach = inputSoKeHoach.value.trim();
                    const ngayKeHoach = inputNgayKeHoach.value;

                    // Chỉ lưu khi cả 2 đều có giá trị HOẶC cả 2 đều trống (để xóa)
                    if ((soKeHoach && ngayKeHoach) || (!soKeHoach && !ngayKeHoach)) {
                        // Gửi AJAX request
                        const formData = new FormData();
                        formData.append('action', 'update_ke_hoach');
                        formData.append('so_ke_hoach', soKeHoach);
                        formData.append('ngay_ke_hoach', ngayKeHoach);

                        fetch('xu_ly_cai_dat.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.text())
                            .then(data => {
                                console.log('Đã lưu kế hoạch tự động');
                            })
                            .catch(error => {
                                console.error('Lỗi khi lưu:', error);
                            });
                    }
                }, 1000); // Đợi 1 giây sau khi dừng nhập
            }

            // Kiểm tra và hiển thị preview khi load trang nếu đã có dữ liệu
            if (previewBox) {
                const soKeHoach = inputSoKeHoach.value.trim();
                const ngayKeHoach = inputNgayKeHoach.value;

                // Nếu có dữ liệu thì hiển thị, không thì ẩn
                if (soKeHoach && ngayKeHoach) {
                    previewBox.style.display = 'block';
                } else {
                    previewBox.style.display = 'none';
                }
            }

            // Lắng nghe sự kiện input cho preview và auto-save
            inputSoKeHoach.addEventListener('input', function () {
                updatePreview();
                autoSaveKeHoach();
            });

            inputNgayKeHoach.addEventListener('input', function () {
                updatePreview();
                autoSaveKeHoach();
            });

            inputNgayKeHoach.addEventListener('change', function () {
                updatePreview();
                autoSaveKeHoach();
            });
        }

        // AJAX Auto-save cho input số đề tài
        const autoSaveInputs = document.querySelectorAll('.auto-save-input');
        let saveTimeout;

        autoSaveInputs.forEach(input => {
            input.addEventListener('change', function () {
                clearTimeout(saveTimeout);

                const giangVienId = this.getAttribute('data-giang-vien-id');
                const loai = this.getAttribute('data-loai');
                const soDeTai = this.value;
                const minValue = parseInt(this.getAttribute('min'));

                // Validate
                if (!soDeTai || soDeTai < minValue || soDeTai > 100) {
                    this.classList.add('error');
                    if (soDeTai < minValue) {
                        alert(`Không thể giảm xuống dưới ${minValue} sinh viên vì đã có ${minValue} sinh viên đăng ký (bao gồm cả chờ duyệt)`);
                        this.value = minValue; // Reset về giá trị tối thiểu
                    }
                    setTimeout(() => this.classList.remove('error'), 2000);
                    return;
                }

                // Hiển thị trạng thái đang lưu
                this.classList.add('saving');
                this.classList.remove('saved', 'error');

                // Gửi AJAX request
                const formData = new FormData();
                formData.append('action', 'update_single_limit');
                formData.append('giang_vien_id', giangVienId);
                formData.append('loai', loai);
                formData.append('so_de_tai', soDeTai);

                fetch('xu_ly_cai_dat.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hiển thị trạng thái đã lưu
                            this.classList.remove('saving');
                            this.classList.add('saved');

                            // Xóa trạng thái sau 1.5 giây
                            setTimeout(() => {
                                this.classList.remove('saved');
                            }, 1500);
                        } else {
                            // Hiển thị lỗi
                            this.classList.remove('saving');
                            this.classList.add('error');

                            // Hiển thị thông báo lỗi nếu có
                            if (data.message) {
                                alert(data.message);
                            }

                            setTimeout(() => {
                                this.classList.remove('error');
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.classList.remove('saving');
                        this.classList.add('error');

                        setTimeout(() => {
                            this.classList.remove('error');
                        }, 2000);
                    });
            });

            // Debounce cho input (khi gõ liên tục)
            input.addEventListener('input', function () {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    this.dispatchEvent(new Event('change'));
                }, 800);
            });
        });
    });
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
    document.getElementById('vaiTroModal').addEventListener('change', function () {
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

</body>

</html>