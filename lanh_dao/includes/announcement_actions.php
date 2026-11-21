<?php
if (!defined('ROOT_PATH')) { http_response_code(404); exit; }
$message = getFlashMessage('success') ?: '';
$error = getFlashMessage('error') ?: '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if (in_array($action, ['add','update'], true)) {
            $thongBaoModel->savePublicAnnouncement($action === 'update' ? ($_POST['id'] ?? '') : null, $_POST);
        } else { $thongBaoModel->changePublicAnnouncement($_POST['id'] ?? null, $action); }
        setFlashMessage('success', 'Đã cập nhật thông báo.');
    } catch (Throwable $exception) {
        setFlashMessage('error', $exception instanceof DomainException ? $exception->getMessage() : safeError($exception));
        if (in_array($_POST['action'] ?? '', ['add','update'], true)) {
            $draft = array_intersect_key($_POST, array_flip(['action','id','tieu_de','noi_dung','loai','ngay_bat_dau','ngay_ket_thuc','trang_thai']));
            $_SESSION['announcement_draft'] = array_filter($draft, static function ($value) { return is_string($value) && strlen($value) <= 65536; });
        }
    }
    redirect('lanh_dao/quan_ly_thong_bao.php');
}
$search = is_string($_GET['search'] ?? null) ? trim($_GET['search']) : '';
$state = is_string($_GET['state'] ?? null) && isset(ThongBaoPolicy::LABELS[$_GET['state']]) ? $_GET['state'] : '';
$matchingAnnouncements = $thongBaoModel->listPublicForManagement($search, $state);
$totalPages = max(1, (int)ceil(count($matchingAnnouncements) / 20));
$currentListPage = max(1, min($totalPages, (int)filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT)));
$dsThongBao = array_slice($matchingAnnouncements, ($currentListPage - 1) * 20, 20);
$failedDraft = $_SESSION['announcement_draft'] ?? null;
unset($_SESSION['announcement_draft']);
