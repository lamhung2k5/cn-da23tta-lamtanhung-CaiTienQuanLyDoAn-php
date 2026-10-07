<?php
/**
 * XỬ LÝ XÓA ĐĂNG KÝ SINH VIÊN
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$dangKyModel = new DangKyDeTaiModel();
$deTaiModel = new DeTaiModel();
$giangVienModel = new GiangVienModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

// Lấy ID đăng ký
$dangKyId = (int)($_GET['id'] ?? 0);
$deTaiId = (int)($_GET['de_tai_id'] ?? 0);

if (!$dangKyId || !$deTaiId) {
    setFlashMessage('error', 'Thông tin không hợp lệ');
    redirect('giang_vien/danh_sach_de_tai.php');
}

// Kiểm tra đăng ký có tồn tại không
$dangKy = $dangKyModel->findById($dangKyId);

if (!$dangKy) {
    setFlashMessage('error', 'Đăng ký không tồn tại');
    redirect('giang_vien/chi_tiet_de_tai.php?id=' . $deTaiId);
}

// Kiểm tra đề tài có thuộc về giảng viên không
$deTai = $deTaiModel->findById($deTaiId);

if (!$deTai || $deTai['giang_vien_id'] != $giangVien['id']) {
    setFlashMessage('error', 'Bạn không có quyền thực hiện thao tác này');
    redirect('giang_vien/danh_sach_de_tai.php');
}

// Chỉ cho phép xóa đăng ký có trạng thái "từ chối"
if ($dangKy['trang_thai'] !== STATUS_TU_CHOI) {
    setFlashMessage('error', 'Chỉ có thể xóa đăng ký có trạng thái "Từ chối"');
    redirect('giang_vien/chi_tiet_de_tai.php?id=' . $deTaiId);
}

// Xóa đăng ký
try {
    $dangKyModel->delete($dangKyId);
    setFlashMessage('success', 'Đã xóa đăng ký sinh viên thành công');
} catch (Exception $e) {
    setFlashMessage('error', 'Có lỗi xảy ra khi xóa đăng ký: ' . $e->getMessage());
}

redirect('giang_vien/chi_tiet_de_tai.php?id=' . $deTaiId);
