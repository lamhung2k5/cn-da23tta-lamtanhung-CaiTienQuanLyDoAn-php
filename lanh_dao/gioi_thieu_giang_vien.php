<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_LANH_DAO);
$pageTitle = 'Quản lý giới thiệu giảng viên';
$currentPage = 'gioi_thieu_giang_vien.php';
$model = new GiangVienModel();
$profiles = $model->getDirectory(false);
$selectedId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$selected = null;
foreach ($profiles as $profile) {
    if ((int)$profile['id'] === (int)$selectedId) { $selected = $profile; }
}
$error = getFlashMessage('error');
$success = getFlashMessage('success');
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid"><div class="row">
    <?php include __DIR__ . '/includes/sidebar_lanh_dao.php'; ?>
    <main class="col-md-10 p-4">
        <h3>Giới thiệu giảng viên</h3>
        <p class="text-muted">Quản lý thông tin hiển thị trên trang giới thiệu Khoa. Chỉ tài khoản đang hoạt động và được bật công khai mới xuất hiện.</p>
        <a class="btn btn-outline-secondary mb-3" href="quan_ly_nguoi_dung.php">Quản lý tài khoản</a>
        <a class="btn btn-outline-primary mb-3" href="../khoa_cntt.php" target="_blank">Xem trang công khai</a>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($selectedId && !$selected): ?><div class="alert alert-warning">Không tìm thấy hồ sơ giảng viên.</div><?php endif; ?>
        <?php if ($selected): ?>
            <section class="card mb-4"><div class="card-body">
                <h4><?= e($selected['ho_ten']) ?></h4>
                <p><?= e($selected['email']) ?> · <?= e($selected['ma_giang_vien']) ?></p>
                <form method="POST" action="xu_ly_gioi_thieu_giang_vien.php" class="row g-3">
                    <?= csrf_field() ?><input type="hidden" name="giang_vien_id" value="<?= (int)$selected['id'] ?>">
                    <div class="col-md-6">
                        <label for="public-position" class="form-label">Chức vụ giới thiệu</label>
                        <select id="public-position" name="chuc_vu_cong_khai" class="form-select">
                            <?php foreach (['Giảng viên','Trưởng khoa','Phó Trưởng khoa'] as $position): ?>
                                <option <?= $selected['chuc_vu_cong_khai'] === $position ? 'selected' : '' ?>><?= e($position) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Thông tin giới thiệu không thay đổi quyền đăng nhập.</div>
                    </div>
                    <?php foreach (['hoc_ham_hoc_vi'=>['Học hàm / học vị',50], 'so_noi_bo'=>['Số nội bộ',30], 'khoa'=>['Khoa',255], 'chuyen_mon'=>['Chuyên môn',255], 'so_dien_thoai'=>['Điện thoại công khai',20]] as $field=>$meta): ?>
                        <div class="col-md-6">
                            <label for="profile-<?= e($field) ?>" class="form-label"><?= e($meta[0]) ?></label>
                            <input id="profile-<?= e($field) ?>" name="<?= e($field) ?>" class="form-control" maxlength="<?= (int)$meta[1] ?>" value="<?= e($selected[$field] ?? '') ?>">
                        </div>
                    <?php endforeach; ?>
                    <div class="col-12">
                        <div class="form-check">
                            <input id="public-visible" name="hien_thi_cong_khai" type="checkbox" class="form-check-input" value="1" <?= $selected['hien_thi_cong_khai'] ? 'checked' : '' ?>>
                            <label for="public-visible" class="form-check-label">Hiển thị trên trang giới thiệu</label>
                        </div>
                    </div>
                    <div class="col-12"><button class="btn btn-primary">Lưu thông tin</button></div>
                </form>
            </div></section>
        <?php endif; ?>
        <div class="table-responsive"><table class="table table-bordered align-middle bg-white">
            <thead><tr><th>Giảng viên</th><th>Email</th><th>Chức vụ</th><th>Tài khoản</th><th>Công khai</th><th>Thao tác</th></tr></thead>
            <tbody>
            <?php foreach ($profiles as $profile): ?>
                <tr><td><?= e($profile['ho_ten']) ?></td><td><?= e($profile['email']) ?></td>
                    <td><?= e($profile['chuc_vu_cong_khai']) ?></td>
                    <td><?= $profile['trang_thai'] === 'active' ? 'Hoạt động' : 'Đã khóa' ?></td>
                    <td><?= $profile['hien_thi_cong_khai'] ? ($profile['trang_thai'] === 'active' ? 'Đang hiển thị' : 'Đã bật, tạm ẩn do khóa tài khoản') : 'Ẩn' ?></td>
                    <td><a class="btn btn-outline-primary btn-sm" href="?id=<?= (int)$profile['id'] ?>">Chỉnh sửa</a></td></tr>
            <?php endforeach; ?>
            <?php if (!$profiles): ?><tr><td colspan="6">Chưa có hồ sơ giảng viên trong hệ thống.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </main>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
