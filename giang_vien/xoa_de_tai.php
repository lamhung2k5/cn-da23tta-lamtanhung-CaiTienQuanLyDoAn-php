<?php
/**
 * XÓA ĐỀ TÀI
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
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
    setFlashMessage('error', 'Bạn không có quyền xóa đề tài này');
    redirect('giang_vien/danh_sach_de_tai.php?loai=' . ($deTai['he_dao_tao'] ?? 'co_so_nganh'));
}

// Chỉ cho phép xóa đề tài nháp hoặc bị từ chối
if (!in_array($deTai['trang_thai'], ['nhap', 'tu_choi'])) {
    setFlashMessage('error', 'Không thể xóa đề tài đã gửi duyệt hoặc đã được duyệt');
    redirect('giang_vien/danh_sach_de_tai.php?loai=' . $deTai['he_dao_tao']);
}

// Kiểm tra đã có sinh viên đăng ký chưa
if ($deTai['so_luong_da_dang_ky'] > 0) {
    setFlashMessage('error', 'Không thể xóa đề tài đã có sinh viên đăng ký');
    redirect('giang_vien/danh_sach_de_tai.php?loai=' . $deTai['he_dao_tao']);
}

// Lưu loại đề tài trước khi xóa
$loaiDeTai = $deTai['he_dao_tao'];

// Xóa đề tài
try {
    $deTaiModel->delete($deTaiId);
    setFlashMessage('success', 'Xóa đề tài thành công');
} catch (Exception $e) {
    setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
}

redirect('giang_vien/danh_sach_de_tai.php?loai=' . $loaiDeTai);
