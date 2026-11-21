<?php
if (!defined('ROOT_PATH')) { http_response_code(404); exit; }
$message = getFlashMessage('success') ?: '';
$error = getFlashMessage('error') ?: '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tab = $_POST['current_tab'] ?? 'page-settings';
    if (!in_array($tab, ['page-settings','co-so-nganh','chuyen-nganh','notice'], true)) { $tab = 'page-settings'; }
    $db = Database::getInstance()->getConnection();
    try {
        $db->beginTransaction();
        $action = $_POST['action'] ?? '';
        if ($action === 'update_page') {
            $contentModel->updatePageContent($pageContent['id'] ?? null, $_POST);
            if (isset($_POST['notice_tieu_de']) && ($_POST['notice_tieu_de'] !== '' || ($_POST['notice_noi_dung'] ?? '') !== '')) {
                $contentModel->updateNotice($notice['id'] ?? null, ['tieu_de'=>$_POST['notice_tieu_de'], 'noi_dung'=>$_POST['notice_noi_dung'] ?? '', 'trang_thai'=>$_POST['notice_trang_thai'] ?? ($notice['trang_thai'] ?? 'mo')]);
            }
            $tab = 'page-settings';
        } elseif (in_array($action, ['add_timeline','update_timeline'], true)) {
            $data = $_POST;
            $requirements = $data['yeu_cau'] ?? [];
            if (!is_array($requirements) || count($requirements) > 50) { throw new DomainException('Danh sách yêu cầu không hợp lệ.'); }
            $items = [];
            foreach ($requirements as $item) { $items[] = ThongBaoPolicy::text($item, 'Yêu cầu', 1000); }
            $data['yeu_cau'] = implode('|', array_filter($items, static function ($item) { return $item !== ''; }));
            if ($action === 'add_timeline') { $contentModel->addTimelineItem($data); }
            else { $contentModel->updateTimelineItem($_POST['id'] ?? null, $data); }
            $tab = ($data['loai'] ?? '') === 'chuyen_nganh' ? 'chuyen-nganh' : 'co-so-nganh';
        } elseif ($action === 'delete_timeline') {
            $contentModel->deleteTimelineItem($_POST['id'] ?? null);
        } elseif ($action === 'toggle_timeline') {
            $id = ThongBaoPolicy::positiveId($_POST['id'] ?? null);
            if (!$contentModel->toggleTimelineStatus($id)) { throw new DomainException('Không tìm thấy mốc thời gian.'); }
        } elseif ($action === 'update_notice') {
            $contentModel->updateNotice($notice['id'] ?? null, $_POST);
        } else { throw new DomainException('Thao tác không hợp lệ.'); }
        $db->commit();
        setFlashMessage('success', 'Đã cập nhật nội dung thông báo đồ án.');
    } catch (Throwable $exception) {
        if ($db->inTransaction()) { $db->rollBack(); }
        setFlashMessage('error', $exception instanceof DomainException ? $exception->getMessage() : safeError($exception));
    }
    redirect('lanh_dao/quan_ly_noi_dung_do_an.php?tab=' . urlencode($tab));
}
