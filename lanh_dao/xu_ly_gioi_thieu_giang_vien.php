<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_LANH_DAO);
requirePost();
$id = filter_var($_POST['giang_vien_id'] ?? null, FILTER_VALIDATE_INT);
try {
    if (!$id || $id < 1) { throw new DomainException('Hồ sơ không hợp lệ.'); }
    (new GiangVienModel())->updateDirectoryProfile($id, $_POST);
    auditSecurity('faculty.public_profile_updated', (int)$id);
    setFlashMessage('success', 'Đã lưu thông tin giới thiệu giảng viên.');
} catch (Throwable $error) {
    setFlashMessage('error', $error instanceof DomainException ? $error->getMessage() : safeError($error));
}
redirect('lanh_dao/gioi_thieu_giang_vien.php' . ($id > 0 ? '?id=' . (int)$id : ''));
