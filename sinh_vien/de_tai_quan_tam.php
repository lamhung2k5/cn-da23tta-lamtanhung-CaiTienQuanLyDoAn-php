<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_SINH_VIEN);
$pageTitle = 'Đề tài quan tâm';
$sinhVien = (new SinhVienModel())->getByNguoiDungId(getCurrentUser()['id']);
if (!$sinhVien) { securityDeny(403, 'Không tìm thấy hồ sơ Sinh viên.'); }
$quanTamModel = new DeTaiQuanTamModel();
$type = in_array($_GET['loai'] ?? '', ['co_so_nganh','chuyen_nganh'], true) ? $_GET['loai'] : null;
$topics = $quanTamModel->listForStudent($sinhVien['id'], $type);
$total = $quanTamModel->savedCount($sinhVien['id']);
$limit = $quanTamModel->limit();
$context = $quanTamModel->registrationContext($sinhVien['id']);
$window = new DangKyThoiGian();
$success = getFlashMessage('success');
$error = getFlashMessage('error');
include ROOT_PATH . 'includes/header.php';
?>
<link rel="stylesheet" href="<?= e(BASE_URL) ?>assets/css/de_tai_quan_tam.css">
<script defer src="<?= e(BASE_URL) ?>assets/js/de_tai_quan_tam.js"></script>
<div class="container-fluid"><div class="row">
    <aside class="col-md-2 sidebar"><nav class="nav flex-column">
        <a class="nav-link" href="dashboard.php"><i class="bi bi-house-door"></i> Trang chủ</a>
        <a class="nav-link" href="danh_sach_de_tai.php"><i class="bi bi-journal-text"></i> Đề tài có thể đăng ký</a>
        <?php include __DIR__ . '/includes/quan_tam_sidebar.php'; ?>
        <a class="nav-link" href="de_tai_cua_toi.php"><i class="bi bi-bookmark-check"></i> Đề tài của tôi</a>
    </nav></aside>
    <main class="col-md-10 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h2 class="mb-0">Đề tài quan tâm <span class="badge bg-primary fs-6"><?= $total ?>/<?= $limit ?></span></h2>
            <a class="btn btn-outline-primary" href="danh_sach_de_tai.php">Tìm thêm đề tài</a>
        </div>
        <p class="text-muted">Lưu trước các đề tài để đăng ký nhanh khi mở đợt. Quan tâm không giữ chỗ hoặc tạo quyền ưu tiên.</p>
        <?php if ($success): ?><div class="alert alert-success" role="status"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
        <?php if ($total >= $limit): ?><div class="alert alert-warning">Bạn đã lưu <?= $total ?> đề tài; giới hạn hiện tại là <?= $limit ?>. Danh sách vẫn được giữ. Hãy bỏ bớt trước khi thêm mới.</div><?php endif; ?>
        <nav class="nav nav-pills gap-2 mb-4" aria-label="Lọc đề tài quan tâm">
            <?php foreach ([''=>'Tất cả','co_so_nganh'=>'Cơ sở ngành','chuyen_nganh'=>'Chuyên ngành'] as $value=>$label): ?>
                <a class="nav-link <?= ($type ?? '') === $value ? 'active' : '' ?>" href="de_tai_quan_tam.php<?= $value ? '?loai=' . $value : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <?php if (!$topics): ?>
            <div class="card"><div class="card-body text-center py-5">
                <i class="bi bi-bookmark-heart fs-1 text-muted" aria-hidden="true"></i>
                <p class="mt-3 mb-0"><?= $total ? 'Chưa có đề tài quan tâm trong hệ đào tạo này.' : 'Bạn chưa quan tâm đề tài nào. Chọn nút Quan tâm trên thẻ đề tài để bắt đầu.' ?></p>
            </div></div>
        <?php endif; ?>
        <?php foreach ($topics as $topic):
            $state = DeTaiQuanTamService::registrationState($topic, $context, $window);
            $available = $topic['trang_thai_de_tai'] === STATUS_DA_DUYET;
        ?>
            <article class="card shadow-sm mb-3" data-saved-topic="<?= (int)$topic['quan_tam_id'] ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <h3 class="h5"><?= e($topic['tieu_de']) ?></h3>
                        <span class="badge bg-light text-dark align-self-start"><?= e(getHeDaoTaoLabel($topic['he_dao_tao'])) ?></span>
                    </div>
                    <?php if ($available): ?>
                        <p class="mb-2">Giảng viên: <strong><?= e($topic['ten_giang_vien']) ?></strong> · Còn <?= max(0,(int)$topic['con_lai']) ?>/<?= (int)$topic['so_luong_sv'] ?> chỗ</p>
                    <?php endif; ?>
                    <p class="mb-1"><strong><?= e($state['label']) ?></strong></p>
                    <?php if ($state['description'] !== ''): ?><p class="text-muted small"><?= e($state['description']) ?></p><?php endif; ?>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <?php if ($available): ?><a class="btn btn-outline-secondary btn-sm" href="chi_tiet_de_tai.php?id=<?= (int)$topic['de_tai_id'] ?>">Xem chi tiết</a><?php endif; ?>
                        <?php if ($state['can_register']): ?>
                            <form method="POST" action="dang_ky_de_tai.php" class="d-inline-block">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$topic['de_tai_id'] ?>">
                                <input type="hidden" name="return_to" value="followed"><input type="hidden" name="filter" value="<?= e($type ?? '') ?>">
                                <button class="btn btn-primary btn-sm" type="submit" data-register-topic="<?= (int)$topic['de_tai_id'] ?>">Đăng ký</button>
                            </form>
                        <?php elseif ($state['state'] === 'registered' || $state['state'] === 'existing'): ?>
                            <a class="btn btn-outline-success btn-sm" href="de_tai_cua_toi.php">Đề tài của tôi</a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled title="<?= e($state['reason'] ?? $state['label']) ?>">Đăng ký</button>
                        <?php endif; ?>
                        <form method="POST" action="xu_ly_quan_tam.php" class="d-inline-block">
                            <?= csrf_field() ?><input type="hidden" name="action" value="remove">
                            <input type="hidden" name="quan_tam_id" value="<?= (int)$topic['quan_tam_id'] ?>">
                            <input type="hidden" name="return_to" value="followed"><input type="hidden" name="filter" value="<?= e($type ?? '') ?>">
                            <button class="btn btn-outline-danger btn-sm" type="submit">Bỏ quan tâm</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </main>
</div></div>
<?php
$hideFooter = true;
include ROOT_PATH . 'includes/footer.php';
?>
