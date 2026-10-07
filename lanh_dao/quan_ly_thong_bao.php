<?php
/**
 * QUẢN LÝ THÔNG BÁO CÔNG KHAI - GIAO DIỆN MỚI
 * Trang để lãnh đạo quản lý thông báo hiển thị ở trang chủ
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$pageTitle = 'Quản lý thông báo công khai';

$lanhDaoModel = new LanhDaoModel();
$thongBaoModel = new ThongBaoModel();

$lanhDao = $lanhDaoModel->getByNguoiDungId($user['id']);

// Xử lý form thêm/sửa thông báo
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $tieuDe = trim($_POST['tieu_de'] ?? '');
            $noiDung = trim($_POST['noi_dung'] ?? '');
            $loai = $_POST['loai'] ?? 'info';
            $link = trim($_POST['link'] ?? 'thong_bao.php');
            $loaiThongBao = 'thong_bao';
            $ngayBatDau = !empty($_POST['ngay_bat_dau']) ? $_POST['ngay_bat_dau'] : null;
            $ngayKetThuc = !empty($_POST['ngay_ket_thuc']) ? $_POST['ngay_ket_thuc'] : null;
            $trangThai = $_POST['trang_thai'] ?? 'mo';

            if (empty($tieuDe)) {
                $error = 'Vui lòng nhập tiêu đề thông báo';
            } else {
                $thongBaoModel->taoThongBaoCongKhai($tieuDe, $noiDung, $loai, $link, $loaiThongBao, $ngayBatDau, $ngayKetThuc, $trangThai);
                $message = 'Thêm thông báo thành công';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int) $_POST['id'];
            if ($id > 0) {
                $thongBaoModel->delete($id);
                $message = 'Xóa thông báo thành công';
            }
        } elseif ($_POST['action'] === 'toggle_status') {
            $id = (int) $_POST['id'];
            if ($id > 0) {
                $thongBaoModel->toggleTrangThai($id);
                $message = 'Cập nhật trạng thái thành công';
            }
        } elseif ($_POST['action'] === 'update') {
            $id = (int) $_POST['id'];
            $tieuDe = trim($_POST['tieu_de'] ?? '');
            $noiDung = trim($_POST['noi_dung'] ?? '');
            $loai = $_POST['loai'] ?? 'info';
            $link = trim($_POST['link'] ?? 'thong_bao.php');
            $loaiThongBao = 'thong_bao';
            $ngayBatDau = !empty($_POST['ngay_bat_dau']) ? $_POST['ngay_bat_dau'] : null;
            $ngayKetThuc = !empty($_POST['ngay_ket_thuc']) ? $_POST['ngay_ket_thuc'] : null;
            $trangThai = $_POST['trang_thai'] ?? 'mo';

            if ($id > 0 && !empty($tieuDe)) {
                $thongBaoModel->capNhatThongBao($id, $tieuDe, $noiDung, $loai, $link, $loaiThongBao, $ngayBatDau, $ngayKetThuc, $trangThai);
                $message = 'Cập nhật thông báo thành công';
            }
        }
    }
}

// Lấy danh sách thông báo (TẤT CẢ - không lọc theo ngày)
$dsThongBao = $thongBaoModel->getAllThongBaoCongKhai(50);

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

    /* ===== CUSTOM BUTTON STYLES FOR QUAN LY THONG BAO ===== */

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
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    .btn-primary-modern i,
    .btn-primary-modern:hover i {
        color: #ffffff !important;
    }

    .btn-primary-modern:hover {
        background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
        color: #ffffff !important;
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
        background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
    }

    .btn-success-modern i,
    .btn-success-modern:hover i {
        color: #ffffff !important;
    }

    .btn-success-modern:hover {
        background: linear-gradient(135deg, #157347 0%, #146c43 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(25, 135, 84, 0.4);
        color: #ffffff !important;
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
        white-space: nowrap;
        min-width: 140px;
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

    /* Light Modern Button (Nút Hủy) */
    .btn-light-modern {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #495057;
        border: 1.5px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-light-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.05), transparent);
        transition: left 0.5s ease;
    }

    .btn-light-modern:hover::before {
        left: 100%;
    }

    .btn-light-modern:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        color: #343a40;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        border-color: #ced4da;
    }

    .btn-light-modern:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-light-modern i {
        transition: transform 0.3s ease;
    }

    .btn-light-modern:hover i {
        transform: scale(1.1);
    }

    /* Cancel Modern Button (Nút Hủy Đẹp) */
    .btn-cancel-modern {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: #ffffff;
        border: 1.5px solid #5a6268;
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.25);
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 600;
    }

    .btn-cancel-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
        transition: left 0.5s ease;
    }

    .btn-cancel-modern:hover::before {
        left: 100%;
    }

    .btn-cancel-modern:hover {
        background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.35);
        border-color: #495057;
    }

    .btn-cancel-modern:active {
        transform: translateY(0);
        box-shadow: 0 4px 10px rgba(108, 117, 125, 0.25);
    }

    .btn-cancel-modern i {
        transition: transform 0.3s ease;
        font-size: 1rem;
    }

    .btn-cancel-modern:hover i {
        transform: rotate(-90deg) scale(1.1);
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

    /* Modern Card Styles */
    .card-modern {
        border: 1px solid #dee2e6;
        border-radius: 0.75rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .card-modern:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .card-header-modern {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: #495057;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-body-modern {
        padding: 1.5rem;
    }

    /* Modern Alert Styles */
    .alert-modern {
        border: none;
        border-radius: 0.5rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        animation: alertSlideIn 0.5s ease-out forwards;
    }

    .alert-modern.alert-success {
        background: linear-gradient(135deg, #d1e7dd 0%, #badbcc 100%);
        color: #0f5132;
        border-left: 4px solid #198754;
    }

    .alert-modern.alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%);
        color: #842029;
        border-left: 4px solid #dc3545;
    }

    .alert-modern.alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffecb5 100%);
        color: #664d03;
        border-left: 4px solid #ffc107;
    }

    .alert-modern.alert-info {
        background: linear-gradient(135deg, #cff4fc 0%, #b6effb 100%);
        color: #055160;
        border-left: 4px solid #0dcaf0;
    }

    /* Modern Form Styles */
    .form-control-modern {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        outline: none;
    }

    .form-label-modern {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
        display: block;
    }

    /* Modern Modal Styles */
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
        border-radius: 0.75rem 0.75rem 0 0;
    }

    .modal-body-modern {
        padding: 1.75rem 1.75rem 2.25rem 1.75rem;
        overflow: visible !important;
    }

    .modal-footer-modern {
        border-top: 1px solid #dee2e6;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 0 0 0.75rem 0.75rem;
    }

    /* Enhanced Button Close Styles */
    .btn-close {
        box-sizing: content-box;
        width: 1em;
        height: 1em;
        padding: 0.25em 0.25em;
        color: #000;
        background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='m.235.739l15.026 15.026-1.061 1.061L-.826 1.8z'/%3e%3cpath d='M15.261.739L.235 15.765l1.061 1.061L16.322 1.8z'/%3e%3c/svg%3e") center/1em auto no-repeat;
        border: 0;
        border-radius: 0.375rem;
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .btn-close:hover {
        color: #000;
        text-decoration: none;
        opacity: 0.75;
        transform: scale(1.1);
    }

    .btn-close:focus {
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        opacity: 1;
    }

    .btn-close:disabled,
    .btn-close.disabled {
        pointer-events: none;
        user-select: none;
        opacity: 0.25;
    }

    .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .btn-close-white:hover {
        filter: invert(1) grayscale(100%) brightness(200%);
        transform: scale(1.1);
    }

    /* Enhanced Link Buttons */
    a.btn-modern {
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    a.btn-modern:hover {
        text-decoration: none;
    }

    /* Status Badge Enhancements */
    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        transition: all 0.3s ease;
    }

    .status-active {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);
    }

    .status-active:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.4);
    }

    .status-inactive {
        background: linear-gradient(135deg, #6c757d 0%, #5c636a 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }

    .status-inactive:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
    }

    /* Notification Toast Buttons */
    .notification-toast .btn-close {
        background: none;
        border: none;
        font-size: 1.2rem;
        opacity: 0.7;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0.25rem;
        border-radius: 0.25rem;
    }

    .notification-toast .btn-close:hover {
        opacity: 1;
        background: rgba(0, 0, 0, 0.1);
        transform: scale(1.1);
    }

    /* Badge Buttons */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.375rem;
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: translateY(-1px);
    }

    .badge.bg-success {
        background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;
        box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);
    }

    .badge.bg-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%) !important;
        color: #000 !important;
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

    .badge.bg-danger {
        background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%) !important;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    .badge.bg-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5c636a 100%) !important;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }

    /* Small Button Variants */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
    }

    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 1.25rem;
        border-radius: 0.5rem;
    }

    /* Preview Button Specific Styling */
    .btn-preview {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 0.375rem;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 6px rgba(23, 162, 184, 0.3);
        text-decoration: none;
    }

    .btn-preview:hover {
        background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-preview:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.25);
    }

    .btn-preview::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-preview:hover::before {
        left: 100%;
    }

    /* Enhanced Light Modern Button for Preview Links */
    a.btn-light-modern {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #495057;
        border: 1px solid #dee2e6;
        padding: 0.4rem 0.8rem;
        border-radius: 0.375rem;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    a.btn-light-modern:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        color: #495057;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        text-decoration: none;
    }

    a.btn-light-modern:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    }

    a.btn-light-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.05), transparent);
        transition: left 0.5s;
    }

    a.btn-light-modern:hover::before {
        left: 100%;
    }

    /* Icon styling within preview buttons */
    a.btn-light-modern i.bi-box-arrow-up-right {
        font-size: 0.9rem;
        transition: transform 0.3s ease;
    }

    a.btn-light-modern:hover i.bi-box-arrow-up-right {
        transform: translateX(2px) translateY(-2px);
    }

    /* Alternative Info Button Style for Preview */
    .btn-info-modern {
        background: linear-gradient(135deg, #0dcaf0 0%, #31d2f2 100%);
        color: #000;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 0.375rem;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 6px rgba(13, 202, 240, 0.3);
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .btn-info-modern:hover {
        background: linear-gradient(135deg, #31d2f2 0%, #25cff2 100%);
        color: #000;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 202, 240, 0.4);
        text-decoration: none;
    }

    .btn-info-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
        transition: left 0.5s;
    }

    .btn-info-modern:hover::before {
        left: 100%;
    }

    /* Custom styles for notification management */
    .notification-item {
        border-left: 3px solid #0d6efd;
        padding: 1rem;
        margin-bottom: 1rem;
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }

    .notification-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .notification-item.type-success {
        border-left-color: #198754;
    }

    .notification-item.type-warning {
        border-left-color: #ffc107;
    }

    .notification-item.type-danger {
        border-left-color: #dc3545;
    }

    .notification-item.inactive {
        opacity: 0.6;
        border-left-color: #6c757d;
    }

    .notification-title {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .notification-content {
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .notification-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid #dee2e6;
        font-size: 0.875rem;
        color: #6c757d;
    }

    .notification-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .type-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .type-badge.type-info {
        background-color: #cff4fc;
        color: #055160;
    }

    .type-badge.type-success {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .type-badge.type-warning {
        background-color: #fff3cd;
        color: #664d03;
    }

    .type-badge.type-danger {
        background-color: #f8d7da;
        color: #842029;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    /* Empty state styling */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #212529;
        opacity: 0.85;
    }

    .empty-state h3 {
        color: #212529;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #495057;
        font-weight: 500;
        margin-bottom: 2rem;
    }
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
                                Quản lý <strong>thông báo công khai</strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Tạo và quản lý thông báo công khai hiển thị trên trang chủ.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($message): ?>
                <div class="alert alert-modern alert-success fade-in-up">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-modern alert-danger fade-in-up">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Action Bar -->
            <div class="card-modern mb-4 fade-in-up">
                <div class="card-body-modern">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Danh sách thông báo
                        </h5>
                        <button class="btn btn-modern btn-success-modern" data-bs-toggle="modal"
                            data-bs-target="#addNotificationModal">
                            Thêm thông báo mới
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="fade-in-up">
                <?php if (empty($dsThongBao)): ?>
                    <div class="card-modern">
                        <div class="card-body-modern">
                            <div class="empty-state">
                                <i class="bi bi-megaphone text-dark"></i>
                                <h3>Chưa có thông báo nào</h3>
                                <p>Hãy tạo thông báo đầu tiên để hiển thị trên trang chủ</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($dsThongBao as $thongBao): ?>
                        <div
                            class="notification-item type-<?= $thongBao['loai'] ?> <?= $thongBao['trang_thai'] === 'khoa' ? 'inactive' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex gap-2">
                                    <span class="text-muted small me-1">Loại thông báo:</span>
                                    <span class="type-badge type-<?= $thongBao['loai'] ?>">
                                        <?php
                                        $typeLabels = [
                                            'info' => 'Thông tin',
                                            'success' => 'Thành công',
                                            'warning' => 'Cảnh báo',
                                            'danger' => 'Quan trọng'
                                        ];
                                        echo $typeLabels[$thongBao['loai']] ?? 'Thông tin';
                                        ?>
                                    </span>

                                </div>
                                <div class="notification-actions">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $thongBao['id'] ?>">
                                        <button type="submit" class="btn-toggle-timeline"
                                            title="<?= $thongBao['trang_thai'] === 'mo' ? 'Ẩn thông báo' : 'Hiển thị thông báo' ?>">
                                            <i class="bi bi-<?= $thongBao['trang_thai'] === 'mo' ? 'eye-slash' : 'eye' ?>"></i>
                                        </button>
                                    </form>
                                    <button class="btn-edit-timeline" data-bs-toggle="modal"
                                        data-bs-target="#editNotificationModal" data-id="<?= $thongBao['id'] ?>"
                                        data-tieu_de="<?= htmlspecialchars($thongBao['tieu_de']) ?>"
                                        data-noi_dung="<?= htmlspecialchars($thongBao['noi_dung']) ?>"
                                        data-loai="<?= $thongBao['loai'] ?>"
                                        data-link="<?= htmlspecialchars($thongBao['link']) ?>"
                                        data-ngay_bat_dau="<?= $thongBao['ngay_bat_dau'] ?>"
                                        data-ngay_ket_thuc="<?= $thongBao['ngay_ket_thuc'] ?>"
                                        data-trang_thai="<?= $thongBao['trang_thai'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" class="d-inline"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $thongBao['id'] ?>">
                                        <button type="submit" class="btn-delete-timeline">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <h4 class="notification-title"><?= htmlspecialchars($thongBao['tieu_de']) ?></h4>
                            <div class="notification-content"><?= nl2br(htmlspecialchars($thongBao['noi_dung'])) ?></div>

                            <div class="notification-meta">
                                <div class="notification-date">
                                    Ngày tạo: <?= date('d/m/Y', strtotime($thongBao['created_at'])) ?>
                                </div>
                                <?php if ($thongBao['ngay_ket_thuc']): ?>
                                <div class="notification-date">
                                    Ngày kết thúc: <?= date('d/m/Y', strtotime($thongBao['ngay_ket_thuc'])) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Add Notification Modal -->
    <div class="modal fade" id="addNotificationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        Thêm thông báo mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body modal-body-modern">
                        <input type="hidden" name="action" value="add">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label form-label-modern">Tiêu đề thông báo</label>
                                <input type="text" class="form-control form-control-modern" name="tieu_de" required
                                    placeholder="Nhập tiêu đề thông báo...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-modern">Loại thông báo</label>
                                <select class="form-control form-control-modern" name="loai" required>
                                    <option value="info">📢 Thông tin</option>
                                    <option value="success">✅ Thành công</option>
                                    <option value="warning">⚠️ Cảnh báo</option>
                                    <option value="danger">🚨 Quan trọng</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label form-label-modern">Nội dung thông báo</label>
                            <textarea class="form-control form-control-modern" name="noi_dung" rows="6" required
                                placeholder="Nhập nội dung chi tiết của thông báo..."></textarea>
                            <div class="form-text">
                                Hỗ trợ xuống dòng tự động. Nội dung sẽ hiển thị trên trang chủ.
                            </div>
                        </div>

                        <input type="hidden" name="link" value="thong_bao.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-modern">Ngày bắt đầu</label>
                                <input type="date" class="form-control form-control-modern" name="ngay_bat_dau">
                                <div class="form-text">Để trống sẽ hiển thị ngay</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-modern">Ngày kết thúc</label>
                                <input type="date" class="form-control form-control-modern" name="ngay_ket_thuc">
                                <div class="form-text">Để trống sẽ hiển thị vĩnh viễn</div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label form-label-modern">Trạng thái hiển thị</label>
                            <select class="form-select form-control-modern" name="trang_thai">
                                <option value="mo">🟢 Hiển thị công khai</option>
                                <option value="khoa">🔒 Ẩn (nháp)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-modern">
                        <button type="button" class="btn btn-modern btn-cancel-modern" data-bs-dismiss="modal">
                            Hủy
                        </button>
                        <button type="submit" class="btn btn-modern btn-success-modern">
                            Tạo thông báo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Notification Modal -->
    <div class="modal fade" id="editNotificationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Chỉnh sửa thông báo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body modal-body-modern">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label form-label-modern">Tiêu đề thông báo</label>
                                <input type="text" class="form-control form-control-modern" name="tieu_de"
                                    id="edit_tieu_de" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-modern">Loại thông báo</label>
                                <select class="form-control form-control-modern" name="loai" id="edit_loai" required>
                                    <option value="info">📢 Thông tin</option>
                                    <option value="success">✅ Thành công</option>
                                    <option value="warning">⚠️ Cảnh báo</option>
                                    <option value="danger">🚨 Quan trọng</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label form-label-modern">Nội dung thông báo</label>
                            <textarea class="form-control form-control-modern" name="noi_dung" id="edit_noi_dung"
                                rows="6" required></textarea>
                        </div>

                        <input type="hidden" name="link" value="thong_bao.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-modern">Ngày bắt đầu</label>
                                <input type="date" class="form-control form-control-modern" name="ngay_bat_dau"
                                    id="edit_ngay_bat_dau">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-modern">Ngày kết thúc</label>
                                <input type="date" class="form-control form-control-modern" name="ngay_ket_thuc"
                                    id="edit_ngay_ket_thuc">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label form-label-modern">Trạng thái hiển thị</label>
                            <select class="form-select form-control-modern" name="trang_thai" id="edit_trang_thai">
                                <option value="mo">🟢 Hiển thị công khai</option>
                                <option value="khoa">🔒 Ẩn (nháp)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-modern">
                        <button type="button" class="btn btn-modern btn-cancel-modern" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Hủy
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
        // Handle edit modal - populate data
        document.querySelectorAll('[data-bs-target="#editNotificationModal"]').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_tieu_de').value = this.dataset.tieu_de;
                document.getElementById('edit_noi_dung').value = this.dataset.noi_dung;
                document.getElementById('edit_loai').value = this.dataset.loai;

                document.getElementById('edit_ngay_bat_dau').value = this.dataset.ngay_bat_dau;
                document.getElementById('edit_ngay_ket_thuc').value = this.dataset.ngay_ket_thuc;
                document.getElementById('edit_trang_thai').value = this.dataset.trang_thai;
            });
        });

        // Initialize modern UI
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Modern notification management UI loaded');

            // Add loading states for form submissions
            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.dataset.noLoading) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Đang xử lý...';
                        submitBtn.disabled = true;

                        // Re-enable after 3 seconds as fallback
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 3000);
                    }
                });
            });

            // Add hover effects for notification items
            document.querySelectorAll('.notification-item').forEach(function (item) {
                item.addEventListener('mouseenter', function () {
                    this.style.transform = 'translateY(-4px)';
                });

                item.addEventListener('mouseleave', function () {
                    this.style.transform = 'translateY(-2px)';
                });
            });

            // Auto-hide alerts after 5 seconds
            document.querySelectorAll('.alert-modern').forEach(function (alert) {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });

            // Add smooth scrolling for better UX
            document.querySelectorAll('a[href^="#"]').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add keyboard shortcuts
            document.addEventListener('keydown', function (e) {
                // Ctrl/Cmd + N = New notification
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    const addModal = new bootstrap.Modal(document.getElementById('addNotificationModal'));
                    addModal.show();
                }

                // Escape = Close modals
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal.show').forEach(function (modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    });
                }
            });

            // Add tooltips for better UX
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            console.log('All modern UI features initialized');
        });

        // Add real-time preview & character counter for notification content
        document.getElementById('addNotificationModal')?.addEventListener('shown.bs.modal', function () {
            const titleInput = this.querySelector('input[name="tieu_de"]');
            const contentInput = this.querySelector('textarea[name="noi_dung"]');

            // Focus on title input
            titleInput?.focus();

            // Add character counter (ensure only 1 instance exists)
            if (contentInput) {
                let counter = contentInput.parentNode.querySelector('.char-counter');
                if (!counter) {
                    counter = document.createElement('div');
                    counter.className = 'form-text text-end char-counter';
                    counter.style.marginTop = '0.25rem';
                    contentInput.parentNode.appendChild(counter);

                    contentInput.addEventListener('input', function () {
                        const length = this.value.length;
                        counter.textContent = `${length} ký tự`;
                        counter.style.color = length > 500 ? '#dc2626' : '#64748b';
                    });
                }
                const length = contentInput.value.length;
                counter.textContent = `${length} ký tự`;
                counter.style.color = length > 500 ? '#dc2626' : '#64748b';
            }
        });

        // Add confirmation for destructive actions
        document.querySelectorAll('form[onsubmit*="confirm"]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const confirmText = this.getAttribute('onsubmit').match(/confirm\('([^']+)'\)/);
                if (confirmText && !confirm(confirmText[1])) {
                    e.preventDefault();
                }
            });
        });
    </script>

    <?php include_once __DIR__ . '/includes/modal_quan_ly_tai_khoan.php'; ?>

    </body>

    </html>