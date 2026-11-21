<?php if (!defined('ROOT_PATH')) { http_response_code(404); exit; } ?>
<?php foreach ($facultyMembers as $member): ?>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4 faculty-item" data-position="<?= e($member['chuc_vu_cong_khai']) ?>" data-name="<?= e(mb_strtolower($member['ho_ten'], 'UTF-8')) ?>">
        <div class="faculty-card">
            <div class="faculty-avatar"><i class="bi bi-person-circle"></i></div>
            <div class="faculty-content">
                <div class="faculty-info"><strong style="color: #d2691e;">Họ tên:</strong> <span><?= e(trim(($member['hoc_ham_hoc_vi'] ?? '') . ' ' . $member['ho_ten'])) ?></span></div>
                <div class="faculty-info"><strong style="color: #d2691e;">Chức vụ:</strong> <span><?= e($member['chuc_vu_cong_khai']) ?></span></div>
                <div class="faculty-info"><strong style="color: #d2691e;">Số nội bộ:</strong> <span><?= e($member['so_noi_bo'] ?: 'Chưa cập nhật') ?></span></div>
                <div class="faculty-info"><strong style="color: #d2691e;">Điện thoại:</strong> <span><?= e($member['so_dien_thoai'] ?: 'Chưa cập nhật') ?></span></div>
                <div class="faculty-info"><strong style="color: #d2691e;">Email:</strong> <span><?= e($member['email']) ?></span></div>
                <a href="giang_vien_chi_tiet_v2.php?id=<?= (int)$member['id'] ?>" class="btn-detail">Danh sách đề tài</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php if (!$facultyMembers): ?>
    <p class="text-center text-muted">Chưa có giảng viên được công khai.</p>
<?php endif; ?>
