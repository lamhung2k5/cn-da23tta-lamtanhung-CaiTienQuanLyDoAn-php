<?php
/**
 * TẠO ĐỀ TÀI MỚI
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$pageTitle = 'Tạo đề tài mới - Giảng viên';

$giangVienModel = new GiangVienModel();
$deTaiModel = new DeTaiModel();
$caiDatModel = new CaiDatModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

if (!$giangVien) {
    setFlashMessage('error', 'Không tìm thấy thông tin giảng viên');
    redirect('../index.php');
}

// Kiểm tra loại đề tài
$loaiDeTai = sanitize($_GET['loai'] ?? '');
if (empty($loaiDeTai) || !in_array($loaiDeTai, ['co_so_nganh', 'chuyen_nganh'])) {
    redirect('chon_loai_de_tai.php');
}

// Không còn kiểm tra giới hạn số đề tài nữa - giảng viên có thể tạo không giới hạn

$error = '';
$success = '';

// Xử lý tạo đề tài
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Không còn kiểm tra giới hạn số đề tài nữa
    $tieuDe = sanitize($_POST['tieu_de'] ?? '');
    $moTa = sanitize($_POST['mo_ta'] ?? '');
    $heDaoTao = $loaiDeTai; // Lấy từ URL thay vì POST
    $soLuongSv = 1; // Mặc định 1 sinh viên
    // Xử lý checkbox ngành (có thể chọn nhiều)
    $chuyenNganhArray = $_POST['chuyen_nganh'] ?? [];
    $chuyenNganh = is_array($chuyenNganhArray) ? implode(', ', array_map('sanitize', $chuyenNganhArray)) : sanitize($chuyenNganhArray);
    $congNghe = sanitize($_POST['cong_nghe'] ?? '');
    $yeuCauSinhVien = sanitize($_POST['yeu_cau_sinh_vien'] ?? '');
    $ghiChu = sanitize($_POST['ghi_chu'] ?? '');
    $trangThai = STATUS_NHAP;
    $action = sanitize($_POST['action'] ?? 'save'); // save hoặc save_continue

    // Validate
    if (empty($tieuDe) || empty($moTa)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
    } else {
        try {
            $deTaiId = $deTaiModel->createDeTai([
                'tieu_de' => $tieuDe,
                'ten_de_tai' => $tieuDe,
                'mo_ta' => $moTa,
                'giang_vien_id' => $giangVien['id'],
                'he_dao_tao' => $heDaoTao,
                'so_luong_sv' => $soLuongSv,
                'trang_thai' => $trangThai,
                'chuyen_nganh' => $chuyenNganh,
                'cong_nghe' => $congNghe,
                'yeu_cau_sinh_vien' => $yeuCauSinhVien,
                'ghi_chu' => $ghiChu,
                'nam_hoc' => date('Y') . '-' . (date('Y') + 1),
                'hoc_ky' => 'HK' . (date('n') <= 6 ? '2' : '1')
            ]);

            $success = 'Đã tạo đề tài thành công';
            setFlashMessage('success', $success);

            // Kiểm tra action để quyết định redirect
            if ($action === 'save_continue') {
                // Lưu và tiếp tục tạo đề tài mới cùng loại
                redirect('giang_vien/tao_de_tai.php?loai=' . $loaiDeTai);
            } else {
                // Lưu và quay về danh sách với tab tương ứng
                redirect('giang_vien/danh_sach_de_tai.php?loai=' . $loaiDeTai);
            }
        } catch (Exception $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
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
                <a class="nav-link active" href="chon_loai_de_tai.php">
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
            <!-- Welcome Card -->
            <div class="card mb-4 fade-in-up border-dark" style="border-width: 2px !important;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2 text-dark">
                                Tạo đề tài <strong><?= getHeDaoTaoLabel($loaiDeTai) ?></strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Tạo đề tài mới cho sinh viên đăng ký. Không giới hạn số lượng.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="chon_loai_de_tai.php" class="btn text-white" style="background-color: #0d6efd;">
                                Quay lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="he_dao_tao" value="<?= $loaiDeTai ?>">

                        <div class="mb-3">
                            <label class="form-label">Tiêu đề đề tài <span class="text-danger">*</span></label>
                            <input type="text" name="tieu_de" class="form-control" placeholder="Nhập tiêu đề đề tài"
                                required value="<?= $_POST['tieu_de'] ?? '' ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả đề tài <span class="text-danger">*</span></label>
                            <textarea name="mo_ta" class="form-control" rows="5"
                                placeholder="Mô tả chi tiết về đề tài, mục tiêu, phạm vi..."
                                required><?= $_POST['mo_ta'] ?? '' ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Ngành</label>
                                <div class="border rounded p-2 d-flex gap-3" style="border-color: #dee2e6 !important;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chuyen_nganh[]"
                                            value="Công nghệ thông tin" id="nganh_cntt" <?php
                                            $selected = $_POST['chuyen_nganh'] ?? [];
                                            echo (is_array($selected) && in_array('Công nghệ thông tin', $selected)) ? 'checked' : '';
                                            ?>>
                                        <label class="form-check-label" for="nganh_cntt">
                                            Công nghệ thông tin
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chuyen_nganh[]"
                                            value="Trí tuệ nhân tạo" id="nganh_ai" <?php
                                            $selected = $_POST['chuyen_nganh'] ?? [];
                                            echo (is_array($selected) && in_array('Trí tuệ nhân tạo', $selected)) ? 'checked' : '';
                                            ?>>
                                        <label class="form-check-label" for="nganh_ai">
                                            Trí tuệ nhân tạo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <label class="form-label">Công nghệ sử dụng</label>
                                <input type="text" name="cong_nghe" class="form-control"
                                    placeholder="VD: PHP, MySQL, Bootstrap, React..."
                                    value="<?= $_POST['cong_nghe'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Yêu cầu sinh viên</label>
                            <textarea name="yeu_cau_sinh_vien" class="form-control" rows="3"
                                placeholder="Kiến thức, kỹ năng cần có..."><?= $_POST['yeu_cau_sinh_vien'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="2"
                                placeholder="Ghi chú thêm (nếu có)..."><?= $_POST['ghi_chu'] ?? '' ?></textarea>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" name="action" value="save_continue" class="btn btn-success">
                                Lưu và tiếp tục
                            </button>
                            <button type="submit" name="action" value="save" class="btn btn-primary">
                                Lưu và quay lại
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>