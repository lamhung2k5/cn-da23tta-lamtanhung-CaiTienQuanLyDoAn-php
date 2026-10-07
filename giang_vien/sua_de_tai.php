<?php
/**
 * SỬA ĐỀ TÀI
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$pageTitle = 'Sửa đề tài - Giảng viên';

$giangVienModel = new GiangVienModel();
$deTaiModel = new DeTaiModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

// Lấy ID đề tài
$deTaiId = (int)($_GET['id'] ?? 0);

if (!$deTaiId) {
    setFlashMessage('error', 'Đề tài không tồn tại');
    redirect('giang_vien/danh_sach_de_tai.php');
}

$deTai = $deTaiModel->findById($deTaiId);

if (!$deTai || $deTai['giang_vien_id'] != $giangVien['id']) {
    setFlashMessage('error', 'Bạn không có quyền sửa đề tài này');
    redirect('giang_vien/danh_sach_de_tai.php?loai=' . ($deTai['he_dao_tao'] ?? 'co_so_nganh'));
}

// Chỉ cho phép sửa đề tài nháp hoặc bị từ chối
if (!in_array($deTai['trang_thai'], ['nhap', 'tu_choi'])) {
    setFlashMessage('error', 'Không thể sửa đề tài đã gửi duyệt hoặc đã được duyệt');
    redirect('giang_vien/danh_sach_de_tai.php?loai=' . $deTai['he_dao_tao']);
}

$error = '';
$success = '';

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tieuDe = sanitize($_POST['tieu_de'] ?? '');
    $moTa = sanitize($_POST['mo_ta'] ?? '');
    $heDaoTao = sanitize($_POST['he_dao_tao'] ?? '');
    $soLuongSv = $deTai['so_luong_sv']; // Giữ nguyên số lượng sinh viên
    $chuyenNganh = sanitize($_POST['chuyen_nganh'] ?? '');
    $congNghe = sanitize($_POST['cong_nghe'] ?? '');
    $yeuCauSinhVien = sanitize($_POST['yeu_cau_sinh_vien'] ?? '');
    $ghiChu = sanitize($_POST['ghi_chu'] ?? '');
    $action = sanitize($_POST['action'] ?? 'save_submit');
    $trangThai = ($action === 'save_submit') ? STATUS_CHO_DUYET : STATUS_NHAP;
    
    // Validate
    if (empty($tieuDe) || empty($moTa) || empty($heDaoTao)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
    } else {
        // Không còn kiểm tra giới hạn số đề tài nữa - giảng viên có thể tạo không giới hạn
        
        try {
            $deTaiModel->update($deTaiId, [
                'tieu_de' => $tieuDe,
                'ten_de_tai' => $tieuDe,
                'mo_ta' => $moTa,
                'he_dao_tao' => $heDaoTao,
                'so_luong_sv' => $soLuongSv,
                'trang_thai' => $trangThai,
                'chuyen_nganh' => $chuyenNganh,
                'cong_nghe' => $congNghe,
                'yeu_cau_sinh_vien' => $yeuCauSinhVien,
                'ghi_chu' => $ghiChu,
                'ly_do_tu_choi' => null // Xóa lý do từ chối cũ
            ]);
            
            $success = 'Cập nhật đề tài thành công';
            
            if ($trangThai === STATUS_CHO_DUYET) {
                // Gửi thông báo cho tất cả lãnh đạo
                $thongBaoModel = new ThongBaoModel();
                $lanhDaoModel = new LanhDaoModel();
                
                $danhSachLanhDao = $lanhDaoModel->findAll();
                
                foreach ($danhSachLanhDao as $lanhDao) {
                    $thongBaoModel->taoThongBao(
                        $lanhDao['nguoi_dung_id'],
                        'Đề tài mới chờ duyệt',
                        "Giảng viên {$user['ho_ten']} đã gửi đề tài \"{$tieuDe}\" chờ duyệt.",
                        'warning',
                        'lanh_dao/duyet_de_tai.php'
                    );
                }
                
                $success = 'Cập nhật và gửi đề tài chờ duyệt thành công';
            } else {
                $success = 'Lưu nháp đề tài thành công';
            }
            
            // Hiển thị cảnh báo nếu có
            if (isset($warning)) {
                setFlashMessage('warning', $warning);
            } else {
                setFlashMessage('success', $success);
            }
            redirect('giang_vien/danh_sach_de_tai.php?loai=' . $heDaoTao);
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
                                Sửa đề tài <strong><?= getHeDaoTaoLabel($deTai['he_dao_tao']) ?></strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Chỉnh sửa thông tin đề tài và gửi lại để chờ duyệt.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="danh_sach_de_tai.php?loai=<?= htmlspecialchars($deTai['he_dao_tao']) ?>" class="btn text-white" style="background-color: #0d6efd;">
                                <i class="bi bi-chevron-double-left"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($deTai['trang_thai'] === 'tu_choi' && !empty($deTai['ly_do_tu_choi'])): ?>
                <div class="alert alert-warning">
                    <strong>Lý do từ chối:</strong>
                    <?= nl2br(htmlspecialchars($deTai['ly_do_tu_choi'])) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="he_dao_tao" value="<?= htmlspecialchars($deTai['he_dao_tao']) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề đề tài <span class="text-danger">*</span></label>
                            <input type="text" name="tieu_de" class="form-control" 
                                   placeholder="Nhập tiêu đề đề tài" required
                                   value="<?= htmlspecialchars($deTai['tieu_de']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả đề tài <span class="text-danger">*</span></label>
                            <textarea name="mo_ta" class="form-control" rows="5" 
                                      placeholder="Mô tả chi tiết về đề tài, mục tiêu, phạm vi..." 
                                      required><?= htmlspecialchars($deTai['mo_ta']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ngành</label>
                            <input type="text" name="chuyen_nganh" class="form-control" 
                                   placeholder="VD: Công nghệ thông tin"
                                   value="<?= htmlspecialchars($deTai['chuyen_nganh']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Công nghệ sử dụng</label>
                            <input type="text" name="cong_nghe" class="form-control" 
                                   placeholder="VD: PHP, MySQL, Bootstrap, React..."
                                   value="<?= htmlspecialchars($deTai['cong_nghe']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Yêu cầu sinh viên</label>
                            <textarea name="yeu_cau_sinh_vien" class="form-control" rows="3" 
                                      placeholder="Kiến thức, kỹ năng cần có..."><?= htmlspecialchars($deTai['yeu_cau_sinh_vien']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="2" 
                                      placeholder="Ghi chú thêm (nếu có)..."><?= htmlspecialchars($deTai['ghi_chu']) ?></textarea>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" name="action" value="save_draft" class="btn btn-secondary">
                                <i class="bi bi-save"></i> Lưu nháp
                            </button>
                            <button type="submit" name="action" value="save_submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Lưu và gửi duyệt
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
