<?php if (!defined('ROOT_PATH')) { http_response_code(404); exit; } ?>
<?php if ($failedDraft): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const draft = <?= js($failedDraft) ?>;
    const modal = document.getElementById(draft.action === 'update' ? 'editNotificationModal' : 'addNotificationModal');
    if (!modal || !window.bootstrap) return;
    for (const field of ['id','tieu_de','noi_dung','loai','ngay_bat_dau','ngay_ket_thuc','trang_thai']) {
        const input = modal.querySelector('[name="' + field + '"]');
        if (input && Object.prototype.hasOwnProperty.call(draft, field)) input.value = draft[field];
    }
    bootstrap.Modal.getOrCreateInstance(modal).show();
});
</script>
<?php endif; ?>
