<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_SINH_VIEN);
requirePost();
$student = (new SinhVienModel())->getByNguoiDungId(getCurrentUser()['id']);
if (!$student) { securityDeny(403, 'Không tìm thấy hồ sơ Sinh viên.'); }
$model = new DeTaiQuanTamModel();
$action = $_POST['action'] ?? '';
$topicId = filter_var($_POST['de_tai_id'] ?? null, FILTER_VALIDATE_INT);
$savedId = filter_var($_POST['quan_tam_id'] ?? null, FILTER_VALIDATE_INT);
try {
    if ($action === 'add' && $topicId > 0) { $message = $model->addForCurrentStudent($student['id'], $topicId); }
    elseif ($action === 'remove' && $savedId > 0) { $message = $model->removeForCurrentStudent($student['id'], $savedId); }
    else { throw new DomainException('Thao tác quan tâm không hợp lệ.'); }
    setFlashMessage('success', $message);
} catch (Throwable $error) {
    setFlashMessage('error', $error instanceof DomainException ? $error->getMessage() : safeError($error));
}
$returnTo = $_POST['return_to'] ?? 'list';
$type = ($_POST['loai'] ?? '') === 'chuyen_nganh' ? 'chuyen_nganh' : 'co_so_nganh';
if ($returnTo === 'followed') {
    $filter = in_array($_POST['filter'] ?? '', ['co_so_nganh','chuyen_nganh'], true) ? '?loai=' . $_POST['filter'] : '';
    redirect('sinh_vien/de_tai_quan_tam.php' . $filter);
}
if ($returnTo === 'detail' && $topicId > 0) { redirect('sinh_vien/chi_tiet_de_tai.php?id=' . $topicId); }
$search = is_string($_POST['search'] ?? null) ? substr($_POST['search'], 0, 255) : '';
redirect('sinh_vien/danh_sach_de_tai.php?loai=' . $type . ($search !== '' ? '&search=' . urlencode($search) : ''));
