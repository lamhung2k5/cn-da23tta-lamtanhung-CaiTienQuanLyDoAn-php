<?php
if (!isset($sinhVien) || !$sinhVien) { $sinhVien = (new SinhVienModel())->getByNguoiDungId(getCurrentUser()['id']); }
$sidebarQuanTam = new DeTaiQuanTamModel();
$sidebarCount = $sinhVien ? $sidebarQuanTam->savedCount($sinhVien['id']) : 0;
$sidebarLimit = $sidebarQuanTam->limit();
?>
<a class="nav-link <?= basename($_SERVER['SCRIPT_NAME']) === 'de_tai_quan_tam.php' ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>sinh_vien/de_tai_quan_tam.php">
    <i class="bi bi-bookmark-heart"></i> Đề tài quan tâm
    <span class="badge bg-secondary ms-1" data-quan-tam-count><?= $sidebarCount ?>/<?= $sidebarLimit ?></span>
</a>
