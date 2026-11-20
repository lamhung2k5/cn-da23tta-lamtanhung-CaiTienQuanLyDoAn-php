<?php if (!hasRole(ROLE_LANH_DAO)) { securityDeny(); } ?>
<div class="card mt-4">
    <div class="card-header">Khóa, mở khóa và cấp lại mật khẩu</div>
    <div class="card-body">
        <form method="POST" action="xu_ly_tai_khoan.php"><?= csrf_field() ?>

            <label class="form-label" for="security-user">Tài khoản</label>
            <select class="form-select mb-3" name="user_id" id="security-user" required>
                <?php foreach ($danhSachNguoiDung as $account): ?>
                    <option value="<?= (int)$account['id'] ?>"><?= e($account['ho_ten'] . ' — ' . $account['email']) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="form-label" for="security-action">Thao tác</label>
            <select class="form-select mb-3" name="action" id="security-action" required>
                <option value="set_status">Đổi trạng thái</option>
                <option value="reset_password">Cấp lại mật khẩu</option>
            </select>
            <label class="form-label" for="security-status">Trạng thái khi đổi</label>
            <select class="form-select mb-3" name="status" id="security-status">
                <option value="inactive">Khóa</option><option value="active">Hoạt động</option>
            </select>
            <label class="form-label" for="security-new-password">Mật khẩu mới khi cấp lại (8–72 byte)</label>
            <input class="form-control mb-3" id="security-new-password" type="password" name="new_password" autocomplete="new-password" maxlength="72">
            <label class="form-label" for="security-confirm-password">Nhập lại mật khẩu mới</label>
            <input class="form-control mb-3" id="security-confirm-password" type="password" name="confirm_password" autocomplete="new-password" maxlength="72">
            <label class="form-label" for="security-current-password">Mật khẩu hiện tại của Lãnh đạo để xác nhận</label>
            <input class="form-control mb-3" id="security-current-password" type="password" name="current_password" autocomplete="current-password" required>
            <button class="btn btn-primary" type="submit">Xác nhận cập nhật</button>
        </form>
    </div>
</div>
