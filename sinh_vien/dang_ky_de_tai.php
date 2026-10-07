<?php
/**
 * ĐĂNG KÝ ĐỀ TÀI
 */

require_once '../bootstrap.php';
requireRole(ROLE_SINH_VIEN);

$user = getCurrentUser();
$sinhVienModel = new SinhVienModel();
$deTaiModel = new DeTaiModel();
$dangKyModel = new DangKyDeTaiModel();
$thongBaoModel = new ThongBaoModel();

$sinhVien = $sinhVienModel->getByNguoiDungId($user['id']);

// Lấy ID đề tài
$deTaiId = (int)($_GET['id'] ?? 0);

if (!$deTaiId) {
    setFlashMessage('error', 'Đề tài không tồn tại');
    redirect('sinh_vien/danh_sach_de_tai.php');
}

$deTai = $deTaiModel->findById($deTaiId);

if (!$deTai || $deTai['trang_thai'] !== 'da_duyet') {
    setFlashMessage('error', 'Đề tài không tồn tại hoặc chưa được duyệt');
    redirect('sinh_vien/danh_sach_de_tai.php');
}

// Kiểm tra sinh viên đã đủ số lượng đề tài đã duyệt chưa (1 CSN + 1 CN)
if ($sinhVienModel->daDuSoLuongDeTai($sinhVien['id'])) {
    setFlashMessage('error', 'Bạn đã đủ số lượng đề tài (1 Cơ sở ngành + 1 Chuyên ngành đã được duyệt). Không thể đăng ký thêm.');
    redirect('sinh_vien/de_tai_cua_toi.php');
}

// Kiểm tra còn chỗ không - Kiểm tra giới hạn sinh viên của giảng viên
$db = Database::getInstance()->getConnection();
$giangVienModel = new GiangVienModel();
$giangVien = $giangVienModel->findById($deTai['giang_vien_id']);

// Lấy giới hạn sinh viên theo loại đề tài
$columnName = $deTai['he_dao_tao'] === 'co_so_nganh' ? 'gioi_han_sv_csn' : 'gioi_han_sv_cn';
$gioiHanSV = $giangVien[$columnName] ?? 10;

// Đếm số sinh viên đã đăng ký với giảng viên này (theo loại đề tài)
$stmt = $db->prepare("SELECT COUNT(DISTINCT dk.sinh_vien_id) as total
    FROM dang_ky_de_tai dk
    JOIN de_tai dt ON dk.de_tai_id = dt.id
    WHERE dt.giang_vien_id = :giang_vien_id 
    AND dt.he_dao_tao = :he_dao_tao
    AND dk.trang_thai = 'da_duyet'");
$stmt->execute([
    'giang_vien_id' => $deTai['giang_vien_id'],
    'he_dao_tao' => $deTai['he_dao_tao']
]);
$result = $stmt->fetch();
$soSinhVienDaDangKy = $result['total'] ?? 0;

if ($soSinhVienDaDangKy >= $gioiHanSV) {
    $loaiDeTaiText = $deTai['he_dao_tao'] === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành';
    setFlashMessage('error', "Giảng viên đã đủ số lượng sinh viên hướng dẫn ({$loaiDeTaiText}). Vui lòng đăng ký đề tài của giảng viên khác.");
    redirect('sinh_vien/danh_sach_de_tai.php');
}

// Đăng ký
$result = $dangKyModel->dangKyDeTai($sinhVien['id'], $deTaiId);

if ($result['success']) {
    // Gửi thông báo cho giảng viên
    $thongBaoModel->thongBaoSinhVienDangKy(
        $deTai['giang_vien_id'], 
        $user['ho_ten'], 
        $deTai['tieu_de']
    );
    
    setFlashMessage('success', $result['message']);
    redirect('sinh_vien/de_tai_cua_toi.php');
} else {
    setFlashMessage('error', $result['message']);
    redirect('sinh_vien/danh_sach_de_tai.php');
}
