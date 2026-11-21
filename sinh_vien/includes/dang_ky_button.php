<?php // Caller supplies $registrationState, $registrationTopicId, $registrationReturn. ?>
<?php if ($registrationState['can_register']): ?>
    <form method="POST" action="<?= e(BASE_URL) ?>sinh_vien/dang_ky_de_tai.php" class="d-inline-block">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$registrationTopicId ?>">
        <input type="hidden" name="return_to" value="<?= e($registrationReturn ?? 'list') ?>">
        <input type="hidden" name="loai" value="<?= e($quanTamType ?? 'co_so_nganh') ?>">
        <button type="submit" class="btn btn-danger btn-sm" data-register-topic="<?= (int)$registrationTopicId ?>"><i class="bi bi-bookmark-plus-fill me-1"></i>Đăng ký ngay</button>
    </form>
<?php else: ?>
    <button class="btn btn-danger btn-sm" disabled title="<?= e($registrationState['reason'] ?? $registrationState['description']) ?>"><i class="bi bi-lock-fill me-1"></i>Đăng ký ngay</button>
<?php endif; ?>
