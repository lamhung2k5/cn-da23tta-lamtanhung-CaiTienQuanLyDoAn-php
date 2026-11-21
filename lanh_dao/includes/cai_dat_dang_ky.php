<?php
$registrationSettings = (new DangKyThoiGian())->settings();
$savedLimit = (new DeTaiQuanTamModel())->limit();
?>
<div class="tab-pane fade" id="content-dang-ky" role="tabpanel" aria-labelledby="tab-dang-ky">
    <div class="p-4">
        <h4>Đăng ký đề tài và danh sách quan tâm</h4>
        <p class="text-muted">Giới hạn quan tâm dùng chung cho Cơ sở ngành và Chuyên ngành. Quan tâm không giữ chỗ đăng ký.</p>
        <form method="POST" action="xu_ly_cai_dat_dang_ky.php">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="gioi-han-quan-tam">Số đề tài quan tâm tối đa của mỗi sinh viên</label>
                <input class="form-control" style="max-width:240px" id="gioi-han-quan-tam" name="gioi_han_de_tai_quan_tam" type="number" min="1" max="50" value="<?= $savedLimit ?>" required>
                <div class="form-text">Từ 1 đến 50. Nếu giảm giới hạn, danh sách đã lưu vẫn giữ nguyên; sinh viên phải bỏ bớt trước khi thêm mới.</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="cho-phep-dang-ky">Trạng thái đăng ký</label>
                <select class="form-select" id="cho-phep-dang-ky" name="cho_phep_dang_ky">
                    <option value="1" <?= $registrationSettings['cho_phep_dang_ky'] === '1' ? 'selected' : '' ?>>Mở theo thời gian bên dưới</option>
                    <option value="0" <?= $registrationSettings['cho_phep_dang_ky'] !== '1' ? 'selected' : '' ?>>Tạm đóng đăng ký</option>
                </select>
                <div class="form-text">Tạm đóng đăng ký không chặn việc thêm/bỏ quan tâm.</div>
            </div>
            <div class="row g-3 mb-4">
                <?php foreach (['csn'=>'Cơ sở ngành','cn'=>'Chuyên ngành'] as $suffix=>$label): ?>
                    <fieldset class="col-md-6">
                        <legend class="h6"><?= e($label) ?></legend>
                        <?php foreach (['bat_dau'=>'Mở đăng ký','ket_thuc'=>'Đóng đăng ký'] as $field=>$fieldLabel):
                            $key = 'dang_ky_' . $suffix . '_' . $field;
                        ?>
                            <label class="form-label" for="<?= e($key) ?>"><?= e($fieldLabel) ?></label>
                            <input class="form-control mb-2" id="<?= e($key) ?>" name="<?= e($key) ?>" type="datetime-local" step="1" value="<?= e(str_replace(' ', 'T', $registrationSettings[$key])) ?>" required>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endforeach; ?>
            </div>
            <p class="small text-muted">Thời gian theo Việt Nam (Asia/Ho_Chi_Minh). Server kiểm tra lại lịch và điều kiện tại thời điểm sinh viên bấm Đăng ký.</p>
            <button class="btn btn-primary" type="submit">Lưu cài đặt đăng ký</button>
        </form>
    </div>
</div>
