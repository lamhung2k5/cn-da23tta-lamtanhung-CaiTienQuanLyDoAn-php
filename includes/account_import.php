<?php
requireRole(ROLE_LANH_DAO);
require_once ROOT_PATH . 'helpers/account_import.php';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $count = importSchoolAccounts(readAccountImport($_FILES['file'] ?? null), $importRole);
        $success = 'Đã nhập ' . $count . ' tài khoản.';
    } catch (Throwable $exception) {
        $error = $exception instanceof DomainException ? $exception->getMessage() : safeError($exception);
    }
}
$pageTitle = $importRole === ROLE_SINH_VIEN ? 'Import sinh viên' : 'Import giảng viên';
include ROOT_PATH . 'includes/header.php';
?>
<main class="container py-5">
    <h1><?= e($pageTitle) ?></h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <p>Chỉ nhận CSV UTF-8 hoặc XLSX, tối đa 2 MB và 2000 tài khoản. Một dòng lỗi sẽ hủy toàn bộ lần nhập.</p>
    <p>Cột bắt buộc: <code>email, ho_ten, mat_khau, <?= $importRole === ROLE_SINH_VIEN ? 'ma_sinh_vien' : 'ma_giang_vien' ?></code>.</p>
    <p>Mật khẩu riêng cho từng tài khoản, dài 8–72 byte. File import này chỉ cấp vai trò <?= e($importRole) ?>.</p>
    <form method="POST" enctype="multipart/form-data"><?= csrf_field() ?>

        <label for="account-import-file" class="form-label">File danh sách</label>
        <input id="account-import-file" class="form-control mb-3" type="file" name="file" accept=".csv,.xlsx" required>
        <button type="submit" class="btn btn-primary">Nhập tài khoản</button>
        <a class="btn btn-secondary" href="<?= e(BASE_URL) ?>lanh_dao/quan_ly_nguoi_dung.php">Quay lại</a>
    </form>
</main>
<?php include ROOT_PATH . 'includes/footer.php'; ?>
