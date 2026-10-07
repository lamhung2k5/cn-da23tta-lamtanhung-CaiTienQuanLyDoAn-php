<?php
/**
 * TRANG THÔNG BÁO ĐỒ ÁN CƠ SỞ NGÀNH VÀ CHUYÊN NGÀNH
 * Hiển thị nội dung từ database - có thể được chỉnh sửa bởi lãnh đạo
 */

require_once 'bootstrap.php';

$pageTitle = 'Thông báo thời gian nộp đồ án';

// Check if user is logged in as lãnh đạo
$isLanhDao = false;
$user = null;
if (function_exists('getCurrentUser')) {
    $user = getCurrentUser();
    if ($user && isset($user['vai_tro']) && $user['vai_tro'] === 'lanh_dao') {
        $isLanhDao = true;
    }
}

// Handle edit form submission
if ($isLanhDao && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $contentModel = new ThongBaoDoAnModel();

    if ($_POST['action'] === 'save_timeline') {
        $id = (int) $_POST['id'];
        $data = [
            'loai' => $_POST['loai'],
            'thu_tu' => $_POST['thu_tu'],
            'tieu_de' => $_POST['tieu_de'],
            'noi_dung' => $_POST['noi_dung'],
            'ngay' => $_POST['ngay'],
            'thang' => $_POST['thang'],
            'nam' => $_POST['nam'],
            'yeu_cau' => $_POST['yeu_cau'],
            'trang_thai' => $_POST['trang_thai']
        ];
        $contentModel->updateTimelineItem($id, $data);
    }

    if ($_POST['action'] === 'save_notice') {
        $notice = $contentModel->getNotice();
        $data = [
            'tieu_de' => $_POST['tieu_de'],
            'noi_dung' => $_POST['noi_dung'],
            'trang_thai' => $_POST['trang_thai']
        ];
        $contentModel->updateNotice($notice['id'], $data);
    }

    if ($_POST['action'] === 'save_page') {
        $pageContent = $contentModel->getPageContent();
        $data = [
            'page_title' => $_POST['page_title'],
            'subtitle' => $_POST['subtitle'],
            'date_badge' => $_POST['date_badge'],
            'trang_thai' => $_POST['trang_thai'],
            'ngay_bat_dau' => !empty($_POST['ngay_bat_dau']) ? $_POST['ngay_bat_dau'] : null,
            'ngay_ket_thuc' => !empty($_POST['ngay_ket_thuc']) ? $_POST['ngay_ket_thuc'] : null
        ];
        $contentModel->updatePageContent($pageContent['id'], $data);
    }

    // Refresh data
    $pageContent = $contentModel->getPageContent();
    $timelineCoSoNganh = $contentModel->getTimelineItems('co_so_nganh');
    $timelineChuyenNganh = $contentModel->getTimelineItems('chuyen_nganh');
    $notice = $contentModel->getNotice();
}

// Lấy nội dung từ database
$contentModel = new ThongBaoDoAnModel();
$pageContent = $contentModel->getPageContent();
$timelineCoSoNganh = $contentModel->getTimelineItems('co_so_nganh');
$timelineChuyenNganh = $contentModel->getTimelineItems('chuyen_nganh');
$notice = $contentModel->getNotice();

// Kiểm tra trang có đang hoạt động không
$pageActive = $contentModel->isPageActive();

// Tính toán countdown cho mỗi mốc thời gian
function getCountdownInfo($ngay, $thang, $nam)
{
    $deadline = mktime(23, 59, 59, $thang, $ngay, $nam);
    $today = time();
    $diff = $deadline - $today;
    $days = floor($diff / (60 * 60 * 24));

    if ($days < 0) {
        return ['days' => $days, 'text' => 'Đã hết hạn', 'class' => 'expired'];
    } elseif ($days == 0) {
        return ['days' => 0, 'text' => 'Hôm nay', 'class' => 'urgent'];
    } elseif ($days <= 7) {
        return ['days' => $days, 'text' => "Còn {$days} ngày", 'class' => 'urgent'];
    } elseif ($days <= 30) {
        return ['days' => $days, 'text' => "Còn {$days} ngày", 'class' => 'warning'];
    } else {
        return ['days' => $days, 'text' => "Còn {$days} ngày", 'class' => 'normal'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?> - Hệ Thống Quản Lý Đề Tài</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">

    <style>
        /* ============ EDITOR MODE ============ */
        .edit-mode .edit-btn {
            display: inline-block !important;
        }

        .edit-btn {
            display: none;
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 100;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        .edit-btn:hover {
            transform: scale(1.1);
        }

        .timeline-item {
            position: relative;
        }

        .timeline-item .edit-btn {
            top: 5px;
            right: 5px;
        }

        .edit-form {
            display: none;
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .edit-form.active {
            display: block;
        }

        .edit-form .form-control {
            margin-bottom: 10px;
        }

        .edit-form .btn {
            margin-right: 5px;
        }

        .announcement-header {
            position: relative;
        }

        .announcement-header .edit-btn {
            top: 0;
            right: 0;
        }

        .important-notice {
            position: relative;
        }

        .important-notice .edit-btn {
            top: 10px;
            right: 10px;
        }

        /* ============ DARK MODE CSS VARIABLES ============ */
        :root {
            --bg-primary: #f8f9fa;
            --bg-secondary: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #555;
            --text-muted: #6c757d;
            --card-bg: #ffffff;
            --card-shadow: rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --card-bg: #1e293b;
            --card-shadow: rgba(0, 0, 0, 0.3);
        }

        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        [data-theme="dark"] .announcement-card {
            background: var(--glass-bg);
            color: var(--text-primary);
        }

        [data-theme="dark"] .timeline-section {
            background: var(--card-bg);
        }

        [data-theme="dark"] .section-title h2,
        [data-theme="dark"] .announcement-header h1 {
            color: #3b82f6;
        }

        [data-theme="dark"] .timeline-item {
            background: var(--card-bg);
        }

        [data-theme="dark"] .timeline-content h4,
        [data-theme="dark"] .timeline-content p {
            color: var(--text-primary);
        }

        [data-theme="dark"] .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: #2c3e50;
        }

        .container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* ============ HEADER ============ */
        .top-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #1d4ed8 100%);
            color: white;
            padding: 12px 0;
            box-shadow: 0 4px 20px rgba(30, 58, 138, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid #fbbf24;
        }

        .logo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            padding: 3px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: contain;
        }

        .logo-university {
            font-size: 16px;
            font-weight: 800;
            color: white;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.3;
            margin-top: 4px;
        }

        .logo-text p {
            font-size: 12px;
            margin: 0;
            opacity: 0.9;
        }

        /* ============ NAVIGATION ============ */
        .nav-menu {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            position: relative;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .nav-item:hover {
            color: #ffd700;
            background: rgba(255, 215, 0, 0.15);
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.5);
        }

        .nav-item i {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .nav-item:hover i {
            transform: scale(1.1);
        }

        .login-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            color: white !important;
            padding: 12px 24px !important;
            border-radius: 25px !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: 2px solid transparent !important;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3) !important;
            position: relative !important;
            overflow: hidden !important;
            letter-spacing: 0.5px !important;
            animation: loginPulse 3s ease-in-out infinite;
        }

        @keyframes loginPulse {

            0%,
            100% {
                box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
            }

            50% {
                box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5);
            }
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
            color: white !important;
            transform: translateY(-3px) scale(1.05) !important;
            box-shadow: 0 12px 32px rgba(59, 130, 246, 0.4) !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
            animation: none;
        }

        .login-btn i {
            margin-right: 8px;
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .login-btn:hover i {
            transform: scale(1.1) rotate(-5deg);
        }

        /* Dropdown Styles */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            padding: 10px;
        }

        .dropdown-menu .dropdown-item {
            padding: 12px 15px;
            border-radius: 8px;
            font-weight: 500;
        }

        .dropdown-menu .dropdown-item:hover {
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            color: #0052a3;
        }

        .dropdown-menu .dropdown-item i {
            margin-right: 10px;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
            text-decoration: none;
        }

        .logo img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        .logo-text h3 {
            margin: 0;
            font-weight: 700;
        }

        .logo-text p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        /* Main Content */
        .main-content {
            padding: 40px 0;
        }

        .announcement-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .announcement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
        }

        .announcement-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .announcement-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .announcement-header .subtitle {
            font-size: 1.2rem;
            color: #7f8c8d;
            font-weight: 500;
        }

        .announcement-header .date-badge {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        /* Timeline Sections - Equal Height Cards */
        .timeline-section {
            margin-bottom: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }

        /* Equal height columns wrapper */
        .row.g-4 {
            align-items: stretch;
        }

        .col-md-6 {
            display: flex;
            flex-direction: column;
        }

        .col-md-6 .timeline-section {
            flex: 1;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .section-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .co-so-ngành .section-icon {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
        }

        .chuyen-ngành .section-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .section-title h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e40af;
        }

        .co-so-ngành .section-title h2 {
            color: #1e40af;
        }

        .chuyen-ngành .section-title h2 {
            color: #059669;
        }

        /* Timeline Items */
        .timeline-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            position: relative;
        }

        .timeline-item:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .co-so-ngành .timeline-item {
            border-left-color: #3b82f6;
        }

        .co-so-ngành .timeline-item:hover {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        }

        .chuyen-ngành .timeline-item {
            border-left-color: #10b981;
        }

        .chuyen-ngành .timeline-item:hover {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        }

        .timeline-date {
            flex-shrink: 0;
            width: 120px;
            text-align: center;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            color: white;
            position: relative;
        }

        .co-so-ngành .timeline-date {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
        }

        .chuyen-ngành .timeline-date {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        /* Countdown Timer Styles */
        .countdown-badge {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 8px;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .countdown-badge.urgent {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            animation: pulse-urgent 1.5s infinite;
        }

        .countdown-badge.warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .countdown-badge.normal {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .countdown-badge.expired {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }

        @keyframes pulse-urgent {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }

            50% {
                transform: scale(1.02);
                box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
            }
        }

        .countdown-icon {
            font-size: 0.9rem;
        }

        .timeline-date .day {
            font-size: 1.8rem;
            display: block;
        }

        .timeline-date .month {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-content h4 {
            margin: 0 0 10px 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e40af;
        }

        .timeline-content p {
            margin: 0 0 15px 0;
            color: #7f8c8d;
            line-height: 1.6;
        }

        .timeline-content .requirements {
            background: white;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #ecf0f1;
        }

        .timeline-content .requirements h5 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-weight: 600;
        }

        .timeline-content .requirements ul {
            margin: 0;
            padding-left: 20px;
        }

        .timeline-content .requirements li {
            margin-bottom: 5px;
            color: #7f8c8d;
        }

        /* Important Notice */
        .important-notice {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin: 30px 0;
            text-align: center;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }

        .important-notice h3 {
            margin: 0 0 15px 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .important-notice p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
            border-top: 4px solid #ffcc00;
        }

        .footer-title h4 {
            color: white;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .footer-subtitle {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .footer-contact p {
            color: rgba(255, 255, 255, 0.9);
        }

        .footer-contact a {
            color: #ffcc00;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-contact a:hover {
            color: white;
        }

        .footer-divider {
            background: linear-gradient(90deg, #ffcc00, transparent) !important;
        }

        /* Contact Info */
        .contact-info {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
        }

        .contact-info h3 {
            margin: 0 0 20px 0;
            font-weight: 700;
            text-align: center;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .contact-item i {
            font-size: 1.5rem;
            width: 40px;
            text-align: center;
        }

        .contact-item div h5 {
            margin: 0 0 5px 0;
            font-weight: 600;
        }

        .contact-item div p {
            margin: 0;
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .announcement-card {
                padding: 25px 20px;
            }

            .announcement-header h1 {
                font-size: 2rem;
            }

            .timeline-item {
                flex-direction: column;
                gap: 15px;
            }

            .timeline-date {
                width: 100%;
                text-align: center;
            }

            .section-title {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 20px 0;
            }

            .announcement-card {
                margin: 0 10px 20px 10px;
                padding: 20px 15px;
            }

            .announcement-header h1 {
                font-size: 1.8rem;
            }

            .announcement-header .subtitle {
                font-size: 1rem;
            }

            .timeline-item {
                padding: 15px;
            }

            .timeline-content h4 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="top-header">
        <div class="container">
            <!-- Logo & Navigation -->
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="logo-section">
                    <div class="logo-img">
                        <img src="assets/images/logo.png" alt="TVU Logo">
                    </div>
                    <div class="logo-text">
                        <div class="logo-university">ĐẠI HỌC TRÀ VINH</div>
                    </div>
                </div>

                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-item"><i class="bi bi-house"></i> Trang chủ</a></li>
                    <li class="dropdown">
                        <a href="thong_bao_do_an.php" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i> Thông báo
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="thong_bao_do_an.php"><i class="bi bi-bell me-2"></i>Thông
                                    báo đồ án</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-search"></i> Tra cứu
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="khoa_cntt.php"><i
                                        class="bi bi-person-badge me-2"></i>Khoa Công nghệ thông tin</a></li>
                            <li><a class="dropdown-item" href="danh_sach_de_tai_cong_khai.php"><i
                                        class="bi bi-journal-text me-2"></i>Danh sách đề tài</a></li>
                            <?php $menuLinks = getMenuLinks(); ?>
                            <?php if (!empty($menuLinks['ket_qua_thi'])): ?>
                                <li><a class="dropdown-item" href="<?= $menuLinks['ket_qua_thi'] ?>" target="_blank"><i
                                            class="bi bi-file-earmark-text me-2"></i>Kết quả thi</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a href="#" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-info-circle"></i> Giới thiệu
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="gioi_thieu_sinh_vien.php"><i
                                        class="bi bi-mortarboard me-2"></i>Sinh viên</a></li>
                            <li><a class="dropdown-item" href="gioi_thieu_giang_vien.php"><i
                                        class="bi bi-person-badge me-2"></i>Giảng viên</a></li>
                            <li><a class="dropdown-item" href="gioi_thieu_lanh_dao.php"><i
                                        class="bi bi-shield-lock me-2"></i>Lãnh đạo</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-people"></i> Biểu mẫu
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item"
                                    href="https://drive.google.com/drive/folders/1mSIO416XjD_t_J3RVunTQWK2qB4kRvNn"
                                    target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Biểu mẫu cho sinh
                                    viên</a></li>
                            <li><a class="dropdown-item"
                                    href="https://drive.google.com/drive/folders/1m0knxZO_grEEt3bDSLmATW5cTu2Bhzv5"
                                    target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Biểu mẫu dành cho giảng
                                    viên</a></li>
                        </ul>
                    </li>
                    <li><a href="auth/login.php" class="nav-item login-btn"><i class="bi bi-box-arrow-in-right"></i>
                            Đăng nhập</a></li>
                    <?php if ($isLanhDao): ?>
                        <li><a href="thong_bao_do_an.php" class="nav-item" style="background: rgba(255,193,7,0.2);"><i
                                    class="bi bi-pencil"></i> Chỉnh sửa</a></li>
                    <?php endif; ?>
                    <li>

                    </li>
                </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Announcement Header -->
            <div class="announcement-card">
                <div class="announcement-header">
                    <?php if ($isLanhDao): ?>
                        <button class="btn btn-primary btn-sm edit-btn" onclick="toggleEditForm('page')">
                            <i class="bi bi-pencil"></i> Sửa
                        </button>
                        <div id="edit-form-page" class="edit-form"
                            style="text-align: left; max-width: 600px; margin: 20px auto;">
                            <h5 class="mb-3">Sửa thông tin trang</h5>
                            <form method="POST">
                                <input type="hidden" name="action" value="save_page">
                                <div class="mb-2">
                                    <label class="form-label">Tiêu đề</label>
                                    <input type="text" name="page_title" class="form-control"
                                        value="<?php echo htmlspecialchars($pageContent['page_title'] ?? ''); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Mô tả</label>
                                    <input type="text" name="subtitle" class="form-control"
                                        value="<?php echo htmlspecialchars($pageContent['subtitle'] ?? ''); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nhãn ngày</label>
                                    <input type="text" name="date_badge" class="form-control"
                                        value="<?php echo htmlspecialchars($pageContent['date_badge'] ?? ''); ?>">
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Trạng thái</label>
                                        <select name="trang_thai" class="form-control">
                                            <option value="mo" <?php echo ($pageContent['trang_thai'] ?? 'mo') === 'mo' ? 'selected' : ''; ?>>Mở</option>
                                            <option value="khoa" <?php echo ($pageContent['trang_thai'] ?? 'mo') === 'khoa' ? 'selected' : ''; ?>>Khóa</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Từ ngày</label>
                                        <input type="date" name="ngay_bat_dau" class="form-control"
                                            value="<?php echo $pageContent['ngay_bat_dau'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Đến ngày</label>
                                        <input type="date" name="ngay_ket_thuc" class="form-control"
                                            value="<?php echo $pageContent['ngay_ket_thuc'] ?? ''; ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check"></i>
                                    Lưu</button>
                                <button type="button" class="btn btn-secondary btn-sm"
                                    onclick="toggleEditForm('page')">Hủy</button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <h1><i class="bi bi-megaphone-fill"></i>
                        <?php echo htmlspecialchars($pageContent['page_title'] ?? 'THÔNG BÁO'); ?></h1>
                    <p class="subtitle">
                        <?php echo htmlspecialchars($pageContent['subtitle'] ?? 'Thời gian nộp đồ án cơ sở ngành và chuyên ngành'); ?>
                    </p>
                    <span class="date-badge">
                        <?php echo htmlspecialchars($pageContent['date_badge'] ?? 'Học kỳ II - Năm học 2025-2026'); ?>
                    </span>
                </div>

                <!-- Page is locked message -->
                <?php if (!$pageActive): ?>
                    <div class="alert alert-secondary d-flex align-items-center mb-4" role="alert"
                        style="border-radius: 12px;">
                        <i class="bi bi-lock-fill fs-4 me-2"></i>
                        <div>
                            <h6 class="mb-1 fw-bold">Thông báo đồ án tạm thời bị khóa</h6>
                            <p class="mb-0 small">Hiện tại trang thông báo đồ án không hiển thị. Vui lòng liên hệ lãnh đạo
                                để biết thêm chi tiết</p>
                        </div>
                    </div>
                <?php else: ?>

                    <!-- Two Column Layout -->
                    <div class="row g-4">
                        <!-- Đồ án Cơ sở ngành -->
                        <div class="col-md-6">
                            <div class="timeline-section co-so-ngành">
                                <div class="section-title">
                                    <h2>ĐỒ ÁN CƠ SỞ NGÀNH</h2>
                                </div>

                                <?php if (!empty($timelineCoSoNganh)): ?>
                                    <?php foreach ($timelineCoSoNganh as $item): ?>
                                        <div class="timeline-item">
                                            <?php if ($isLanhDao): ?>
                                                <button class="btn btn-primary btn-sm edit-btn"
                                                    onclick="toggleEditForm('timeline-<?php echo $item['id']; ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <div id="edit-form-timeline-<?php echo $item['id']; ?>" class="edit-form">
                                                    <h6>Sửa mốc: <?php echo htmlspecialchars($item['tieu_de']); ?></h6>
                                                    <form method="POST">
                                                        <input type="hidden" name="action" value="save_timeline">
                                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                        <div class="row">
                                                            <div class="col-md-2"><input type="number" name="ngay" class="form-control"
                                                                    value="<?php echo $item['ngay']; ?>" placeholder="Ngày"></div>
                                                            <div class="col-md-2"><input type="number" name="thang" class="form-control"
                                                                    value="<?php echo $item['thang']; ?>" placeholder="Tháng"></div>
                                                            <div class="col-md-3"><input type="number" name="nam" class="form-control"
                                                                    value="<?php echo $item['nam']; ?>" placeholder="Năm"></div>
                                                            <div class="col-md-2"><input type="number" name="thu_tu"
                                                                    class="form-control" value="<?php echo $item['thu_tu']; ?>"
                                                                    placeholder="Thứ tự"></div>
                                                            <div class="col-md-3"><select name="trang_thai" class="form-control">
                                                                    <option value="mo" <?php echo $item['trang_thai'] === 'mo' ? 'selected' : ''; ?>>Hiện</option>
                                                                    <option value="khoa" <?php echo $item['trang_thai'] === 'khoa' ? 'selected' : ''; ?>>Ẩn</option>
                                                                </select></div>
                                                        </div>
                                                        <input type="text" name="tieu_de" class="form-control"
                                                            value="<?php echo htmlspecialchars($item['tieu_de']); ?>"
                                                            placeholder="Tiêu đề">
                                                        <textarea name="noi_dung" class="form-control"
                                                            placeholder="Nội dung"><?php echo htmlspecialchars($item['noi_dung']); ?></textarea>
                                                        <textarea name="yeu_cau" class="form-control"
                                                            placeholder="Yêu cầu (mỗi dòng một)"><?php echo htmlspecialchars(str_replace('|', "\n", $item['yeu_cau'])); ?></textarea>
                                                        <input type="hidden" name="loai" value="<?php echo $item['loai']; ?>">
                                                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check"></i>
                                                            Lưu</button>
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="toggleEditForm('timeline-<?php echo $item['id']; ?>')">Hủy</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                            <div class="timeline-date">
                                                <span class="day"><?php echo $item['ngay']; ?></span>
                                                <span class="month">Tháng <?php echo $item['thang']; ?></span>
                                            </div>
                                            <div class="timeline-content">
                                                <h4><?php echo htmlspecialchars($item['tieu_de']); ?></h4>
                                                <p><?php echo htmlspecialchars($item['noi_dung']); ?></p>
                                                <?php
                                                $countdown = getCountdownInfo($item['ngay'], $item['thang'], $item['nam']);
                                                ?>
                                                <div class="countdown-badge <?php echo $countdown['class']; ?> mb-2"
                                                    style="display: inline-flex;">
                                                    <i class="bi bi-clock-history countdown-icon"></i>
                                                    <?php echo $countdown['text']; ?>
                                                </div>
                                                <?php if ($item['yeu_cau']): ?>
                                                    <div class="requirements">
                                                        <h5>Yêu cầu nộp:</h5>
                                                        <ul>
                                                            <?php foreach (explode('|', $item['yeu_cau']) as $yc): ?>
                                                                <li><?php echo htmlspecialchars(trim($yc)); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Chưa có lịch trình cơ sở ngành</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Đồ án Chuyên ngành -->
                        <div class="col-md-6">
                            <div class="timeline-section chuyen-ngành">
                                <div class="section-title">
                                    <h2>ĐỒ ÁN CHUYÊN NGÀNH</h2>
                                </div>

                                <?php if (!empty($timelineChuyenNganh)): ?>
                                    <?php foreach ($timelineChuyenNganh as $item): ?>
                                        <div class="timeline-item">
                                            <?php if ($isLanhDao): ?>
                                                <button class="btn btn-success btn-sm edit-btn"
                                                    onclick="toggleEditForm('timeline-<?php echo $item['id']; ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <div id="edit-form-timeline-<?php echo $item['id']; ?>" class="edit-form">
                                                    <h6>Sửa mốc: <?php echo htmlspecialchars($item['tieu_de']); ?></h6>
                                                    <form method="POST">
                                                        <input type="hidden" name="action" value="save_timeline">
                                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                        <div class="row">
                                                            <div class="col-md-2"><input type="number" name="ngay" class="form-control"
                                                                    value="<?php echo $item['ngay']; ?>" placeholder="Ngày"></div>
                                                            <div class="col-md-2"><input type="number" name="thang" class="form-control"
                                                                    value="<?php echo $item['thang']; ?>" placeholder="Tháng"></div>
                                                            <div class="col-md-3"><input type="number" name="nam" class="form-control"
                                                                    value="<?php echo $item['nam']; ?>" placeholder="Năm"></div>
                                                            <div class="col-md-2"><input type="number" name="thu_tu"
                                                                    class="form-control" value="<?php echo $item['thu_tu']; ?>"
                                                                    placeholder="Thứ tự"></div>
                                                            <div class="col-md-3"><select name="trang_thai" class="form-control">
                                                                    <option value="mo" <?php echo $item['trang_thai'] === 'mo' ? 'selected' : ''; ?>>Hiện</option>
                                                                    <option value="khoa" <?php echo $item['trang_thai'] === 'khoa' ? 'selected' : ''; ?>>Ẩn</option>
                                                                </select></div>
                                                        </div>
                                                        <input type="text" name="tieu_de" class="form-control"
                                                            value="<?php echo htmlspecialchars($item['tieu_de']); ?>"
                                                            placeholder="Tiêu đề">
                                                        <textarea name="noi_dung" class="form-control"
                                                            placeholder="Nội dung"><?php echo htmlspecialchars($item['noi_dung']); ?></textarea>
                                                        <textarea name="yeu_cau" class="form-control"
                                                            placeholder="Yêu cầu (mỗi dòng một)"><?php echo htmlspecialchars(str_replace('|', "\n", $item['yeu_cau'])); ?></textarea>
                                                        <input type="hidden" name="loai" value="<?php echo $item['loai']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check"></i>
                                                            Lưu</button>
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="toggleEditForm('timeline-<?php echo $item['id']; ?>')">Hủy</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                            <div class="timeline-date">
                                                <span class="day"><?php echo $item['ngay']; ?></span>
                                                <span class="month">Tháng <?php echo $item['thang']; ?></span>
                                            </div>
                                            <div class="timeline-content">
                                                <h4><?php echo htmlspecialchars($item['tieu_de']); ?></h4>
                                                <p><?php echo htmlspecialchars($item['noi_dung']); ?></p>
                                                <?php
                                                $countdown = getCountdownInfo($item['ngay'], $item['thang'], $item['nam']);
                                                ?>
                                                <div class="countdown-badge <?php echo $countdown['class']; ?> mb-2"
                                                    style="display: inline-flex;">
                                                    <i class="bi bi-clock-history countdown-icon"></i>
                                                    <?php echo $countdown['text']; ?>
                                                </div>
                                                <?php if ($item['yeu_cau']): ?>
                                                    <div class="requirements">
                                                        <h5>Yêu cầu nộp:</h5>
                                                        <ul>
                                                            <?php foreach (explode('|', $item['yeu_cau']) as $yc): ?>
                                                                <li><?php echo htmlspecialchars(trim($yc)); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Chưa có lịch trình chuyên ngành</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notice -->
                    <?php if ($notice && $notice['trang_thai'] === 'mo'): ?>
                        <div class="important-notice">
                            <?php if ($isLanhDao): ?>
                                <button class="btn btn-light btn-sm edit-btn" onclick="toggleEditForm('notice')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <div id="edit-form-notice" class="edit-form"
                                    style="background: rgba(255,255,255,0.95); text-align: left;">
                                    <h6>Sửa lưu ý</h6>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="save_notice">
                                        <input type="text" name="tieu_de" class="form-control"
                                            value="<?php echo htmlspecialchars($notice['tieu_de']); ?>" placeholder="Tiêu đề">
                                        <textarea name="noi_dung" class="form-control" rows="3"
                                            placeholder="Nội dung"><?php echo htmlspecialchars($notice['noi_dung']); ?></textarea>
                                        <select name="trang_thai" class="form-control mb-2">
                                            <option value="mo" <?php echo $notice['trang_thai'] === 'mo' ? 'selected' : ''; ?>>Hiện
                                            </option>
                                            <option value="khoa" <?php echo $notice['trang_thai'] === 'khoa' ? 'selected' : ''; ?>>Ẩn
                                            </option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check"></i>
                                            Lưu</button>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            onclick="toggleEditForm('notice')">Hủy</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            <h3>
                                <?php echo htmlspecialchars($notice['tieu_de']); ?>
                            </h3>
                            <p><?php echo nl2br(htmlspecialchars($notice['noi_dung'])); ?></p>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- Left Section - Info -->
                <div class="col-md-8">
                    <div class="footer-title mb-3">
                        <h4>Khoa Công nghệ thông tin - Đại học Trà Vinh </h4>
                        <p class="footer-subtitle mb-0">School of Information Technology - Tra Vinh University</p>
                    </div>

                    <div class="footer-divider mb-3"
                        style="width: 100px; height: 3px; background: linear-gradient(135deg, #ffcc00 0%, #ffa500 100%);">
                    </div>

                    <div class="footer-contact">
                        <p class="mb-2">
                            <i class="bi bi-geo-alt-fill me-2"></i>
                            Số 126, Nguyễn Thiện Thành, Khóm 4, Phường Hòa Thuận, Tỉnh Vĩnh Long
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-telephone-fill me-2"></i>
                            (+84) 294.3855246
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-envelope-fill me-2"></i>
                            <a href="mailto:ktcn@tvu.edu.vn">ktcn@tvu.edu.vn</a>
                        </p>
                        <p class="mb-3">
                            <i class="bi bi-globe me-2"></i>
                            <a href="https://cet.tvu.edu.vn" target="_blank">https://cet.tvu.edu.vn</a>
                        </p>
                    </div>

                    <div class="footer-brand d-flex align-items-center gap-3">
                        <div class="brand-line"
                            style="width: 150px; height: 2px; background: linear-gradient(90deg, #ffcc00 0%, transparent 100%);">
                        </div>
                        <span style="font-weight: 600; color: #ffcc00;">Tra Vinh University</span>
                    </div>
                </div>

                <!-- Right Section - Social -->
                <div class="col-md-4 text-end">
                    <div class="social-icons d-flex justify-content-end gap-3">
                        <a href="https://facebook.com" target="_blank" class="social-icon"
                            style="width: 50px; height: 50px; border-radius: 50%; background-color: #1877f2; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 1.5rem; transition: all 0.3s ease;"
                            onmouseover="this.style.transform='scale(1.1)'"
                            onmouseout="this.style.transform='scale(1)'">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://youtube.com" target="_blank" class="social-icon"
                            style="width: 50px; height: 50px; border-radius: 50%; background-color: #ff0000; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 1.5rem; transition: all 0.3s ease;"
                            onmouseover="this.style.transform='scale(1.1)'"
                            onmouseout="this.style.transform='scale(1)'">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="https://github.com" target="_blank" class="social-icon"
                            style="width: 50px; height: 50px; border-radius: 50%; background-color: #333; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 1.5rem; transition: all 0.3s ease;"
                            onmouseover="this.style.transform='scale(1.1)'"
                            onmouseout="this.style.transform='scale(1)'">
                            <i class="bi bi-github"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleDarkMode() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('theme-icon');

            if (html.getAttribute('data-theme') === 'dark') {
                html.setAttribute('data-theme', 'light');
                themeIcon.className = 'bi bi-moon-fill';
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                themeIcon.className = 'bi bi-sun-fill';
                localStorage.setItem('theme', 'dark');
            }
        }

        function toggleEditForm(id) {
            var form = document.getElementById('edit-form-' + id);
            if (form) {
                form.classList.toggle('active');
            }
        }

        // Close edit forms when clicking outside
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.edit-form') && !e.target.closest('.edit-btn')) {
                document.querySelectorAll('.edit-form').forEach(function (form) {
                    form.classList.remove('active');
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const savedTheme = localStorage.getItem('theme');
            const html = document.documentElement;
            const themeIcon = document.getElementById('theme-icon');

            if (!savedTheme) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    html.setAttribute('data-theme', 'dark');
                    themeIcon.className = 'bi bi-sun-fill';
                }
            } else if (savedTheme === 'dark') {
                html.setAttribute('data-theme', 'dark');
                themeIcon.className = 'bi bi-sun-fill';
            }
        });
    </script>

    <script>
        // Enhanced dropdown functionality for submenu - Same as khoa_cntt.php
        document.addEventListener('DOMContentLoaded', function () {
            const submenuToggles = document.querySelectorAll('.submenu-toggle');

            submenuToggles.forEach(function (toggle) {
                const submenu = toggle.closest('.dropdown-submenu');
                const submenuContent = submenu.querySelector('.dropdown-submenu-content');

                // Handle click
                toggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Close other submenus first
                    document.querySelectorAll('.dropdown-submenu-content').forEach(function (content) {
                        if (content !== submenuContent) {
                            content.style.display = 'none';
                            content.classList.remove('show');
                        }
                    });

                    // Toggle current submenu
                    const isVisible = submenuContent.style.display === 'block' || submenuContent.classList.contains('show');

                    if (isVisible) {
                        submenuContent.style.display = 'none';
                        submenuContent.classList.remove('show');
                    } else {
                        submenuContent.style.display = 'block';
                        submenuContent.style.visibility = 'visible';
                        submenuContent.style.opacity = '1';
                        submenuContent.classList.add('show');
                    }
                });

                // Handle hover for desktop
                submenu.addEventListener('mouseenter', function () {
                    if (window.innerWidth > 768) {
                        // Close other submenus
                        document.querySelectorAll('.dropdown-submenu-content').forEach(function (content) {
                            if (content !== submenuContent) {
                                content.style.display = 'none';
                                content.classList.remove('show');
                            }
                        });

                        submenuContent.style.display = 'block';
                        submenuContent.style.visibility = 'visible';
                        submenuContent.style.opacity = '1';
                        submenuContent.classList.add('show');
                    }
                });

                submenu.addEventListener('mouseleave', function () {
                    if (window.innerWidth > 768) {
                        setTimeout(function () {
                            if (!submenu.matches(':hover')) {
                                submenuContent.style.display = 'none';
                            }
                        }, 100);
                    }
                });
            });

            // Close submenus when clicking outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.dropdown-submenu') && !e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-submenu-content').forEach(function (content) {
                        content.style.display = 'none';
                        content.classList.remove('show');
                    });
                }
            });

            // Sticky Header functionality
            const header = document.querySelector('.top-header');
            const body = document.body;

            function handleScroll() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > 50) {
                    header.classList.add('scrolled');
                    body.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                    body.classList.remove('scrolled');
                }
            }

            // Add scroll event listener
            window.addEventListener('scroll', handleScroll);

            // Call once to set initial state
            handleScroll();
        });
    </script>
</body>

</html>
