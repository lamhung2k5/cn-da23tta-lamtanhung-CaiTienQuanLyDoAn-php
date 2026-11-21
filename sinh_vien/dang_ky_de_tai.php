<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_SINH_VIEN);
requirePost();
$user = getCurrentUser();
$student = (new SinhVienModel())->getByNguoiDungId($user['id']);
if (!$student) { securityDeny(403, 'Không tìm thấy hồ sơ Sinh viên.'); }
$topicId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$topic = $topicId > 0 ? (new DeTaiModel())->findById($topicId) : null;
if (!$topic) {
    $result = ['success'=>false,'message'=>'Đề tài không tồn tại.'];
} else {
    // The model checks ownership, registration window and capacity inside its transaction.
    $result = (new DangKyDeTaiModel())->dangKyDeTai($student['id'], $topicId);
}
if ($result['success']) {
    try { (new ThongBaoModel())->thongBaoSinhVienDangKy($topic['giang_vien_id'], $user['ho_ten'], $topic['tieu_de']); }
    catch (Throwable $error) { safeError($error); } // A notification failure must not undo a confirmed registration.
    setFlashMessage('success', $result['message']);
    redirect('sinh_vien/de_tai_cua_toi.php');
}
setFlashMessage('error', $result['message']);
if (($_POST['return_to'] ?? '') === 'followed') {
    $filter = in_array($_POST['filter'] ?? '', ['co_so_nganh','chuyen_nganh'], true) ? '?loai=' . $_POST['filter'] : '';
    redirect('sinh_vien/de_tai_quan_tam.php' . $filter);
}
if (($_POST['return_to'] ?? '') === 'detail' && $topicId > 0) { redirect('sinh_vien/chi_tiet_de_tai.php?id=' . $topicId); }
$type = $topic && $topic['he_dao_tao'] === 'chuyen_nganh' ? 'chuyen_nganh' : 'co_so_nganh';
redirect('sinh_vien/danh_sach_de_tai.php?loai=' . $type);