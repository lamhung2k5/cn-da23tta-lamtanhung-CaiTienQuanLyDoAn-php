<?php
require_once __DIR__ . '/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { securityDeny(405, 'Trang này chỉ dùng để đọc thông báo.'); }
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$preview = ($_GET['preview'] ?? null) === '1' && hasRole(ROLE_LANH_DAO);
$announcement = $id && $id > 0 ? (new ThongBaoModel())->getPublicAnnouncement($id, $preview) : null;
header('Cache-Control: no-store');
if (!$announcement) {
    http_response_code(404);
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><title>Không tìm thấy thông báo</title><p>Thông báo không tồn tại hoặc hiện không được công khai.</p><a href="index.php">Về trang chủ</a></html>';
    exit;
}
?>
<!doctype html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($announcement['tieu_de']) ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><main class="container py-4" style="max-width:960px">
    <a href="index.php" class="btn btn-outline-primary mb-3">Về trang chủ</a>
    <?php if ($preview): ?><div class="alert alert-warning">Xem trước bản đã lưu · <?= e(ThongBaoPolicy::LABELS[ThongBaoPolicy::state($announcement)]) ?>. Chỉ Lãnh đạo xem được bản chưa công khai.</div><a class="btn btn-outline-secondary mb-3" href="lanh_dao/quan_ly_thong_bao.php">Quay lại quản lý</a><?php endif; ?>
    <article class="card shadow-sm"><div class="card-body p-4" style="overflow-wrap:anywhere"><h1 class="h3"><?= e($announcement['tieu_de']) ?></h1><p class="text-muted">Ngày đăng: <?= e(date('d/m/Y', strtotime($announcement['created_at']))) ?></p><div class="lh-lg"><?= nl2br(e($announcement['noi_dung'])) ?></div></div></article>
</main></body></html>
