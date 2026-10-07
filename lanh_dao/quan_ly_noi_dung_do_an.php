<?php
/**
 * QUẢN LÝ NỘI DUNG THÔNG BÁO ĐỒ ÁN - GIAO DIỆN MỚI
 * Trang để lãnh đạo chỉnh sửa toàn bộ nội dung trang thong_bao_do_an.php
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$pageTitle = 'Quản lý nội dung thông báo đồ án';

$contentModel = new ThongBaoDoAnModel();
$thongBaoModel = new ThongBaoModel();

// Get current data
$pageContent = $contentModel->getPageContent() ?: [];
$timelineCoSoNganh = $contentModel->getTimelineItemsForAdmin('co_so_nganh'); // Admin view - show all
$timelineChuyenNganh = $contentModel->getTimelineItemsForAdmin('chuyen_nganh'); // Admin view - show all
$notice = $contentModel->getNoticeForAdmin() ?: []; // Use admin version to get notice regardless of status

// Handle form submissions
$message = '';
$error = '';

// Check for messages from redirect
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $redirectTab = $_POST['current_tab'] ?? 'page-settings';

    // Update page content & notice
    if ($action === 'update_page') {
        $data = [
            'page_title' => $_POST['page_title'],
            'subtitle' => $_POST['subtitle'],
            'date_badge' => $_POST['date_badge'],
            'trang_thai' => $_POST['trang_thai'],
            'ngay_bat_dau' => !empty($_POST['ngay_bat_dau']) ? $_POST['ngay_bat_dau'] : null,
            'ngay_ket_thuc' => !empty($_POST['ngay_ket_thuc']) ? $_POST['ngay_ket_thuc'] : null
        ];
        $pageId = $pageContent['id'] ?? null;
        $contentModel->updatePageContent($pageId, $data);

        if (isset($_POST['notice_tieu_de'])) {
            $noticeData = [
                'tieu_de' => $_POST['notice_tieu_de'],
                'noi_dung' => $_POST['notice_noi_dung'],
                'trang_thai' => $_POST['trang_thai']
            ];
            $noticeId = $notice['id'] ?? null;
            $contentModel->updateNotice($noticeId, $noticeData);
        }

        $message = 'Cập nhật thông tin trang thành công';
        $redirectTab = 'page-settings';
    }

    // Add timeline item
    elseif ($action === 'add_timeline') {
        $yeuCau = implode('|', array_filter($_POST['yeu_cau'] ?? []));
        $data = [
            'loai' => $_POST['loai'],
            'thu_tu' => $_POST['thu_tu'],
            'tieu_de' => $_POST['tieu_de'],
            'noi_dung' => $_POST['noi_dung'],
            'ngay' => $_POST['ngay'],
            'thang' => $_POST['thang'],
            'nam' => $_POST['nam'],
            'yeu_cau' => $yeuCau
        ];
        $contentModel->addTimelineItem($data);
        $message = 'Thêm mốc thời gian thành công';
        $redirectTab = ($_POST['loai'] ?? '') === 'chuyen_nganh' ? 'chuyen-nganh' : 'co-so-nganh';
    }

    // Update timeline item
    elseif ($action === 'update_timeline') {
        $yeuCau = implode('|', array_filter($_POST['yeu_cau'] ?? []));
        $data = [
            'loai' => $_POST['loai'],
            'thu_tu' => $_POST['thu_tu'],
            'tieu_de' => $_POST['tieu_de'],
            'noi_dung' => $_POST['noi_dung'],
            'ngay' => $_POST['ngay'],
            'thang' => $_POST['thang'],
            'nam' => $_POST['nam'],
            'yeu_cau' => $yeuCau,
            'trang_thai' => $_POST['trang_thai']
        ];
        $contentModel->updateTimelineItem($_POST['id'], $data);
        $message = 'Cập nhật mốc thời gian thành công';
        $redirectTab = ($_POST['loai'] ?? '') === 'chuyen_nganh' ? 'chuyen-nganh' : 'co-so-nganh';
    }

    // Delete timeline item
    elseif ($action === 'delete_timeline') {
        $contentModel->deleteTimelineItem($_POST['id']);
        $message = 'Xóa mốc thời gian thành công';
        $redirectTab = $_POST['current_tab'] ?? 'co-so-nganh';
    }

    // Toggle timeline status
    elseif ($action === 'toggle_timeline') {
        $result = $contentModel->toggleTimelineStatus($_POST['id']);
        if ($result) {
            $message = 'Cập nhật trạng thái thành công';
        } else {
            $error = 'Lỗi khi cập nhật trạng thái!';
        }
        $redirectTab = $_POST['current_tab'] ?? 'co-so-nganh';
    }

    // Update notice
    elseif ($action === 'update_notice') {
        $data = [
            'tieu_de' => $_POST['tieu_de'],
            'noi_dung' => $_POST['noi_dung'],
            'trang_thai' => $_POST['trang_thai']
        ];
        $noticeId = $notice['id'] ?? null;
        $contentModel->updateNotice($noticeId, $data);
        $message = 'Cập nhật lưu ý thành công';
        $redirectTab = 'notice';
    }

    // Chuyển hướng PRG (Post-Redirect-Get) để tránh tự động gửi lại form khi load lại trang
    $redirectUrl = $_SERVER['PHP_SELF'] . '?tab=' . urlencode($redirectTab);
    if ($message) {
        $redirectUrl .= '&message=' . urlencode($message);
    }
    if ($error) {
        $redirectUrl .= '&error=' . urlencode($error);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
    /* Import simple admin CSS */
    @import url('assets/simple-admin.css');
    /* Import enhanced button styles */
    @import url('assets/button-styles.css');
    /* Import animations */
    @import url('assets/animations.css');

    /* Custom styles for this page */
    .timeline-item {
        border-left: 3px solid #0d6efd;
        padding-left: 1rem;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 12px;
        height: 12px;
        background: #0d6efd;
        border-radius: 50%;
    }

    .timeline-item.inactive {
        opacity: 0.7;
        border-left-color: #6c757d;
        background: #f8f9fa;
        position: relative;
    }

    .timeline-item.inactive::before {
        background: #6c757d;
    }

    .timeline-date {
        background: #0d6efd;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 0.5rem;
    }

    .timeline-title {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .timeline-content {
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .timeline-requirements {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.75rem;
        margin-top: 0.5rem;
    }

    .timeline-requirements h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .timeline-requirements ul {
        margin: 0;
        padding-left: 1.25rem;
    }

    .timeline-requirements li {
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    /* ===== CUSTOM BUTTON STYLES FOR QUAN LY NOI DUNG DO AN ===== */

    /* Modern Button Base */
    .btn-modern {
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.6rem 1.2rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        text-decoration: none;
        cursor: pointer;
        user-select: none;
    }

    .btn-modern:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    }

    .btn-modern:active {
        transform: translateY(1px);
    }

    /* Primary Modern Button */
    .btn-primary-modern {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    .btn-primary-modern:hover {
        background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
        color: white;
    }

    .btn-primary-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-primary-modern:hover::before {
        left: 100%;
    }

    /* Success Modern Button */
    .btn-success-modern {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
    }

    .btn-success-modern:hover {
        background: linear-gradient(135deg, #157347 0%, #146c43 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(25, 135, 84, 0.4);
        color: white;
    }

    .btn-success-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-success-modern:hover::before {
        left: 100%;
    }

    /* Warning Modern Button */
    .btn-warning-modern {
        background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
        color: #000;
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    }

    .btn-warning-modern:hover {
        background: linear-gradient(135deg, #ffca2c 0%, #ffc720 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 193, 7, 0.4);
        color: #000;
    }

    .btn-warning-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
        transition: left 0.5s;
    }

    .btn-warning-modern:hover::before {
        left: 100%;
    }

    /* Danger Modern Button */
    .btn-danger-modern {
        background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .btn-danger-modern:hover {
        background: linear-gradient(135deg, #bb2d3b 0%, #b02a37 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
        color: white;
    }

    .btn-danger-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-danger-modern:hover::before {
        left: 100%;
    }

    /* Light Modern Button */
    .btn-light-modern {
        background: #f8f9fa;
        color: #495057;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-light-modern:hover {
        background: #e9ecef;
        color: #495057;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    /* Small Modern Buttons for Actions */
    .btn-sm-modern {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        border-radius: 0.375rem;
    }

    /* Icon-only Modern Buttons */
    .btn-icon-modern {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-icon-modern:hover {
        transform: translateY(-2px) scale(1.05);
    }

    /* Timeline Action Buttons - Redesigned */
    .timeline-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .btn-edit-timeline {
        background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
        border: none;
        color: #000;
        width: 36px;
        height: 36px;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

    .btn-edit-timeline:hover {
        background: linear-gradient(135deg, #ffca2c 0%, #ffc720 100%);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 16px rgba(255, 193, 7, 0.4);
        color: #000;
    }

    .btn-delete-timeline {
        background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
        border: none;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    .btn-delete-timeline:hover {
        background: linear-gradient(135deg, #bb2d3b 0%, #b02a37 100%);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
        color: white;
    }

    .btn-toggle-timeline {
        background: linear-gradient(135deg, #6c757d 0%, #5c636a 100%);
        border: none;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }

    .btn-toggle-timeline:hover {
        background: linear-gradient(135deg, #5c636a 0%, #565e64 100%);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 16px rgba(108, 117, 125, 0.4);
        color: white;
    }

    /* Tab Navigation Buttons */
    .nav-tabs .nav-link {
        border: none;
        border-radius: 0.5rem 0.5rem 0 0;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        color: #6c757d;
        background: #f8f9fa;
        margin-right: 0.25rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .nav-tabs .nav-link:hover {
        background: #e9ecef;
        color: #495057;
        transform: translateY(-2px);
    }

    /* Hover effect riêng cho tab Chuyên ngành */
    .nav-tabs .nav-link[href="#chuyen-nganh"]:hover:not(.active) {
        background: #c3e6cb;
        color: #155724;
        transform: translateY(-2px);
    }

    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    /* CSS riêng cho tab Chuyên ngành khi active - Độ ưu tiên cao */
    .nav-tabs .nav-link.active[href="#chuyen-nganh"] {
        background: linear-gradient(135deg, #155724 0%, #1e7e34 100%) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(21, 87, 36, 0.4) !important;
    }

    /* Selector bổ sung để đảm bảo hoạt động */
    ul.nav.nav-tabs li .nav-link.active[href="#chuyen-nganh"] {
        background: linear-gradient(135deg, #155724 0%, #1e7e34 100%) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(21, 87, 36, 0.4) !important;
    }

    .nav-tabs .nav-link.active::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(255, 255, 255, 0.5);
    }

    /* Form Submit Buttons */
    .btn-form-submit {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        position: relative;
        overflow: hidden;
    }

    .btn-form-submit:hover {
        background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
        color: white;
    }

    .btn-form-submit::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-form-submit:hover::before {
        left: 100%;
    }

    /* Loading State */
    .btn-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.8;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: btn-loading-spin 1s linear infinite;
    }

    @keyframes btn-loading-spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Pulse Animation for Important Buttons */
    .btn-pulse {
        animation: pulse-glow 2s infinite;
    }

    @keyframes pulse-glow {
        0% {
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        50% {
            box-shadow: 0 4px 20px rgba(13, 110, 253, 0.6);
        }

        100% {
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }
    }

    /* Responsive Button Adjustments */
    @media (max-width: 768px) {
        .btn-modern {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .btn-form-submit {
            padding: 0.6rem 1.5rem;
            font-size: 0.9rem;
        }

        .btn-icon-modern {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }

        .timeline-actions {
            gap: 0.25rem;
        }

        .nav-tabs .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
    }

    /* Button Group Enhancements */
    .btn-group .btn-modern:not(:first-child) {
        margin-left: -1px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .btn-group .btn-modern:not(:last-child) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .btn-group .btn-modern:hover {
        z-index: 2;
    }

    /* Special Effects */
    .btn-modern.btn-glow {
        box-shadow: 0 0 20px rgba(13, 110, 253, 0.5);
    }

    .btn-modern.btn-shadow-lg {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .btn-modern.btn-shadow-lg:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    /* Simple Modal Enhancements */
    .modal-content-modern {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .modal-header-modern {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        border-bottom: none;
        padding: 1.5rem;
    }

    .modal-body-modern {
        padding: 2rem;
    }

    .modal-footer-modern {
        border-top: 1px solid #dee2e6;
        padding: 1.5rem;
        background: #f8f9fa;
    }

    .form-label-modern {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control-modern {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        outline: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-body-modern {
            padding: 1rem;
        }

        .modal-footer-modern {
            padding: 1rem;
        }
    }

    /* ===== MODAL BUTTONS STYLING ===== */

    /* Base Modern Button Style */
    .btn-modern {
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.75rem 2rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        text-decoration: none;
        cursor: pointer;
        user-select: none;
        min-width: 160px;
        justify-content: center;
        white-space: nowrap;
    }

    .btn-modern:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    }

    .btn-modern:active {
        transform: translateY(1px);
    }

    /* Light Modern Button (Hủy) */
    .btn-modern.btn-light-modern,
    button.btn-modern.btn-light-modern {
        background: linear-gradient(135deg, #6c757d 0%, #5c636a 100%) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3) !important;
        min-width: 140px !important;
        white-space: nowrap !important;
    }

    .btn-modern.btn-light-modern:hover,
    button.btn-modern.btn-light-modern:hover {
        background: linear-gradient(135deg, #5c636a 0%, #565e64 100%) !important;
        color: white !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4) !important;
        text-decoration: none !important;
    }

    .btn-modern.btn-light-modern:focus,
    button.btn-modern.btn-light-modern:focus {
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(108, 117, 125, 0.25) !important;
    }

    .btn-modern.btn-light-modern:active,
    button.btn-modern.btn-light-modern:active {
        transform: translateY(1px) !important;
    }

    .btn-modern.btn-light-modern::before,
    button.btn-modern.btn-light-modern::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: -100% !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent) !important;
        transition: left 0.5s !important;
    }

    .btn-modern.btn-light-modern:hover::before,
    button.btn-modern.btn-light-modern:hover::before {
        left: 100% !important;
    }

    .btn-modern.btn-light-modern:hover i.bi-x-lg {
        transform: scale(1.1) !important;
    }

    /* Warning Modern Button (Lưu thay đổi) */
    .btn-modern.btn-warning-modern,
    button.btn-modern.btn-warning-modern {
        background: linear-gradient(135deg, #f57c00 0%, #ef6c00 100%) !important;
        color: #000 !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        padding: 0.75rem 2.5rem !important;
        text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3) !important;
        box-shadow: 0 4px 12px rgba(245, 124, 0, 0.3) !important;
        min-width: 180px !important;
        white-space: nowrap !important;
    }

    .btn-modern.btn-warning-modern:hover,
    button.btn-modern.btn-warning-modern:hover {
        background: linear-gradient(135deg, #ef6c00 0%, #e65100 100%) !important;
        color: #000 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(245, 124, 0, 0.4) !important;
        text-decoration: none !important;
    }

    .btn-modern.btn-warning-modern:focus,
    button.btn-modern.btn-warning-modern:focus {
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.25) !important;
    }

    .btn-modern.btn-warning-modern:active,
    button.btn-modern.btn-warning-modern:active {
        transform: translateY(1px) !important;
    }

    .btn-modern.btn-warning-modern::before,
    button.btn-modern.btn-warning-modern::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: -100% !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent) !important;
        transition: left 0.5s !important;
    }

    .btn-modern.btn-warning-modern:hover::before,
    button.btn-modern.btn-warning-modern:hover::before {
        left: 100% !important;
    }

    .btn-modern.btn-warning-modern:hover i.bi-check-lg {
        transform: scale(1.1) !important;
    }

    /* Modal Footer Enhancements */
    .modal-footer-modern {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 1rem !important;
        padding: 1.5rem !important;
        background: #f8f9fa !important;
        border-top: 1px solid #dee2e6 !important;
    }

    /* Button Loading State */
    .btn-modern.loading {
        pointer-events: none !important;
        opacity: 0.8 !important;
    }

    .btn-modern.loading::after {
        content: '' !important;
        position: absolute !important;
        width: 16px !important;
        height: 16px !important;
        top: 50% !important;
        left: 50% !important;
        margin-left: -8px !important;
        margin-top: -8px !important;
        border: 2px solid transparent !important;
        border-top-color: currentColor !important;
        border-radius: 50% !important;
        animation: btn-loading-spin 1s linear infinite !important;
    }

    @keyframes btn-loading-spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Pulse Animation for Submit Button */
    .btn-modern.btn-warning-modern {
        animation: orange-pulse 2s infinite !important;
    }

    @keyframes orange-pulse {
        0% {
            box-shadow: 0 4px 12px rgba(245, 124, 0, 0.3);
        }

        50% {
            box-shadow: 0 4px 20px rgba(245, 124, 0, 0.5);
        }

        100% {
            box-shadow: 0 4px 12px rgba(245, 124, 0, 0.3);
        }
    }

    /* Responsive Modal Buttons */
    @media (max-width: 768px) {
        .modal-footer-modern {
            flex-direction: column !important;
            gap: 0.75rem !important;
            padding: 1rem !important;
        }

        .btn-modern {
            width: 100% !important;
            justify-content: center !important;
            padding: 0.6rem 1rem !important;
            font-size: 0.85rem !important;
        }
    }

    @media (max-width: 480px) {
        .btn-modern {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.8rem !important;
            min-width: 100px !important;
        }
    }

    /* Enhanced Icon Animations */
    .btn-modern i {
        transition: transform 0.3s ease !important;
    }

    .btn-modern:hover i {
        transform: scale(1.05) !important;
    }

    /* Special Effects */
    .btn-modern.btn-glow {
        box-shadow: 0 0 20px rgba(13, 110, 253, 0.5) !important;
    }

    .btn-modern.btn-shadow-lg {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .btn-modern.btn-shadow-lg:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
    }

    /* ===== FIX: CARD HEADER ALIGNMENT ===== */
    /* Header chỉ có text: không cần justify-content override */
    /* Header có button bên phải giữ justify-content-between */
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Include sidebar -->
        <?php include_once __DIR__ . '/includes/sidebar_lanh_dao.php'; ?>

        <div class="col-md-10 p-4">
            <!-- Welcome Card -->
            <div class="card mb-4 fade-in-up border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2 text-dark">
                                Quản lý <strong>nội dung đồ án</strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Cấu hình và quản lý toàn bộ nội dung trang thông báo đồ án.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#page-settings" role="tab">
                        Cài đặt trang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#co-so-nganh" role="tab">
                        Cơ sở ngành
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#chuyen-nganh" role="tab">
                        Chuyên ngành
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Page Settings Tab -->
                <div class="tab-pane fade show active fade-in-up" id="page-settings" role="tabpanel"
                    aria-labelledby="page-settings-tab">
                    <div class="card-modern">
                        <div class="card-header-modern d-flex align-items-center">
                            Cấu hình thông tin trang
                        </div>
                        <div class="card-body-modern">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_page">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-label-modern">Tiêu đề trang</label>
                                        <input type="text" class="form-control form-control-modern" name="page_title"
                                            value="<?= htmlspecialchars($pageContent['page_title'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label form-label-modern">Phụ đề</label>
                                        <input type="text" class="form-control form-control-modern" name="subtitle"
                                            value="<?= htmlspecialchars($pageContent['subtitle'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label form-label-modern">Badge ngày tháng</label>
                                        <input type="text" class="form-control form-control-modern" name="date_badge"
                                            value="<?= htmlspecialchars($pageContent['date_badge'] ?? '') ?>"
                                            placeholder="VD: Năm học 2024-2025">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label form-label-modern">Ngày bắt đầu</label>
                                        <input type="date" class="form-control form-control-modern" name="ngay_bat_dau"
                                            value="<?= $pageContent['ngay_bat_dau'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label form-label-modern">Ngày kết thúc</label>
                                        <input type="date" class="form-control form-control-modern" name="ngay_ket_thuc"
                                            value="<?= $pageContent['ngay_ket_thuc'] ?? '' ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-modern">Tiêu đề lưu ý</label>
                                    <input type="text" class="form-control form-control-modern" name="notice_tieu_de"
                                        value="<?= htmlspecialchars($notice['tieu_de'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-modern">Nội dung lưu ý</label>
                                    <textarea class="form-control form-control-modern" name="notice_noi_dung" rows="5"><?= htmlspecialchars($notice['noi_dung'] ?? '') ?></textarea>
                                    <div class="form-text">
                                        Có hỗ trợ HTML cơ bản: &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, &lt;br&gt;, &lt;p&gt;
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label form-label-modern">Trạng thái trang</label>
                                    <select class="form-control form-control-modern" name="trang_thai">
                                        <option value="mo" <?= ($pageContent['trang_thai'] ?? '') === 'mo' ? 'selected' : '' ?>>
                                            🟢 Mở - Hiển thị công khai
                                        </option>
                                        <option value="khoa" <?= ($pageContent['trang_thai'] ?? '') === 'khoa' ? 'selected' : '' ?>>
                                            🔒 Khóa - Ẩn khỏi công chúng
                                        </option>
                                    </select>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-modern btn-primary-modern btn-form-submit">
                                        Lưu cấu hình
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Cơ sở ngành Tab -->
                <div class="tab-pane fade fade-in-up" id="co-so-nganh" role="tabpanel"
                    aria-labelledby="co-so-nganh-tab">
                    <div class="card-modern">
                        <div class="card-header-modern d-flex justify-content-between align-items-center">
                            Timeline đồ án Cơ sở ngành</span>
                            <button class="btn btn-modern btn-success-modern" data-bs-toggle="modal"
                                data-bs-target="#addTimelineModal" data-loai="co_so_nganh">
                                Thêm mốc thời gian
                            </button>
                        </div>
                        <div class="card-body-modern">
                            <?php if (empty($timelineCoSoNganh)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">Chưa có mốc thời gian nào</h5>
                                    <p class="text-muted">Hãy thêm mốc thời gian đầu tiên cho đồ án cơ sở ngành</p>
                                </div>
                            <?php else: ?>
                                <div class="timeline-container">
                                    <?php foreach ($timelineCoSoNganh as $item): ?>
                                        <div class="timeline-item <?= $item['trang_thai'] === 'khoa' ? 'inactive' : '' ?>">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="timeline-date">
                                                    <?= $item['ngay'] ?>/<?= $item['thang'] ?>/<?= $item['nam'] ?>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <span
                                                        class="status-badge <?= $item['trang_thai'] === 'mo' ? 'status-active' : 'status-inactive' ?>">
                                                        <?= $item['trang_thai'] === 'mo' ? 'Hoạt động' : 'Tạm khóa' ?>
                                                    </span>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-warning-modern" data-bs-toggle="modal"
                                                            data-bs-target="#editTimelineModal" data-id="<?= $item['id'] ?>"
                                                            data-loai="<?= $item['loai'] ?>"
                                                            data-thu_tu="<?= $item['thu_tu'] ?>"
                                                            data-tieu_de="<?= htmlspecialchars($item['tieu_de']) ?>"
                                                            data-noi_dung="<?= htmlspecialchars($item['noi_dung']) ?>"
                                                            data-ngay="<?= $item['ngay'] ?>" data-thang="<?= $item['thang'] ?>"
                                                            data-nam="<?= $item['nam'] ?>"
                                                            data-yeu_cau="<?= htmlspecialchars($item['yeu_cau']) ?>"
                                                            data-trang_thai="<?= $item['trang_thai'] ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <form method="POST" class="d-inline"
                                                            onsubmit="return confirm('Bạn có chắc muốn xóa mốc này?')">
                                                            <input type="hidden" name="action" value="delete_timeline">
                                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger-modern">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="toggle_timeline">
                                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-light-modern"
                                                                title="<?= $item['trang_thai'] === 'mo' ? 'Khóa' : 'Mở' ?>">
                                                                <i
                                                                    class="bi bi-<?= $item['trang_thai'] === 'mo' ? 'lock' : 'unlock' ?>"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <h4 class="timeline-title"><?= htmlspecialchars($item['tieu_de']) ?></h4>
                                            <div class="timeline-content"><?= nl2br(htmlspecialchars($item['noi_dung'])) ?>
                                            </div>

                                            <?php if (!empty($item['yeu_cau'])): ?>
                                                <div class="timeline-requirements">
                                                    <h6>Yêu cầu cần hoàn thành:</h6>
                                                    <ul>
                                                        <?php foreach (explode('|', $item['yeu_cau']) as $yeuCau): ?>
                                                            <?php if (trim($yeuCau)): ?>
                                                                <li><?= htmlspecialchars(trim($yeuCau)) ?></li>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Chuyên ngành Tab -->
                <div class="tab-pane fade fade-in-up" id="chuyen-nganh" role="tabpanel"
                    aria-labelledby="chuyen-nganh-tab">
                    <div class="card-modern">
                        <div class="card-header-modern d-flex justify-content-between align-items-center">
                            <span>Timeline đồ án Chuyên ngành</span>
                            <button class="btn btn-modern btn-success-modern" data-bs-toggle="modal"
                                data-bs-target="#addTimelineModal" data-loai="chuyen_nganh">
                                Thêm mốc thời gian
                            </button>
                        </div>
                        <div class="card-body-modern">
                            <?php if (empty($timelineChuyenNganh)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">Chưa có mốc thời gian nào</h5>
                                    <p class="text-muted">Hãy thêm mốc thời gian đầu tiên cho đồ án chuyên ngành</p>
                                </div>
                            <?php else: ?>
                                <div class="timeline-container">
                                    <?php foreach ($timelineChuyenNganh as $item): ?>
                                        <div class="timeline-item <?= $item['trang_thai'] === 'khoa' ? 'inactive' : '' ?>">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="timeline-date">
                                                    <?= $item['ngay'] ?>/<?= $item['thang'] ?>/<?= $item['nam'] ?>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <span
                                                        class="status-badge <?= $item['trang_thai'] === 'mo' ? 'status-active' : 'status-inactive' ?>">
                                                        <?= $item['trang_thai'] === 'mo' ? 'Hoạt động' : 'Tạm khóa' ?>
                                                    </span>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-warning-modern" data-bs-toggle="modal"
                                                            data-bs-target="#editTimelineModal" data-id="<?= $item['id'] ?>"
                                                            data-loai="<?= $item['loai'] ?>"
                                                            data-thu_tu="<?= $item['thu_tu'] ?>"
                                                            data-tieu_de="<?= htmlspecialchars($item['tieu_de']) ?>"
                                                            data-noi_dung="<?= htmlspecialchars($item['noi_dung']) ?>"
                                                            data-ngay="<?= $item['ngay'] ?>" data-thang="<?= $item['thang'] ?>"
                                                            data-nam="<?= $item['nam'] ?>"
                                                            data-yeu_cau="<?= htmlspecialchars($item['yeu_cau']) ?>"
                                                            data-trang_thai="<?= $item['trang_thai'] ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <form method="POST" class="d-inline"
                                                            onsubmit="return confirm('Bạn có chắc muốn xóa mốc này?')">
                                                            <input type="hidden" name="action" value="delete_timeline">
                                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger-modern">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="toggle_timeline">
                                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-light-modern"
                                                                title="<?= $item['trang_thai'] === 'mo' ? 'Khóa' : 'Mở' ?>">
                                                                <i
                                                                    class="bi bi-<?= $item['trang_thai'] === 'mo' ? 'lock' : 'unlock' ?>"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <h4 class="timeline-title"><?= htmlspecialchars($item['tieu_de']) ?></h4>
                                            <div class="timeline-content"><?= nl2br(htmlspecialchars($item['noi_dung'])) ?>
                                            </div>

                                            <?php if (!empty($item['yeu_cau'])): ?>
                                                <div class="timeline-requirements">
                                                    <h6>Yêu cầu cần hoàn thành:</h6>
                                                    <ul>
                                                        <?php foreach (explode('|', $item['yeu_cau']) as $yeuCau): ?>
                                                            <?php if (trim($yeuCau)): ?>
                                                                <li><?= htmlspecialchars(trim($yeuCau)) ?></li>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <!-- Add Timeline Modal -->
    <div class="modal fade" id="addTimelineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        Thêm mốc thời gian mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body modal-body-modern">
                        <input type="hidden" name="action" value="add_timeline">
                        <input type="hidden" name="loai" id="add_loai">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-modern">Thứ tự</label>
                                <input type="number" class="form-control form-control-modern" name="thu_tu" min="1"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-modern">Tiêu đề</label>
                                <input type="text" class="form-control form-control-modern" name="tieu_de" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label form-label-modern">Nội dung mô tả</label>
                            <textarea class="form-control form-control-modern" name="noi_dung" rows="4"
                                required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-modern">Ngày</label>
                                <select class="form-control form-control-modern" name="ngay" required>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-modern">Tháng</label>
                                <select class="form-control form-control-modern" name="thang" required>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>">Tháng <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-modern">Năm</label>
                                <select class="form-control form-control-modern" name="nam" required>
                                    <?php for ($i = date('Y'); $i <= date('Y') + 2; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label form-label-modern">Yêu cầu cần hoàn thành</label>
                            <textarea class="form-control form-control-modern" name="yeu_cau[]" rows="6"
                                placeholder="Nhập mỗi yêu cầu trên một dòng..."></textarea>
                            <div class="form-text">
                                Mỗi yêu cầu trên một dòng riêng biệt
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-modern">
                        <button type="button" class="btn btn-modern btn-light-modern" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-2"></i>Hủy
                        </button>
                        <button type="submit" class="btn btn-modern btn-success-modern">
                            <i class="bi bi-check-lg me-2"></i>Thêm mốc thời gian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Timeline Modal -->
    <div class="modal fade" id="editTimelineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        Chỉnh sửa mốc thời gian
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body modal-body-modern">
                        <input type="hidden" name="action" value="update_timeline">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="loai" id="edit_loai">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-modern">Thứ tự</label>
                                <input type="number" class="form-control form-control-modern" name="thu_tu"
                                    id="edit_thu_tu" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-modern">Tiêu đề</label>
                                <input type="text" class="form-control form-control-modern" name="tieu_de"
                                    id="edit_tieu_de" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label form-label-modern">Nội dung mô tả</label>
                            <textarea class="form-control form-control-modern" name="noi_dung" id="edit_noi_dung"
                                rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-modern">Ngày</label>
                                <select class="form-control form-control-modern" name="ngay" id="edit_ngay" required>
                                    <option value="">Chọn ngày</option>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-modern">Tháng</label>
                                <select class="form-control form-control-modern" name="thang" id="edit_thang" required>
                                    <option value="">Chọn tháng</option>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>">Tháng <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-modern">Năm</label>
                                <select class="form-control form-control-modern" name="nam" id="edit_nam" required>
                                    <option value="">Chọn năm</option>
                                    <?php for ($i = date('Y'); $i <= date('Y') + 2; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label form-label-modern">Yêu cầu cần hoàn thành</label>
                            <textarea class="form-control form-control-modern" name="yeu_cau[]" id="edit_yeu_cau"
                                rows="4" placeholder="Nhập mỗi yêu cầu trên một dòng..."></textarea>
                            <small class="form-text text-muted">Mỗi yêu cầu trên một dòng riêng biệt</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label form-label-modern">Trạng thái</label>
                            <select class="form-control form-control-modern" name="trang_thai" id="edit_trang_thai">
                                <option value="mo">🟢 Hoạt động</option>
                                <option value="khoa">🔒 Tạm khóa</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-modern">
                        <button type="button" class="btn btn-modern btn-light-modern" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-2"></i>Hủy
                        </button>
                        <button type="submit" class="btn btn-modern btn-warning-modern">
                            <i class="bi bi-check-lg me-2"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Handle add modal - set loai
        document.querySelectorAll('[data-bs-target="#addTimelineModal"]').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('add_loai').value = this.dataset.loai;
            });
        });

        // Handle edit modal - populate data
        document.querySelectorAll('[data-bs-target="#editTimelineModal"]').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_loai').value = this.dataset.loai;
                document.getElementById('edit_thu_tu').value = this.dataset.thu_tu;
                document.getElementById('edit_tieu_de').value = this.dataset.tieu_de;
                document.getElementById('edit_noi_dung').value = this.dataset.noi_dung;
                document.getElementById('edit_ngay').value = this.dataset.ngay;
                document.getElementById('edit_thang').value = this.dataset.thang;
                document.getElementById('edit_nam').value = this.dataset.nam;
                document.getElementById('edit_yeu_cau').value = this.dataset.yeu_cau ? this.dataset.yeu_cau.replace(/\|/g, '\n') : '';
                document.getElementById('edit_trang_thai').value = this.dataset.trang_thai;
            });
        });

        // Initialize Bootstrap tabs with modern animations
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM loaded, initializing modern tabs...');

            // Check if Bootstrap is loaded
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap is not loaded!');
                return;
            }

            console.log('Bootstrap version:', bootstrap.Tooltip.VERSION);

            // Initialize all tab triggers
            var triggerTabList = [].slice.call(document.querySelectorAll('a[data-bs-toggle="tab"]'));
            console.log('Found', triggerTabList.length, 'tab triggers');

            triggerTabList.forEach(function (triggerEl, index) {
                console.log('Initializing tab', index, ':', triggerEl.getAttribute('href'));

                triggerEl.addEventListener('click', function (event) {
                    event.preventDefault();
                    console.log('Tab clicked:', this.getAttribute('href'));

                    // Remove active class from all tabs
                    document.querySelectorAll('.nav-link').forEach(function (tab) {
                        tab.classList.remove('active');
                        tab.setAttribute('aria-selected', 'false');
                    });

                    // Remove active class from all tab panes
                    document.querySelectorAll('.tab-pane').forEach(function (pane) {
                        pane.classList.remove('show', 'active');
                    });

                    // Add active class to clicked tab
                    this.classList.add('active');
                    this.setAttribute('aria-selected', 'true');

                    // Show target tab pane with animation
                    var targetId = this.getAttribute('href');
                    var targetPane = document.querySelector(targetId);
                    if (targetPane) {
                        setTimeout(() => {
                            targetPane.classList.add('show', 'active', 'fade-in-up');
                        }, 50);
                        console.log('Activated tab pane:', targetId);
                    } else {
                        console.error('Target pane not found:', targetId);
                    }
                });
            });

            // Add smooth scrolling for better UX
            document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(function (tab) {
                tab.addEventListener('shown.bs.tab', function (event) {
                    // Smooth scroll to top of content
                    document.querySelector('.main-content').scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            });

            console.log('Modern tabs initialization completed');
        });

        // Add hover effects for timeline items
        document.querySelectorAll('.timeline-item').forEach(function (item) {
            item.addEventListener('mouseenter', function () {
                this.style.transform = 'translateX(8px)';
            });

            item.addEventListener('mouseleave', function () {
                this.style.transform = 'translateX(4px)';
            });
        });

        // ===== TAB PERSISTENCE FOR TOGGLE ACTIONS =====

        document.addEventListener('DOMContentLoaded', function () {
            // Get current tab from URL or default to first tab
            const urlParams = new URLSearchParams(window.location.search);
            const currentTab = urlParams.get('tab') || 'page-settings';

            // Xử lý màu sắc cho tab Chuyên ngành khi load trang
            function applyChuyenNganhColor() {
                const chuyenNganhTab = document.querySelector('.nav-link[href="#chuyen-nganh"]');
                if (chuyenNganhTab && chuyenNganhTab.classList.contains('active')) {
                    chuyenNganhTab.style.background = 'linear-gradient(135deg, #155724 0%, #1e7e34 100%)';
                    chuyenNganhTab.style.color = 'white';
                    chuyenNganhTab.style.boxShadow = '0 4px 12px rgba(21, 87, 36, 0.4)';
                }
            }

            // Apply color on page load
            setTimeout(applyChuyenNganhColor, 100);

            // Activate the correct tab
            const tabElement = document.querySelector(`a[href="#${currentTab}"]`);
            const tabContent = document.querySelector(`#${currentTab}`);

            if (tabElement && tabContent) {
                // Remove active from all tabs
                document.querySelectorAll('.nav-link').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active', 'show');
                });

                // Activate current tab
                tabElement.classList.add('active');
                tabContent.classList.add('active', 'show');
            }

            // Add current tab info to all toggle and delete forms
            document.querySelectorAll('form').forEach(form => {
                const action = form.querySelector('input[name="action"]');
                if (action && (action.value === 'toggle_timeline' || action.value === 'delete_timeline')) {
                    // Remove existing current_tab input if any
                    const existingInput = form.querySelector('input[name="current_tab"]');
                    if (existingInput) {
                        existingInput.remove();
                    }

                    // Add current tab input
                    const tabInput = document.createElement('input');
                    tabInput.type = 'hidden';
                    tabInput.name = 'current_tab';
                    tabInput.value = currentTab;
                    form.appendChild(tabInput);
                }
            });

            // Update tab info when user clicks on tabs
            document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('click', function () {
                    const targetTab = this.getAttribute('href').substring(1); // Remove #

                    // Xử lý màu sắc cho tab Chuyên ngành
                    if (this.getAttribute('href') === '#chuyen-nganh') {
                        // Xóa class active khỏi tất cả tab
                        document.querySelectorAll('.nav-link').forEach(t => t.classList.remove('active'));
                        // Thêm class active cho tab hiện tại
                        this.classList.add('active');
                        // Force apply dark green color
                        this.style.background = 'linear-gradient(135deg, #155724 0%, #1e7e34 100%)';
                        this.style.color = 'white';
                        this.style.boxShadow = '0 4px 12px rgba(21, 87, 36, 0.4)';
                    }

                    // Update all toggle and delete forms with new tab
                    document.querySelectorAll('form').forEach(form => {
                        const action = form.querySelector('input[name="action"]');
                        if (action && (action.value === 'toggle_timeline' || action.value === 'delete_timeline')) {
                            const tabInput = form.querySelector('input[name="current_tab"]');
                            if (tabInput) {
                                tabInput.value = targetTab;
                            }
                        }
                    });
                });
            });

            console.log('Tab persistence initialized for current tab:', currentTab);
        });
    </script>

    <?php include_once __DIR__ . '/includes/modal_quan_ly_tai_khoan.php'; ?>

    </body>

    </html>