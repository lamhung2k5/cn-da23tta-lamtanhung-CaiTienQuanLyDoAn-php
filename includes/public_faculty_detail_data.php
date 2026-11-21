<?php
if (!defined('ROOT_PATH')) { http_response_code(404); exit; }
$model = new GiangVienModel();
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$giangVien = $id && $id > 0 ? $model->getPublicProfile($id) : null;
// Keep old email links working, but never fall back to the hardcoded directory.
if (!isset($_GET['id']) && is_string($_GET['email'] ?? null)) {
    foreach ($model->getDirectory() as $profile) {
        if (strcasecmp($profile['email'], $_GET['email']) === 0) { $giangVien = $profile; break; }
    }
}
if (!$giangVien) {
    http_response_code(404);
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><title>Không tìm thấy giảng viên</title><p>Không tìm thấy giảng viên được công khai.</p><a href="khoa_cntt.php">Quay lại danh sách</a></html>';
    exit;
}
$nguoiDung = ['ho_ten'=>trim(($giangVien['hoc_ham_hoc_vi'] ?? '') . ' ' . $giangVien['ho_ten']), 'email'=>$giangVien['email']];
$he_dao_tao_filter = is_string($_GET['he_dao_tao'] ?? null) && in_array($_GET['he_dao_tao'], ['co_so_nganh','chuyen_nganh'], true) ? $_GET['he_dao_tao'] : 'all';
$filter = 'da_duyet';
$tatCaDeTai = $model->getPublicTopics($giangVien['id']);
$danhSachDeTai = $he_dao_tao_filter === 'all' ? $tatCaDeTai : array_values(array_filter($tatCaDeTai, static function ($topic) use ($he_dao_tao_filter) { return $topic['he_dao_tao'] === $he_dao_tao_filter; }));
$thongKe = ['tong'=>count($tatCaDeTai), 'da_duyet'=>count($tatCaDeTai), 'co_so_nganh'=>0, 'chuyen_nganh'=>0];
foreach ($tatCaDeTai as $topic) { ++$thongKe[$topic['he_dao_tao']]; }
