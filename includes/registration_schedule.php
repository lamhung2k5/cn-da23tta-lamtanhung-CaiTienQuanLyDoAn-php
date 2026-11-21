<?php
if (!defined('ROOT_PATH')) { http_response_code(404); exit; }
$registrationSchedule = new DangKyThoiGian();
?>
<section class="card my-4"><div class="card-body">
    <h3 class="h5">Lịch đăng ký đề tài của hệ thống</h3>
    <p class="text-muted">Lịch dưới đây lấy từ cấu hình mở/đóng đăng ký. Các mốc nộp bài, báo cáo và bảo vệ được trình bày riêng bên dưới.</p>
    <div class="row g-3">
    <?php foreach (['co_so_nganh'=>'Cơ sở ngành','chuyen_nganh'=>'Chuyên ngành'] as $type=>$label): $scheduleState = $registrationSchedule->status($type); ?>
        <div class="col-md-6"><div class="border rounded p-3 h-100">
            <h4 class="h6"><?= e($label) ?></h4><p class="fw-semibold"><?= e($scheduleState['label']) ?></p>
            <?php if (isset($scheduleState['starts_at'], $scheduleState['ends_at'])): ?>
                <p class="mb-1">Mở: <?= e(date('H:i:s d/m/Y', DangKyThoiGian::timestamp($scheduleState['starts_at']))) ?></p>
                <p class="mb-0">Đóng: <?= e(date('H:i:s d/m/Y', DangKyThoiGian::timestamp($scheduleState['ends_at']))) ?></p>
            <?php endif; ?>
        </div></div>
    <?php endforeach; ?>
    </div>
</div></section>
