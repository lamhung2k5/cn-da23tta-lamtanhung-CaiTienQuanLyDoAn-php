<?php
// $quanTamTopicId, $quanTamSavedId, $quanTamReturn, $quanTamType set by the calling page.
$isSaved = !empty($quanTamSavedId);
$registeredMessage = DeTaiQuanTamService::followRestriction($quanTamType ?? '', $registrationContext);
$cannotAdd = !$isSaved && $registeredMessage !== '';
?>
<form method="POST" action="<?= e(BASE_URL) ?>sinh_vien/xu_ly_quan_tam.php" class="d-inline-block quan-tam-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $isSaved ? 'remove' : 'add' ?>">
    <input type="hidden" name="de_tai_id" value="<?= (int)($quanTamTopicId ?? 0) ?>">
    <input type="hidden" name="quan_tam_id" value="<?= (int)($quanTamSavedId ?? 0) ?>">
    <input type="hidden" name="return_to" value="<?= e($quanTamReturn ?? 'list') ?>">
    <input type="hidden" name="loai" value="<?= e($quanTamType ?? 'co_so_nganh') ?>">
    <input type="hidden" name="filter" value="<?= e($quanTamFilter ?? '') ?>">
    <input type="hidden" name="search" value="<?= e($search ?? '') ?>">
    <button type="submit" class="btn btn-sm <?= $isSaved ? 'btn-warning' : 'btn-outline-primary' ?>" aria-pressed="<?= $isSaved ? 'true' : 'false' ?>" <?= $cannotAdd ? 'disabled data-registered-follow-disabled="true"' : '' ?> aria-label="<?= e($cannotAdd ? 'Quan tâm: ' . $registeredMessage : ($isSaved ? 'Bỏ quan tâm' : 'Quan tâm')) ?>" title="<?= e($cannotAdd ? $registeredMessage : ($isSaved ? 'Bấm để bỏ quan tâm' : 'Lưu đề tài để lựa chọn nhanh; không giữ chỗ')) ?>">
        <i class="bi <?= $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark-plus' ?> me-1" aria-hidden="true"></i><?= $isSaved ? 'Đã quan tâm' : 'Quan tâm' ?>
    </button>
</form>
