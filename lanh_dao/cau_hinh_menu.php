<?php
/**
 * CẤU HÌNH MENU - LÃNH ĐẠO - GIAO DIỆN MỚI
 * Trang quản lý cấu hình các link trong menu Sinh viên
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$pageTitle = 'Cấu hình Menu & Links';

$configFile = __DIR__ . '/../config/links_config.json';

// Xử lý cập nhật
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $links = [
        'ket_qua_thi' => $_POST['ket_qua_thi'] ?? '',
        'bieu_mau_sinh_vien' => $_POST['bieu_mau_sinh_vien'] ?? '',
        'bieu_mau_giang_vien' => $_POST['bieu_mau_giang_vien'] ?? ''
    ];

    if (file_put_contents($configFile, json_encode($links, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        $message = 'Cập nhật cấu hình thành công';
        $messageType = 'success';
    } else {
        $message = 'Lỗi khi lưu cấu hình!';
        $messageType = 'danger';
    }
}

// Đọc cấu hình hiện tại
$currentLinks = [];
if (file_exists($configFile)) {
    $currentLinks = json_decode(file_get_contents($configFile), true) ?? [];
}

// Gán giá trị mặc định nếu chưa có
$ketQuaThi = $currentLinks['ket_qua_thi'] ?? '';
$bieuMauSinhVien = $currentLinks['bieu_mau_sinh_vien'] ?? '';
$bieuMauGiangVien = $currentLinks['bieu_mau_giang_vien'] ?? '';

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

    /* ===== CUSTOM BUTTON STYLES FOR CAU HINH MENU ===== */

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
        padding: 2rem;
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

    /* Enhanced Light Modern Button Styling */
    a.btn-light-modern,
    button.btn-light-modern,
    .btn.btn-modern.btn-light-modern {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        color: #495057 !important;
        border: 1px solid #dee2e6 !important;
        padding: 0.6rem 1.2rem !important;
        border-radius: 0.5rem !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        text-decoration: none !important;
        position: relative !important;
        overflow: hidden !important;
        cursor: pointer !important;
        user-select: none !important;
        width: 100% !important;
        justify-content: flex-start !important;
    }

    a.btn-light-modern:hover,
    button.btn-light-modern:hover,
    .btn.btn-modern.btn-light-modern:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%) !important;
        color: #495057 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15) !important;
        text-decoration: none !important;
    }

    a.btn-light-modern:focus,
    button.btn-light-modern:focus,
    .btn.btn-modern.btn-light-modern:focus {
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25) !important;
    }

    a.btn-light-modern:active,
    button.btn-light-modern:active,
    .btn.btn-modern.btn-light-modern:active {
        transform: translateY(1px) !important;
    }

    a.btn-light-modern::before,
    button.btn-light-modern::before,
    .btn.btn-modern.btn-light-modern::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: -100% !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.05), transparent) !important;
        transition: left 0.5s !important;
    }

    a.btn-light-modern:hover::before,
    button.btn-light-modern:hover::before,
    .btn.btn-modern.btn-light-modern:hover::before {
        left: 100% !important;
    }

    /* Icon styling within light modern buttons */
    .btn-light-modern i {
        font-size: 1rem;
        transition: transform 0.3s ease;
    }

    .btn-light-modern:hover i.bi-arrow-left {
        transform: translateX(-2px);
    }

    .btn-light-modern:hover i.bi-arrow-clockwise {
        transform: rotate(180deg);
    }

    .btn-light-modern:hover i.bi-check-circle {
        transform: scale(1.1);
    }

    .btn-light-modern:hover i.bi-trash {
        transform: scale(1.1) rotate(5deg);
    }

    .btn-light-modern:hover i.bi-file-code {
        transform: translateY(-2px);
    }

    /* Enhanced Quick Actions Button Styling */
    .card-modern .card-body-modern .d-grid .btn {
        position: relative !important;
        overflow: hidden !important;
    }

    .card-modern .card-body-modern .d-grid .btn::after {
        content: '' !important;
        position: absolute !important;
        top: 50% !important;
        right: 1rem !important;
        width: 0 !important;
        height: 0 !important;
        border-left: 6px solid currentColor !important;
        border-top: 4px solid transparent !important;
        border-bottom: 4px solid transparent !important;
        transform: translateY(-50%) !important;
        opacity: 0 !important;
        transition: all 0.3s ease !important;
    }

    .card-modern .card-body-modern .d-grid .btn:hover::after {
        opacity: 0.6 !important;
        transform: translateY(-50%) translateX(4px) !important;
    }

    /* Special styling for quick action buttons */
    .card-modern .card-body-modern .d-grid button[onclick*="testAllLinks"] {
        border-left: 3px solid #198754 !important;
    }

    .card-modern .card-body-modern .d-grid button[onclick*="clearAllLinks"] {
        border-left: 3px solid #dc3545 !important;
    }

    .card-modern .card-body-modern .d-grid a[href*="links_config.json"] {
        border-left: 3px solid #0d6efd !important;
    }



    /* Form action buttons container */
    .d-flex.justify-content-between.align-items-center {
        padding: 1rem 0 !important;
        border-top: 1px solid #dee2e6 !important;
        margin-top: 1.5rem !important;
    }

    .d-flex.gap-2 .btn {
        margin-left: 0 !important;
    }

    /* ===== NEW ACTION BUTTONS STYLING ===== */

    /* Action Buttons Container */
    .action-buttons-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 0;
        border-top: 2px solid #e9ecef;
        margin-top: 2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin: 2rem -1.5rem -1.5rem -1.5rem;
    }

    .action-buttons-right {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    /* Base Action Button Style */
    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        text-decoration: none;
        cursor: pointer;
        user-select: none;
        position: relative;
        overflow: hidden;
        min-width: 140px;
        justify-content: center;
    }

    .btn-action:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    }

    .btn-action:active {
        transform: translateY(1px);
    }

    /* Back Button (Quay lại Dashboard) */
    .btn-back {
        background: linear-gradient(135deg, #6c757d 0%, #5c636a 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }

    .btn-back:hover {
        background: linear-gradient(135deg, #5c636a 0%, #565e64 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
        text-decoration: none;
    }

    .btn-back::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-back:hover::before {
        left: 100%;
    }

    .btn-back:hover i.bi-arrow-left {
        transform: translateX(-3px);
    }

    /* Reset Button (Đặt lại) */
    .btn-reset {
        background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
        color: #000;
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    }

    .btn-reset:hover {
        background: linear-gradient(135deg, #ffca2c 0%, #ffc720 100%);
        color: #000;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 193, 7, 0.4);
    }

    .btn-reset::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
        transition: left 0.5s;
    }

    .btn-reset:hover::before {
        left: 100%;
    }

    .btn-reset:hover i.bi-arrow-clockwise {
        transform: rotate(180deg);
    }

    /* Save Button (Lưu cấu hình) */
    .btn-save {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        position: relative;
    }

    .btn-save:hover {
        background: linear-gradient(135deg, #157347 0%, #146c43 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(25, 135, 84, 0.4);
    }

    .btn-save::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-save:hover::before {
        left: 100%;
    }

    .btn-save:hover i.bi-check-lg {
        transform: scale(1.1);
    }

    /* Loading State for Save Button */
    .btn-save.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    .btn-save.loading::after {
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

    /* Pulse Animation for Save Button */
    .btn-save {
        animation: save-pulse 2s infinite;
    }

    @keyframes save-pulse {
        0% {
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }

        50% {
            box-shadow: 0 4px 20px rgba(25, 135, 84, 0.5);
        }

        100% {
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }
    }

    /* Responsive Design for Action Buttons */
    @media (max-width: 768px) {
        .action-buttons-container {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
            padding: 1rem;
            margin: 1.5rem -1rem -1rem -1rem;
        }

        .action-buttons-right {
            justify-content: center;
            width: 100%;
        }

        .btn-action {
            flex: 1;
            min-width: 0;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
        }

        .btn-back {
            order: 3;
            margin-top: 0.5rem;
        }
    }

    @media (max-width: 480px) {
        .action-buttons-right {
            flex-direction: column;
            gap: 0.5rem;
        }

        .btn-action {
            width: 100%;
        }
    }

    /* Alternative Info Button Style */
    .btn-info-modern {
        background: linear-gradient(135deg, #0dcaf0 0%, #31d2f2 100%);
        color: #000;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(13, 202, 240, 0.3);
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .btn-info-modern:hover {
        background: linear-gradient(135deg, #31d2f2 0%, #25cff2 100%);
        color: #000;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 202, 240, 0.4);
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

    /* Secondary Modern Button Style */
    .btn-secondary-modern {
        background: linear-gradient(135deg, #6c757d 0%, #5c636a 100%);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .btn-secondary-modern:hover {
        background: linear-gradient(135deg, #5c636a 0%, #565e64 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
        text-decoration: none;
    }

    .btn-secondary-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-secondary-modern:hover::before {
        left: 100%;
    }

    /* Button Group Styling for Light Modern */
    .d-grid .btn-light-modern {
        margin-bottom: 0.5rem;
    }

    .d-grid .btn-light-modern:last-child {
        margin-bottom: 0;
    }

    /* Quick Actions Section Enhancement */
    .card-modern .d-grid {
        gap: 0.75rem !important;
    }

    .card-modern .d-grid .btn-modern {
        width: 100% !important;
        justify-content: flex-start !important;
        text-align: left !important;
        padding: 0.75rem 1rem !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        border-radius: 0.5rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .card-modern .d-grid .btn-light-modern {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        color: #495057 !important;
        border: 1px solid #dee2e6 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    }

    .card-modern .d-grid .btn-light-modern:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%) !important;
        color: #495057 !important;
        transform: translateY(-2px) translateX(4px) !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
    }

    .card-modern .d-grid .btn-light-modern i {
        font-size: 1rem !important;
        transition: transform 0.3s ease !important;
        width: 20px !important;
        text-align: center !important;
    }

    .card-modern .d-grid .btn-light-modern:hover i.bi-check-circle {
        transform: scale(1.1) rotate(5deg) !important;
        color: #198754 !important;
    }

    .card-modern .d-grid .btn-light-modern:hover i.bi-trash {
        transform: scale(1.1) rotate(-5deg) !important;
        color: #dc3545 !important;
    }

    .card-modern .d-grid .btn-light-modern:hover i.bi-file-code {
        transform: translateY(-2px) !important;
        color: #0d6efd !important;
    }

    /* Responsive adjustments for light modern buttons */
    @media (max-width: 768px) {
        .btn-light-modern {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .btn-light-modern i {
            font-size: 0.9rem;
        }
    }

    /* Input Validation States */
    .form-control.is-valid {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    /* Custom styles for menu configuration */
    .link-config-item {
        border-left: 3px solid #0d6efd;
        padding: 1rem;
        margin-bottom: 1.5rem;
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }

    .link-config-item:hover {
        transform: translateX(8px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .link-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    .link-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .link-description {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.4;
    }

    .url-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem;
        margin-top: 0.5rem;
        font-family: monospace;
        font-size: 0.85rem;
        color: #6c757d;
        word-break: break-all;
    }

    .url-preview.empty {
        color: #adb5bd;
        font-style: italic;
    }



    .info-panel {
        background: #0dcaf0;
        border-radius: 0.375rem;
        padding: 1rem;
        color: white;
        margin-bottom: 1rem;
    }

    .info-panel h5 {
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .info-panel ul {
        margin: 0;
        padding-left: 1.25rem;
    }

    .info-panel li {
        margin-bottom: 0.5rem;
        opacity: 0.95;
    }
</style>
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
                                Cập nhật <strong>liên kết hệ thống</strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Quản lý các liên kết hiển thị trong menu và trang chủ hệ thống.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($message): ?>
                <div class="alert alert-modern alert-<?= $messageType ?> fade-in-up">
                    <i
                        class="bi bi-<?= $messageType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Configuration Form -->
                <div class="col-lg-8">
                    <div class="card-modern fade-in-up">
                        <div class="card-header-modern">
                            <i class="bi bi-gear me-2"></i>Cấu hình liên kết hệ thống
                        </div>
                        <div class="card-body-modern">
                            <form method="POST" id="configForm">
                                <!-- Kết quả thi -->
                                <div class="link-config-item">
                                    <div class="link-icon">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                    <div class="link-title">Kết quả thi</div>
                                    <div class="link-description">
                                        Liên kết đến trang hoặc file chứa kết quả thi của sinh viên.
                                        Thường là Google Drive hoặc trang Website chuyên dụng.
                                    </div>

                                    <label class="form-label form-label-modern">URL Kết quả thi</label>
                                    <input type="url" class="form-control form-control-modern" name="ket_qua_thi"
                                        id="ket_qua_thi" value="<?= htmlspecialchars($ketQuaThi) ?>"
                                        placeholder="https://drive.google.com/... hoặc https://ketqua.example.com">

                                    <?php if ($ketQuaThi): ?>
                                        <div class="url-preview">
                                            <i class="bi bi-link me-1"></i>
                                            <?= htmlspecialchars($ketQuaThi) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="url-preview empty">
                                            Chưa có liên kết nào được cấu hình
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Biểu mẫu sinh viên -->
                                <div class="link-config-item">
                                    <div class="link-icon">
                                        <i class="bi bi-file-earmark-person"></i>
                                    </div>
                                    <div class="link-title">Biểu mẫu Sinh viên</div>
                                    <div class="link-description">
                                        Liên kết đến thư mục hoặc trang chứa các biểu mẫu dành cho sinh viên.
                                        Bao gồm đơn xin, giấy tờ, và các mẫu đăng ký.
                                    </div>

                                    <label class="form-label form-label-modern">URL Biểu mẫu Sinh viên</label>
                                    <input type="url" class="form-control form-control-modern" name="bieu_mau_sinh_vien"
                                        id="bieu_mau_sinh_vien" value="<?= htmlspecialchars($bieuMauSinhVien) ?>"
                                        placeholder="https://drive.google.com/... hoặc https://bieumau.example.com">

                                    <?php if ($bieuMauSinhVien): ?>
                                        <div class="url-preview">
                                            <i class="bi bi-link me-1"></i>
                                            <?= htmlspecialchars($bieuMauSinhVien) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="url-preview empty">
                                            Chưa có liên kết nào được cấu hình
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Biểu mẫu giảng viên -->
                                <div class="link-config-item">
                                    <div class="link-icon">
                                        <i class="bi bi-file-earmark-person-fill"></i>
                                    </div>
                                    <div class="link-title">Biểu mẫu Giảng viên</div>
                                    <div class="link-description">
                                        Liên kết đến thư mục hoặc trang chứa các biểu mẫu dành cho giảng viên.
                                        Bao gồm mẫu đánh giá, báo cáo, và các tài liệu hành chính.
                                    </div>

                                    <label class="form-label form-label-modern">URL Biểu mẫu Giảng viên</label>
                                    <input type="url" class="form-control form-control-modern"
                                        name="bieu_mau_giang_vien" id="bieu_mau_giang_vien"
                                        value="<?= htmlspecialchars($bieuMauGiangVien) ?>"
                                        placeholder="https://drive.google.com/... hoặc https://bieumau-gv.example.com">

                                    <?php if ($bieuMauGiangVien): ?>
                                        <div class="url-preview">
                                            <i class="bi bi-link me-1"></i>
                                            <?= htmlspecialchars($bieuMauGiangVien) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="url-preview empty">
                                            Chưa có liên kết nào được cấu hình
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons -->
                                <div class="action-buttons-container mt-4">
                                    <a href="dashboard.php" class="btn btn-action btn-back">
                                        Quay lại
                                    </a>
                                    <div class="action-buttons-right">
                                        <button type="button" class="btn btn-action btn-reset" onclick="resetForm()">
                                            <i class="bi bi-arrow-clockwise me-2"></i>Đặt lại
                                        </button>
                                        <button type="submit" class="btn btn-action btn-save">
                                            <i class="bi bi-check-lg me-2"></i>Lưu cấu hình
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Information Panel -->
                <div class="col-lg-4">
                    <div class="info-panel fade-in-up">
                        <h5>Hướng dẫn sử dụng</h5>
                        <ul>
                            <li><strong>URL hợp lệ:</strong> Bắt đầu bằng http:// hoặc https://</li>
                            <li><strong>Google Drive:</strong> Sử dụng link chia sẻ công khai</li>
                            <li><strong>Link trống:</strong> Links tương ứng sẽ không hiển thị</li>
                            <li><strong>Kiểm tra:</strong> Kiểm tra link trước khi lưu</li>
                            <li><strong>Bảo mật:</strong> Chỉ sử dụng link đáng tin cậy</li>
                        </ul>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card-modern mt-3 fade-in-up">
                        <div class="card-header-modern">
                            <i class="bi bi-lightning me-2"></i>Thao tác nhanh
                        </div>
                        <div class="card-body-modern">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-modern btn-light-modern" onclick="testAllLinks()">
                                    <i class="bi bi-check-circle me-2"></i>Kiểm tra tất cả links
                                </button>
                                <button type="button" class="btn btn-modern btn-light-modern" onclick="clearAllLinks()">
                                    <i class="bi bi-trash me-2"></i>Xóa tất cả links
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- Current Status -->
                    <div class="card-modern mt-3 fade-in-up">
                        <div class="card-header-modern">
                            <i class="bi bi-activity me-2"></i>Trạng thái hiện tại
                        </div>
                        <div class="card-body-modern">
                            <div class="mb-2">
                                <span class="badge <?= $ketQuaThi ? 'bg-success' : 'bg-secondary' ?> me-2">
                                    <?= $ketQuaThi ? '✓' : '✗' ?>
                                </span>
                                Kết quả thi
                            </div>
                            <div class="mb-2">
                                <span class="badge <?= $bieuMauSinhVien ? 'bg-success' : 'bg-secondary' ?> me-2">
                                    <?= $bieuMauSinhVien ? '✓' : '✗' ?>
                                </span>
                                Biểu mẫu Sinh viên
                            </div>
                            <div class="mb-2">
                                <span class="badge <?= $bieuMauGiangVien ? 'bg-success' : 'bg-secondary' ?> me-2">
                                    <?= $bieuMauGiangVien ? '✓' : '✗' ?>
                                </span>
                                Biểu mẫu Giảng viên
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Modern Menu Configuration JavaScript
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Modern menu configuration UI loaded');

            // Add real-time URL validation
            const urlInputs = document.querySelectorAll('input[type="url"]');
            urlInputs.forEach(function (input) {
                input.addEventListener('input', function () {
                    validateUrl(this);
                    updatePreview(this);
                });

                input.addEventListener('blur', function () {
                    validateUrl(this);
                });
            });

            // Form submission with loading state
            document.getElementById('configForm').addEventListener('submit', function () {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Đang lưu...';
                submitBtn.disabled = true;

                // Re-enable after 3 seconds as fallback
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 3000);
            });

            // Add hover effects for link config items
            document.querySelectorAll('.link-config-item').forEach(function (item) {
                item.addEventListener('mouseenter', function () {
                    this.style.transform = 'translateX(8px)';
                });

                item.addEventListener('mouseleave', function () {
                    this.style.transform = 'translateX(4px)';
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

            // Add keyboard shortcuts
            document.addEventListener('keydown', function (e) {
                // Ctrl/Cmd + S = Save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    document.getElementById('configForm').submit();
                }

                // Ctrl/Cmd + R = Reset
                if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                    e.preventDefault();
                    resetForm();
                }
            });

            console.log('All modern UI features initialized');
        });

        // URL Validation Function
        function validateUrl(input) {
            const url = input.value.trim();
            const urlPattern = /^https?:\/\/.+/i;

            // Remove existing validation classes
            input.classList.remove('is-valid', 'is-invalid');

            if (url === '') {
                // Empty is allowed
                return true;
            } else if (urlPattern.test(url)) {
                input.classList.add('is-valid');
                return true;
            } else {
                input.classList.add('is-invalid');
                return false;
            }
        }

        // Update URL Preview
        function updatePreview(input) {
            const previewDiv = input.parentNode.querySelector('.url-preview');
            if (previewDiv) {
                const url = input.value.trim();
                if (url) {
                    previewDiv.innerHTML = '<i class="bi bi-link me-1"></i>' + url;
                    previewDiv.classList.remove('empty');
                } else {
                    previewDiv.innerHTML = 'Chưa có liên kết nào được cấu hình';
                    previewDiv.classList.add('empty');
                }
            }
        }

        // Reset Form Function
        function resetForm() {
            if (confirm('Bạn có chắc muốn đặt lại tất cả các trường về trạng thái ban đầu?')) {
                document.getElementById('configForm').reset();

                // Update previews
                document.querySelectorAll('input[type="url"]').forEach(function (input) {
                    updatePreview(input);
                    input.classList.remove('is-valid', 'is-invalid');
                });

                // Show success message
                showNotification('Đã đặt lại form thành công', 'info');
            }
        }

        // Test All Links Function
        function testAllLinks() {
            const links = [
                document.getElementById('ket_qua_thi').value,
                document.getElementById('bieu_mau_sinh_vien').value,
                document.getElementById('bieu_mau_giang_vien').value
            ].filter(link => link.trim() !== '');

            if (links.length === 0) {
                showNotification('Không có link nào để kiểm tra!', 'warning');
                return;
            }

            showNotification(`Đang kiểm tra ${links.length} link(s)...`, 'info');

            let checkedCount = 0;
            let validCount = 0;

            links.forEach(function (link) {
                // Simple check - try to create URL object
                try {
                    new URL(link);
                    validCount++;
                } catch (e) {
                    console.log('Invalid URL:', link);
                }

                checkedCount++;

                if (checkedCount === links.length) {
                    const message = `Kiểm tra hoàn tất: ${validCount}/${links.length} link hợp lệ`;
                    const type = validCount === links.length ? 'success' : 'warning';
                    showNotification(message, type);
                }
            });
        }

        // Clear All Links Function
        function clearAllLinks() {
            if (confirm('Bạn có chắc muốn xóa tất cả các link đã cấu hình?')) {
                document.querySelectorAll('input[type="url"]').forEach(function (input) {
                    input.value = '';
                    updatePreview(input);
                    input.classList.remove('is-valid', 'is-invalid');
                });

                showNotification('Đã xóa tất cả các link!', 'info');
            }
        }

        // Show Notification Function
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            document.querySelectorAll('.notification-toast').forEach(n => n.remove());

            const notification = document.createElement('div');
            notification.className = `alert alert-modern alert-${type} notification-toast`;
            notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    `;

            const icon = type === 'success' ? 'check-circle-fill' :
                type === 'warning' ? 'exclamation-triangle-fill' :
                    type === 'danger' ? 'x-circle-fill' : 'info-circle-fill';

            notification.innerHTML = `
        <i class="bi bi-${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close ms-auto" onclick="this.parentElement.remove()"></button>
    `;

            document.body.appendChild(notification);

            // Auto remove after 4 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.animation = 'slideOutRight 0.3s ease-out';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 4000);
        }

        // Add CSS animations for notifications
        const style = document.createElement('style');
        style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-toast {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .notification-toast .btn-close {
        background: none;
        border: none;
        font-size: 1.2rem;
        opacity: 0.7;
        cursor: pointer;
    }
    
    .notification-toast .btn-close:hover {
        opacity: 1;
    }
`;
        document.head.appendChild(style);

        // Add form validation on submit
        document.getElementById('configForm').addEventListener('submit', function (e) {
            let isValid = true;

            document.querySelectorAll('input[type="url"]').forEach(function (input) {
                if (!validateUrl(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                showNotification('Vui lòng kiểm tra lại các URL không hợp lệ!', 'danger');
            }
        });
    </script>

    <?php include_once __DIR__ . '/includes/modal_quan_ly_tai_khoan.php'; ?>

    </body>

    </html>