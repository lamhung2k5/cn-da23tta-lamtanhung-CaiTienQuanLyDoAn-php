<?php if (!defined('ROOT_PATH')) { http_response_code(404); exit; } ?>
<form method="GET" class="row g-2 mb-3 align-items-end">
    <div class="col-md-6"><label for="notice-search" class="form-label">Tìm thông báo</label><input id="notice-search" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Tiêu đề hoặc nội dung"></div>
    <div class="col-md-3"><label for="notice-state" class="form-label">Trạng thái hiển thị</label><select id="notice-state" name="state" class="form-select"><option value="">Tất cả</option><?php foreach (ThongBaoPolicy::LABELS as $value=>$label): ?><option value="<?= e($value) ?>" <?= $state === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><button class="btn btn-primary">Tìm kiếm</button> <a href="quan_ly_thong_bao.php" class="btn btn-outline-secondary">Xóa lọc</a></div>
</form>
<p class="text-muted">Tìm thấy <?= count($matchingAnnouncements) ?> thông báo. Ngày kết thúc được tính đến hết ngày; lịch hiển thị không điều khiển đăng ký đề tài.</p>
<?php if ($totalPages > 1): ?><nav aria-label="Phân trang thông báo"><ul class="pagination flex-wrap"><?php for ($i=1;$i<=$totalPages;$i++): ?><li class="page-item <?= $i === $currentListPage ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query(['search'=>$search,'state'=>$state,'page'=>$i])) ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
