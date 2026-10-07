<?php
/**
 * TRANG CHỦ - HỆ THỐNG QUẢN LÝ ĐỀ TÀI
 */

require_once 'bootstrap.php';

// Lấy thông báo công khai
try {
    $thongBaoModel = new ThongBaoModel();
    $dsThongBao = $thongBaoModel->getAllThongBaoCongKhai(10); // Lấy TẤT CẢ thông báo (không lọc ngày)
    $soThongBao = count($dsThongBao);
} catch (Exception $e) {
    $dsThongBao = [];
    $soThongBao = 0;
    error_log("Database Error: " . $e->getMessage());
}

// Nếu đã đăng nhập, chuyển đến dashboard
if (isLoggedIn()) {
    $user = getCurrentUser();
    switch ($user['vai_tro']) {
        case ROLE_GIANG_VIEN:
            redirect('giang_vien/dashboard.php');
            break;
        case ROLE_SINH_VIEN:
            redirect('sinh_vien/dashboard.php');
            break;
        case ROLE_LANH_DAO:
            redirect('lanh_dao/dashboard.php');
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Hệ Thống Quản Lý Đề Tài</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">

    <style>
        /* ============ RESET & BASE STYLES ============ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ============ LIGHT MODE (DEFAULT) ============ */
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f4f8 100%);
            color: #2c3e50;
            padding-top: 0;
            height: 100%;
            overflow-x: hidden;
        }

        /* Dark Mode - Only apply CSS variables when explicitly set */
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
        }

        [data-theme="dark"] .top-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        [data-theme="dark"] .hero-section {
            background-attachment: fixed;
            background-image: linear-gradient(rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.1)),
                url('img/backgourd234.png');
        }

        [data-theme="dark"] .news-card,
        [data-theme="dark"] .role-section .card,
        [data-theme="dark"] .feature-card {
            background: #1e293b;
            border-color: #334155;
        }

        [data-theme="dark"] .news-card-title,
        [data-theme="dark"] .news-card-text,
        [data-theme="dark"] .news-card-date {
            color: #f1f5f9;
        }

        [data-theme="dark"] .news-card-text {
            color: #cbd5e1;
        }

        [data-theme="dark"] .role-section .card-body ul li {
            color: #cbd5e1;
        }

        [data-theme="dark"] .role-section .card-title {
            color: #3b82f6;
        }

        [data-theme="dark"] .news-section,
        [data-theme="dark"] .features-section,
        [data-theme="dark"] .role-section {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
        }

        [data-theme="dark"] .news-card {
            background: rgba(30, 41, 59, 0.85);
            border-color: rgba(59, 130, 246, 0.2);
        }

        [data-theme="dark"] .news-card-content {
            background: linear-gradient(to bottom, rgba(30, 41, 59, 0.9), rgba(30, 41, 59, 1));
        }

        [data-theme="dark"] .news-card-date {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.15);
        }

        [data-theme="dark"] .news-card:hover .news-card-title {
            color: #93c5fd;
        }

        [data-theme="dark"] .news-card-text {
            color: #cbd5e1;
        }

        [data-theme="dark"] .section-title h2,
        [data-theme="dark"] .features-title h2,
        [data-theme="dark"] .role-section .text-center h2 {
            color: #3b82f6;
        }

        [data-theme="dark"] .section-title p,
        [data-theme="dark"] .features-subtitle,
        [data-theme="dark"] .role-section .text-center p {
            color: #cbd5e1;
        }

        [data-theme="dark"] .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        [data-theme="dark"] .modern-notifications-card {
            background: rgba(30, 41, 59, 0.95);
        }

        [data-theme="dark"] .notification-item-modern {
            background: transparent;
        }

        [data-theme="dark"] .notification-item-modern:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        [data-theme="dark"] .notification-title-modern {
            color: #f1f5f9;
        }

        [data-theme="dark"] .notifications-title {
            color: #f1f5f9;
        }

        [data-theme="dark"] .hero-text p {
            color: #e2e8f0 !important;
        }

        /* Glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] .glass-card {
            background: rgba(30, 41, 59, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        /* Parallax */
        .parallax-hero {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        @media (max-width: 768px) {
            .parallax-hero {
                background-attachment: scroll;
            }
        }

        /* Scroll Snap - Full Page Sections */
        html {
            scroll-snap-type: y mandatory;
            scroll-behavior: smooth;
            overflow-y: scroll;
            height: 100%;
        }

        /* Dark Mode Toggle Button */
        #theme-toggle {
            min-width: 80px;
        }

        #theme-toggle:hover {
            background: rgba(255, 215, 0, 0.2) !important;
            color: #ffd700 !important;
        }

        /* Main sections with scroll snap */
        .hero-section,
        .news-section,
        .role-section {
            scroll-snap-align: start;
            scroll-snap-stop: always;
            min-height: 100%;
            /* Changed from 100vh - allows natural height */
            padding: 60px 0;
        }

        /* Override for smaller sections */
        section:not(.hero-section):not(.news-section):not(.role-section) {
            min-height: auto;
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

        .logo-text h2 {
            display: none;
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
            color: #fbbf24;
            background: rgba(251, 191, 36, 0.15);
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(251, 191, 36, 0.2);
            border-color: rgba(251, 191, 36, 0.5);
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

        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu-content {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            min-width: 280px;
            box-shadow: 0 12px 40px rgba(59, 130, 246, 0.2);
            border-radius: 15px;
            border: 1.5px solid rgba(59, 130, 246, 0.1);
            z-index: 1060;
            margin-left: 10px;
            padding: 15px 0;
            overflow: visible;
            opacity: 0;
            visibility: hidden;
            transform: translateX(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(20px);
        }

        .dropdown-submenu-content.show {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateX(0) !important;
        }

        .dropdown-submenu:hover .dropdown-submenu-content {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateX(0) !important;
        }

        .submenu-toggle {
            justify-content: space-between !important;
            font-weight: 600 !important;
            position: relative;
            cursor: pointer !important;
        }

        .submenu-toggle .bi-chevron-right {
            font-size: 14px;
            margin-left: auto;
            transition: all 0.3s ease;
            color: #3b82f6;
        }

        .dropdown-submenu:hover .submenu-toggle .bi-chevron-right,
        .submenu-toggle.active .bi-chevron-right {
            transform: rotate(90deg);
            color: #1e40af;
        }

        .khoa-item {
            padding: 10px 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            color: #0052a3 !important;
            text-decoration: none;
            border-radius: 10px;
            margin: 3px 10px;
            font-weight: 500;
            font-size: 13px;
            background: transparent;
            border: none;
            width: calc(100% - 20px);
            text-align: left;
        }

        .khoa-item:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(29, 78, 216, 0.04) 100%) !important;
            color: #1e40af !important;
            transform: translateX(3px);
        }

        .khoa-item i {
            margin-right: 8px;
            color: #3b82f6;
            font-size: 12px;
        }

        .khoa-item:hover i {
            color: #1e40af;
        }

        .login-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            color: white !important;
            padding: 10px 18px !important;
            border-radius: 25px !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            transition: all 0.3s ease !important;
            border: 2px solid transparent !important;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3) !important;
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

        .container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* ============ HERO BANNER ============ */
        .hero-section {
            background-image: linear-gradient(rgba(15, 23, 42, 0.3), rgba(15, 23, 42, 0.4)),
                url('img/backgourd234.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(ellipse at 20% 80%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(6, 182, 212, 0.08) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.9), transparent);
            pointer-events: none;
            z-index: 0;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: none;
            z-index: 0;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-30px);
            }
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1.7fr 1fr;
            gap: 20px;
            align-items: stretch;
            position: relative;
            z-index: 1;
            padding: 40px 0;
        }

        @media (max-width: 1200px) {
            .hero-content {
                gap: 40px;
                padding: 30px 0;
            }
        }

        @media (max-width: 992px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 50px;
            }

            .hero-text h1::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .hero-buttons {
                justify-content: center;
            }
        }

        .hero-text {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .hero-text h1 {
            font-size: 48px;
            font-weight: 900;
            margin-bottom: 24px;
            line-height: 1.15;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 40px rgba(2, 132, 199, 0.3);
            letter-spacing: -1px;
            position: relative;
            animation: fadeInDown 1s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-text h1::after {
            content: none;
        }

        /* Hero Info Card - Premium Modern Glassmorphism */
        .hero-top-tag {
            display: inline-flex;
            align-items: center;
            padding: 8px 18px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 30px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 20px;
            width: fit-content;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .hero-top-tag i {
            color: #ffffff !important;
        }

        .hero-info-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 56px 68px;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.25);
            margin-bottom: 28px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            flex: 1;
            width: 100%;
            min-height: 440px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero-info-card:hover {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.65) 0%, rgba(30, 41, 59, 0.55) 100%);
            border-color: rgba(245, 158, 11, 0.7);
            transform: translateY(-6px);
            box-shadow:
                0 30px 60px rgba(0, 0, 0, 0.45),
                0 0 30px rgba(245, 158, 11, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.35);
        }

        .hero-info-card h1 {
            font-size: 42px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.25;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            position: relative;
            padding-bottom: 16px;
            text-align: left;
        }

        .hero-info-card h1::after {
            content: none;
        }

        .hero-info-card .hero-desc-1 {
            color: #ffffff;
            font-size: 16px;
            font-weight: 500;
            line-height: 1.65;
            margin-bottom: 10px;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
        }

        .hero-info-card .hero-desc-2 {
            color: #e2e8f0;
            font-size: 16px;
            font-weight: 500;
            line-height: 1.65;
            margin-bottom: 0;
            opacity: 0.95;
        }

        .hero-features-chips {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .chip-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.1);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        .chip-item:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(245, 158, 11, 0.6);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .hero-info-card {
                padding: 20px;
            }
        }

        .hero-text p {
            font-size: 20px;
            line-height: 1.7;
            margin-bottom: 20px;
            opacity: 1;
            color: #ffffff;
            font-weight: 500;
            text-shadow: 0 1px 8px rgba(0, 0, 0, 0.6);
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .hero-text p:last-of-type {
            margin-bottom: 35px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-text p:last-of-type {
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #f59e0b 0%, #eab308 50%, #d97706 100%);
            color: #ffffff !important;
            padding: 18px 42px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow:
                0 10px 30px rgba(245, 158, 11, 0.4),
                0 0 20px rgba(251, 191, 36, 0.2);
            position: relative;
            overflow: hidden;
            font-size: 16px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            animation: heroButtonPulse 4s ease-in-out infinite;
        }

        @keyframes heroButtonPulse {

            0%,
            100% {
                box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4), 0 0 20px rgba(251, 191, 36, 0.2);
                transform: scale(1);
            }

            50% {
                box-shadow: 0 15px 40px rgba(245, 158, 11, 0.5), 0 0 30px rgba(251, 191, 36, 0.3);
                transform: scale(1.02);
            }
        }

        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.6s ease;
        }

        .btn-primary-custom i {
            color: #ffffff !important;
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .btn-primary-custom:hover::before {
            left: 100%;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
            color: #ffffff !important;
            transform: translateY(-4px) scale(1.04);
            box-shadow:
                0 20px 50px rgba(245, 158, 11, 0.5),
                0 0 40px rgba(251, 191, 36, 0.4);
        }

        .btn-primary-custom:hover i {
            color: #ffffff !important;
            transform: none;
        }

        .btn-primary-custom:active {
            transform: translateY(-2px) scale(1.02);
        }

        .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 18px 42px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            border: 2px solid rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            font-size: 16px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-secondary-custom i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .btn-secondary-custom:hover::before {
            left: 100%;
        }

        .btn-secondary-custom:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fef3c7;
            border-color: rgba(245, 158, 11, 0.8);
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 20px 50px rgba(255, 255, 255, 0.15);
        }

        .btn-secondary-custom:hover i {
            transform: none;
        }

        .hero-image {
            text-align: center;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: stretch;
            align-items: stretch;
            height: 100%;
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .hero-image::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(30, 58, 138, 0.2) 0%, rgba(59, 130, 246, 0.1) 50%, transparent 70%);
            animation: heroGlow 4s ease-in-out infinite;
            pointer-events: none;
            z-index: -1;
        }

        @keyframes heroGlow {

            0%,
            100% {
                opacity: 0.8;
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1.15);
            }
        }

        /* ============ GLASSMORPHISM ============ */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 8px 32px var(--card-shadow);
        }

        .glass-header {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--glass-border);
        }

        /* ============ PARALLAX EFFECT ============ */
        .parallax-hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.1)),
                url('img/backgourd234.png');
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            transition: background-attachment 0.3s ease;
        }

        @media (max-width: 768px) {
            .parallax-hero {
                background-attachment: scroll;
            }
        }

        /* ============ BEAUTIFUL NOTIFICATIONS FRAME ============ */
        .notifications-frame {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 25px;
            margin: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .notifications-frame::before {
            display: none;
        }

        .notifications-frame::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
            pointer-events: none;
        }

        .notifications-frame:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
        }

        .notifications-container {
            padding: 0;
            max-width: 100%;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .notifications-title {
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: center;
            justify-content: center;
            text-shadow: none;
            letter-spacing: -0.5px;
        }

        .notifications-title i {
            font-size: 24px;
            color: #1e3a8a;
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.15), rgba(59, 130, 246, 0.1));
            padding: 10px 12px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(30, 58, 138, 0.2);
            border: 1px solid rgba(30, 58, 138, 0.2);
        }

        .modern-notifications-card {
            width: 700px;
            background: #ffffff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            margin: 0 auto;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============ MODERN NOTIFICATIONS CARD ============ */
        .modern-notifications-card {
            width: 100%;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 24px;
            padding: 24px;
            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.18),
                0 8px 20px rgba(0, 0, 0, 0.06);
            margin: 0;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.8);
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .notifications-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .notifications-title {
            font-size: 20px;
            font-weight: 800;
            color: #dc2626;
            margin: 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .notification-count {
            background: #dc2626;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            min-width: 24px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 4px;
        }

        .notifications-list-modern {
            margin-bottom: 0;
            overflow-y: auto;
            padding: 4px 6px 4px 4px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            text-align: left;
        }

        .notifications-list-modern::-webkit-scrollbar {
            width: 5px;
        }

        .notifications-list-modern::-webkit-scrollbar-track {
            background: rgba(226, 232, 240, 0.6);
            border-radius: 10px;
        }

        .notifications-list-modern::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 10px;
        }

        .notifications-list-modern {
            scrollbar-width: thin;
            scrollbar-color: #ef4444 rgba(226, 232, 240, 0.6);
        }

        .notification-item-modern {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: inherit;
            position: relative;
            border-radius: 16px;
            background: rgba(220, 38, 38, 0.04);
            border: 1px solid rgba(220, 38, 38, 0.12);
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.05);
            text-align: left;
            width: 100%;
            box-sizing: border-box;
        }

        .notification-item-modern:hover {
            background: rgba(220, 38, 38, 0.08);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(220, 38, 38, 0.15);
            border-color: rgba(220, 38, 38, 0.25);
        }

        .notification-item-modern:active {
            transform: scale(0.99);
            background: rgba(220, 38, 38, 0.12);
        }

        .notification-icon-modern {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #fef2f2, #fecaca);
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            flex-shrink: 0;
            position: relative;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
            transition: all 0.3s ease;
            margin-top: 2px;
        }

        .notification-icon-modern i {
            font-size: 22px;
            transition: transform 0.3s ease;
        }

        .notification-item-modern:hover .notification-icon-modern {
            transform: scale(1.08) rotate(-4deg);
            box-shadow: 0 6px 18px rgba(220, 38, 38, 0.25);
        }

        .notification-icon-modern.new-notification::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid #ffffff;
            box-shadow: 0 0 6px rgba(239, 68, 68, 0.6);
            animation: dotPulse 1.5s ease-in-out infinite;
        }

        @keyframes dotPulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        .notification-content-modern {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
            gap: 4px;
        }

        .notification-title-modern {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            margin: 0;
            text-align: left;
            word-break: break-word;
            width: 100%;
        }

        .notification-body-modern {
            font-size: 13px;
            color: #475569;
            line-height: 1.45;
            margin: 2px 0 4px;
            text-align: left;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            width: 100%;
        }

        .notification-time-modern {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f1f5f9;
            padding: 3px 10px;
            border-radius: 12px;
            margin-top: 2px;
            text-align: left;
            width: fit-content;
            border: 1px solid #e2e8f0;
        }

        .notification-time-modern::before {
            content: "";
        }

        .btn-view-all-modern {
            margin-top: 16px;
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            color: #1a73e8;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.2s ease;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.6s forwards;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-view-all-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(26, 115, 232, 0.1), transparent);
            transition: left 0.5s ease;
            z-index: -1;
        }

        .btn-view-all-modern:hover {
            background: #f0f7ff;
            color: #1557b0;
            border-color: #1a73e8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
        }

        .btn-view-all-modern:hover::before {
            left: 100%;
        }

        .btn-view-all-modern:active {
            transform: scale(0.98);
            background: #1a73e8;
            color: white;
            border-color: #1a73e8;
            box-shadow: 0 2px 8px rgba(26, 115, 232, 0.3);
        }

        .btn-view-all-modern:active::before {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        }

        .btn-view-all-modern i {
            margin-right: 6px;
            z-index: 2;
            position: relative;
        }

        /* Responsive */
        @media (max-width: 860px) {
            .modern-notifications-card {
                width: calc(100% - 40px);
                margin: 15px 20px;
            }
        }

        @media (max-width: 480px) {
            .modern-notifications-card {
                width: calc(100% - 20px);
                margin: 10px 10px;
                padding: 16px;
            }

            .notifications-title {
                font-size: 15px;
            }

            .notification-item-modern {
                padding: 10px 0;
            }

            .notification-item-modern:hover {
                margin: 0 -10px;
                padding: 10px;
            }
        }

        .notifications-title i {
            font-size: 28px;
            color: #3b82f6;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 25px;
        }

        .notification-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .notification-link:hover {
            text-decoration: none;
            color: inherit;
        }

        .btn-view-all-clean {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white !important;
            border: none;
            padding: 14px 28px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            width: 100%;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-view-all-clean::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-view-all-clean:hover::before {
            left: 100%;
        }

        .btn-view-all-clean:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white !important;
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 12px 35px rgba(59, 130, 246, 0.4);
        }

        /* ============ IMPROVED NOTIFICATION STYLES ============ */
        .notification-item {
            margin-bottom: 6px;
            padding: 12px 16px;
            background: #ffffff;
            border: none;
            border-radius: 12px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 0.9rem;
            line-height: 1.4;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid transparent;
        }

        .notification-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            background: #ffffff;
            transform: translateX(6px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            border-color: rgba(0, 82, 163, 0.15);
        }

        .notification-item:last-child {
            margin-bottom: 0;
        }

        .notification-icon {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }

        .notification-title {
            font-weight: 600;
            color: #1e293b;
            line-height: 1.3;
            font-size: 0.9rem;
        }

        .notification-time {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }

        .notification-time i {
            font-size: 0.7rem;
        }

        .notification-badge {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .notification-badge.new {
            background: #ff3b30;
            color: white;
            animation: newBadgePulse 2s ease-in-out infinite;
        }

        @keyframes newBadgePulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 59, 48, 0.4);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 4px rgba(255, 59, 48, 0.1);
            }
        }

        /* Notification Types with Timeline Effect */
        .notification-general {
            border-left: 4px solid #2563eb;
        }

        .notification-general .notification-icon {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1d4ed8;
        }

        .notification-deadline {
            border-left: 4px solid #f97316;
        }

        .notification-deadline .notification-icon {
            background: linear-gradient(135deg, #fed7aa, #fdba74);
            color: #ea580c;
        }

        .notification-important {
            border-left: 4px solid #ef4444;
        }

        .notification-important .notification-icon {
            background: linear-gradient(135deg, #fecaca, #fca5a5);
            color: #dc2626;
        }

        .notification-guide {
            border-left: 4px solid #10b981;
        }

        .notification-guide .notification-icon {
            background: linear-gradient(135deg, #a7f3d0, #6ee7b7);
            color: #059669;
        }

        .notification-item:hover .notification-icon {
            transform: scale(1.1);
        }

        .notification-general:hover .notification-icon {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }

        .notification-deadline:hover .notification-icon {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
        }

        .notification-important:hover .notification-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .notification-guide:hover .notification-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-view-all {
            background: linear-gradient(135deg, #0052a3 0%, #3b82f6 100%);
            color: white !important;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 82, 163, 0.2);
            width: 100%;
            justify-content: center;
        }

        .btn-view-all:hover {
            background: linear-gradient(135deg, #003d7a 0%, #1e40af 100%);
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 82, 163, 0.25);
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ============ NEWS SECTION ============ */
        .news-section {
            padding: 80px 0 0 0;
            /* Background image is set inline */
            position: relative;
            overflow: hidden;
        }

        .news-section::before {
            display: none;
        }

        .news-section .container {
            position: relative;
            z-index: 1;
        }

        .news-section .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .news-section .section-title h2 {
            font-size: 38px;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: inline-flex;
            align-items: center;
            gap: 15px;
        }

        .news-section .section-title h2 i {
            color: #f59e0b;
            font-size: 32px;
        }

        .news-section .divider {
            display: none;
        }

        .news-section .news-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 20px 0;
            align-items: stretch;
        }

        @media (max-width: 1400px) {
            .news-section .news-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .news-section .news-grid {
                grid-template-columns: 1fr;
            }
        }

        .news-section .news-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            text-decoration: none;
            display: block;
        }

        .news-section .news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .news-section .news-card-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .news-section .news-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .news-section .news-card:hover .news-card-image img {
            transform: scale(1.1);
        }

        .news-section .news-card-content {
            padding: 25px;
        }

        .news-section .news-card-date {
            display: inline-block;
            background: #f0f9ff;
            color: #0369a1;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .news-section .news-card-date i {
            margin-right: 5px;
        }

        .news-section .news-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .news-section .news-card-text {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
        }

        .news-section .featured-card {
            border: 2px solid #f59e0b;
        }

        .news-section .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        }

        /* News card link */
        .news-card-link {
            text-decoration: none;
            display: block;
        }

        .news-card-link:hover {
            transform: translateY(-10px);
        }

        /* Better card styling */
        .news-section .news-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .news-section .news-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
        }

        .news-section .news-card-image {
            position: relative;
            height: 150px;
            overflow: hidden;
        }

        .news-section .news-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .news-section .news-card:hover .news-card-image img {
            transform: scale(1.15);
        }

        .news-section .news-card-content {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .news-section .news-card-date {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            padding: 10px 18px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 15px;
            margin-bottom: 15px;
            width: fit-content;
        }

        .news-section .news-card-date i {
            font-size: 14px;
        }

        .news-section .news-card-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 15px;
            line-height: 1.4;
            transition: color 0.3s ease;
        }

        .news-card-link:hover .news-card-title {
            color: #3b82f6;
        }

        .news-section .news-card-text {
            font-size: 15px;
            color: #64748b;
            line-height: 1.7;
            flex: 1;
        }

        /* Featured card special styling */
        .news-section .featured-card {
            border: none;
            position: relative;
        }

        .news-section .featured-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #f59e0b, #ef4444, #3b82f6);
        }

        /* Responsive news section */
        @media (max-width: 991px) {
            .news-section .section-title h2 {
                font-size: 28px;
            }

            .news-section .news-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .news-section .section-title h2 {
                font-size: 22px;
            }

            .news-section .news-card-image {
                height: 180px;
            }
        }

        .news-section::before {
            display: none;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            z-index: 1;
        }

        .section-title h2 {
            font-size: 38px;
            font-weight: 900;
            color: #0052a3;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .section-title h2::after {
            content: none;
        }

        .section-title .divider {
            display: none;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            align-items: stretch;
            position: relative;
            z-index: 1;
        }


        /* Modern Glassmorphism Cards */
        .news-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
        }

        .news-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0052a3, #003d7a);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .news-card:hover::before {
            transform: scaleX(1);
        }

        .news-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 60px rgba(0, 82, 163, 0.2);
            border-color: rgba(0, 82, 163, 0.2);
        }

        .news-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        .news-card-link:hover {
            text-decoration: none;
            color: inherit;
        }

        .featured-card {
            position: relative;
        }

        .featured-card::before {
            display: none;
        }

        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 700;
            z-index: 2;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            animation: featuredPulse 2s ease-in-out infinite;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @keyframes featuredPulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 6px 25px rgba(255, 107, 107, 0.5);
            }
        }

        .featured-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 60px rgba(255, 107, 107, 0.25);
        }

        .news-card-image {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
            overflow: hidden;
            position: relative;
        }

        .news-card-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(to top, rgba(255, 255, 255, 0.8), transparent);
        }

        .news-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .news-card:hover .news-card-image img {
            transform: scale(1.12);
        }

        .news-card-content {
            padding: 28px;
            flex: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 1));
        }

        .news-card-date {
            font-size: 13px;
            color: #0052a3;
            margin-bottom: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: rgba(0, 82, 163, 0.08);
            border-radius: 20px;
            width: fit-content;
        }

        .news-card-date i {
            font-size: 12px;
        }

        .news-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #0052a3;
            margin-bottom: 14px;
            line-height: 1.4;
            transition: color 0.3s ease;
            min-height: 52px;
            display: flex;
            align-items: center;
        }

        .news-card:hover .news-card-title {
            color: #003d7a;
        }

        .news-card-text {
            font-size: 14px;
            color: #555;
            line-height: 1.7;
            flex: 1;
        }

        /* ============ ROLE CARDS EFFECT ============ */
        .role-section {
            /* Background image is set inline */
            position: relative;
        }

        .role-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.85) 100%);
            z-index: 0;
        }

        .role-section .container {
            position: relative;
            z-index: 1;
        }

        .role-section .text-center h2 {
            font-size: 42px;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .role-section .text-center p {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 50px;
            font-weight: 500;
        }

        /* Card styling */
        .role-section .card {
            border: none !important;
            border-top: none !important;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            background: white;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            height: 100%;
            position: relative;
        }

        .role-section .card::before,
        .role-section .card::after {
            display: none !important;
            content: none !important;
        }

        .role-section .card:hover {
            border: none !important;
            border-top: none !important;
            transform: translateY(-15px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
        }

        .role-section .card:hover::before,
        .role-section .card:hover::after {
            display: none !important;
            content: none !important;
        }

        .role-section .sinh-vien {
            border-top: none;
        }

        .role-section .giang-vien {
            border-top: none;
        }

        .role-section .lanh-dao {
            border-top: none;
        }

        .role-section .card-body {
            padding: 30px 25px;
            text-align: center;
        }

        .role-section .card-body>div:first-child {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            transition: all 0.4s ease;
        }

        .role-section .card-body>div:first-child i {
            font-size: 32px;
        }

        .role-section .sinh-vien .card-body>div:first-child i {
            color: #10b981;
        }

        .role-section .giang-vien .card-body>div:first-child i {
            color: #3b82f6;
        }

        .role-section .lanh-dao .card-body>div:first-child i {
            color: #f59e0b;
        }

        .role-section .card:hover .card-body>div:first-child {
            transform: scale(1.1) rotate(5deg);
        }

        .role-section .card-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .role-section .card-body ul {
            margin-bottom: 20px;
            padding: 0;
            list-style: none;
        }

        .role-section .card-body ul li {
            padding: 8px 0;
            color: #64748b;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            position: relative;
            padding-left: 25px;
        }

        .role-section .card-body ul li:last-child {
            border-bottom: none;
        }

        .role-section .card-body ul li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
        }

        .role-section .btn {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            transition: all 0.3s ease;
        }

        .role-section .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .role-section .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .role-section .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .role-section .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .role-section .text-center h2 {
                font-size: 32px;
            }

            .role-section .card-body {
                padding: 30px 20px;
            }
        }

        @media (max-width: 576px) {
            .role-section .text-center h2 {
                font-size: 26px;
            }

            .role-section .text-center p {
                font-size: 16px;
            }
        }

        .role-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(0, 82, 163, 0.05) 0%, transparent 70%);
            animation: float 8s ease-in-out infinite;
        }

        .role-section .text-center h2 {
            font-size: 42px;
            font-weight: 900;
            color: #1e3a8a;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            display: inline-block;
            text-shadow: 2px 2px 4px rgba(245, 158, 11, 0.2);
        }

        .role-section .text-center h2::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            border-radius: 10px;
        }

        .role-section .text-center p {
            font-size: 16px;
            color: #64748b;
            margin-top: 30px;
            position: relative;
            z-index: 1;
        }

        /* Modern Glassmorphism Cards */
        .role-section .card {
            border: none;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 82, 163, 0.1);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .role-section .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--role-color-start, #3b82f6), var(--role-color-end, #1d4ed8));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .role-section .card:hover::before {
            transform: scaleX(1);
        }

        .role-section .card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.6s ease;
        }

        .role-section .card:hover::after {
            left: 100%;
        }

        .role-section .card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 30px 60px rgba(0, 82, 163, 0.2);
        }

        /* Role-specific colors */
        .role-section .card.sinh-vien {
            --role-color-start: #10b981;
            --role-color-end: #059669;
        }

        .role-section .card.giang-vien {
            --role-color-start: #3b82f6;
            --role-color-end: #1d4ed8;
        }

        .role-section .card.lanh-dao {
            --role-color-start: #f59e0b;
            --role-color-end: #d97706;
        }

        .role-section .card-body {
            padding: 45px 35px;
            position: relative;
            z-index: 1;
        }

        .role-section .card-body>div:first-child {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, var(--role-color-start, #3b82f6), var(--role-color-end, #1d4ed8));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(0, 82, 163, 0.3);
            position: relative;
        }

        .role-section .card-body>div:first-child::after {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--role-color-start, #3b82f6), var(--role-color-end, #1d4ed8));
            opacity: 0.3;
            z-index: -1;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.3;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.1;
            }
        }

        .role-section .card:hover .card-body>div:first-child {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 15px 40px rgba(0, 82, 163, 0.4);
        }

        .role-section .card-title {
            font-size: 22px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 1px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .role-section .card-body ul {
            margin: 0 0 30px 0 !important;
        }

        .role-section .card-body ul li {
            font-size: 15px;
            color: #555;
            margin-bottom: 14px;
            padding-left: 28px;
            line-height: 1.6;
            font-weight: 500;
            position: relative;
        }

        .role-section .card-body ul li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--role-color-start, #10b981);
            font-weight: bold;
            font-size: 14px;
        }

        .role-section .sinh-vien .card-body ul li::before {
            color: #10b981;
        }

        .role-section .giang-vien .card-body ul li::before {
            color: #3b82f6;
        }

        .role-section .lanh-dao .card-body ul li::before {
            color: #f59e0b;
        }

        .role-section .btn {
            font-weight: 700;
            padding: 16px 32px;
            border-radius: 50px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            font-size: 15px;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }

        .role-section .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .role-section .btn:hover::before {
            left: 100%;
        }

        .role-section .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white !important;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }

        .role-section .btn-primary:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5);
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }

        .role-section .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        }

        .role-section .btn-success:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.5);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .role-section .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white !important;
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
        }

        .role-section .btn-warning:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 15px 40px rgba(245, 158, 11, 0.5);
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        }

        /* Icon animation on hover */
        .role-section .card:hover .card-body>div:first-child i {
            animation: bounce 0.6s ease;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* Responsive improvements */
        @media (max-width: 991px) {
            .role-section .card-body {
                padding: 35px 25px;
            }

            .role-section .card-body>div:first-child {
                width: 85px;
                height: 85px;
                font-size: 40px;
            }

            .role-section .text-center h2 {
                font-size: 32px;
            }
        }

        @media (max-width: 576px) {
            .role-section {
                padding: 50px 0;
            }

            .role-section .text-center h2 {
                font-size: 26px;
            }

            .role-section .text-center p {
                font-size: 14px;
            }
        }

        /* ============ FEATURES SECTION ============ */
        .features-section {
            padding: 80px 0;
            /* Background image is set inline */
            position: relative;
            overflow: hidden;
        }

        .features-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(0, 82, 163, 0.08) 0%, transparent 70%);
            animation: float 8s ease-in-out infinite;
        }

        .features-title {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .features-title h2 {
            font-size: 32px;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 0;
        }

        .features-subtitle {
            text-align: center;
            font-size: 16px;
            color: #64748b;
            margin-bottom: 50px;
            position: relative;
            z-index: 1;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
            position: relative;
            z-index: 1;
        }

        .feature-card {
            text-align: center;
            padding: 40px 30px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid rgba(59, 130, 246, 0.1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #eab308);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            box-shadow: 0 25px 60px rgba(59, 130, 246, 0.2);
            transform: translateY(-12px);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .feature-icon {
            width: 85px;
            height: 85px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, #f59e0b, #eab308);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
            position: relative;
        }

        .feature-icon::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #f59e0b, #eab308, #f59e0b);
            border-radius: 50%;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.15) rotate(-10deg);
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            box-shadow: 0 15px 40px rgba(245, 158, 11, 0.5);
        }

        .feature-card:hover .feature-icon::before {
            opacity: 1;
        }

        .feature-title {
            font-size: 19px;
            font-weight: 800;
            color: #1e40af;
            margin-bottom: 15px;
            letter-spacing: -0.3px;
        }

        .feature-text {
            font-size: 15px;
            color: #64748b;
            line-height: 1.7;
            font-weight: 500;
        }

        /* ============ FOOTER ============ */
        .footer {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #1d4ed8 100%);
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
            border-top: 4px solid #fbbf24;
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
            color: #fbbf24;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-contact a:hover {
            color: #fef3c7;
        }

        .footer-divider {
            background: linear-gradient(90deg, #fbbf24, transparent) !important;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .nav-menu {
                gap: 0;
                padding: 10px 0;
            }

            .nav-item {
                padding: 6px 12px;
                font-size: 13px;
            }

            .login-btn {
                padding: 10px 18px !important;
                font-size: 13px !important;
                border-radius: 20px !important;
            }

            .login-btn i {
                font-size: 14px;
                margin-right: 6px;
            }

            .hero-content {
                grid-template-columns: 1fr;
            }

            .notifications-frame {
                margin: 15px 10px;
                padding: 20px;
                border-radius: 16px;
            }

            .notifications-container {
                padding: 0;
            }

            .notifications-title {
                font-size: 22px;
                margin-bottom: 20px;
            }

            .notifications-title i {
                font-size: 24px;
                padding: 8px;
            }

            .notifications-list {
                gap: 5px;
                margin-bottom: 20px;
            }

            .notification-item {
                margin-bottom: 5px;
                padding: 10px 14px;
                gap: 10px;
            }

            .notification-icon {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }

            .notification-title {
                font-size: 0.85rem;
            }

            .notification-time {
                font-size: 0.7rem;
            }

            .notification-badge {
                font-size: 10px;
                padding: 2px 6px;
            }

            .btn-view-all-clean {
                padding: 12px 20px;
                font-size: 0.85rem;
            }

            .btn-view-all {
                padding: 10px 20px;
                font-size: 0.85rem;
            }

            .header-top {
                flex-direction: column;
                gap: 10px;
            }

            .hero-text h1 {
                font-size: 32px;
            }

            .features-title h2 {
                font-size: 24px;
            }

            .section-title h2 {
                font-size: 28px;
            }

            .nav-menu {
                gap: 0;
                padding: 10px 0;
            }

            .nav-item {
                padding: 8px 14px;
                font-size: 13px;
            }

            .login-btn {
                padding: 10px 18px !important;
                font-size: 13px !important;
                border-radius: 20px !important;
            }

            .login-btn i {
                font-size: 14px;
                margin-right: 6px;
            }

            .news-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-content {
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }

            .footer-section {
                padding: 24px;
            }

            .footer-section h4 {
                font-size: 15px;
            }

            .social-links a {
                width: 42px;
                height: 42px;
            }
        }

        @media (max-width: 576px) {
            .logo-university {
                font-size: 13px;
            }

            .logo-img {
                width: 55px;
                height: 55px;
            }

            .nav-menu {
                gap: 4px;
            }

            .nav-item {
                padding: 5px 10px;
                font-size: 12px;
            }

            .login-btn {
                padding: 8px 14px !important;
                font-size: 12px !important;
            }

            .notifications-frame {
                margin: 10px 8px;
                padding: 16px;
                border-radius: 14px;
            }

            .notifications-container {
                padding: 0;
            }

            .notifications-title {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .notifications-title i {
                font-size: 20px;
                padding: 6px;
            }

            .notifications-list {
                gap: 4px;
                margin-bottom: 15px;
            }

            .notification-item {
                margin-bottom: 4px;
                padding: 8px 12px;
                gap: 8px;
            }

            .notification-icon {
                width: 28px;
                height: 28px;
                font-size: 0.8rem;
            }

            .notification-title {
                font-size: 0.8rem;
                line-height: 1.2;
            }

            .notification-time {
                font-size: 0.65rem;
            }

            .notification-badge {
                font-size: 9px;
                padding: 1px 4px;
            }

            .notification-header {
                gap: 6px;
            }

            .btn-view-all-clean {
                padding: 10px 16px;
                font-size: 0.8rem;
            }

            .btn-view-all {
                padding: 8px 16px;
                font-size: 0.8rem;
            }

            .hero-text h1 {
                font-size: 26px;
            }

            .hero-text p {
                font-size: 15px;
            }

            .hero-buttons {
                flex-direction: column;
                gap: 12px;
            }

            .btn-primary-custom,
            .btn-secondary-custom {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .btn-primary-custom {
                padding: 14px 32px;
                font-size: 15px;
                border-radius: 40px;
            }

            .btn-primary-custom i {
                font-size: 16px;
            }

            .btn-secondary-custom {
                padding: 14px 32px;
                font-size: 15px;
                border-radius: 40px;
            }

            .btn-secondary-custom i {
                font-size: 16px;
            }

            .section-title h2 {
                font-size: 22px;
            }

            .features-title h2 {
                font-size: 20px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 25px;
            }

            .footer-section {
                padding: 20px;
            }

            .footer-section h4 {
                font-size: 14px;
            }

            .footer-section p {
                font-size: 14px;
            }

            .footer-section ul li a {
                font-size: 14px;
            }

            .footer-contact {
                font-size: 14px;
                gap: 12px;
            }

            .social-links a {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .news-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <?php
    // Hiển thị flash messages
    $success = getFlashMessage('success');
    $error = getFlashMessage('error');
    if ($success || $error): ?>
        <div class="container mt-3">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- HEADER -->
    <header class="top-header">
        <div class="container">
            <!-- Header Top Info -->

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

                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php" class="nav-item"><i class="bi bi-house"></i> Trang chủ</a></li>
                        <li class="dropdown">
                            <a href="thong_bao_do_an.php" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-bell"></i> Thông báo
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="thong_bao_do_an.php"><i
                                            class="bi bi-bell me-2"></i>Thông báo đồ án</a></li>
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
                                        href="https://drive.google.com/drive/folders/14YYeRXbl-QcB6zfoKyekvNxuw5eweDqg?usp=sharing"
                                        target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Biểu mẫu cho sinh
                                        viên</a></li>
                                <li><a class="dropdown-item"
                                        href="https://drive.google.com/drive/folders/1_dzHtqpalK08Ri6hixv_CDGLRVyzT3Hg?usp=sharing"
                                        target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Biểu mẫu cho giảng
                                        viên</a></li>
                            </ul>
                        </li>
                        
                        <li><a href="auth/login.php" class="nav-item login-btn"><i class="bi bi-box-arrow-in-right"></i>
                                Đăng nhập</a></li>

                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- HERO BANNER WITH PARALLAX -->
    <section class="hero-section parallax-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-info-card">
                        <div class="hero-top-tag">
                            <i class="bi bi-mortarboard-fill me-2"></i> Tra Vinh University
                        </div>
                        <h1>Hệ Thống Quản Lý Đề Tài</h1>
                        <p class="hero-desc-1">
                            Nền tảng hỗ trợ sinh viên đăng ký đề tài, nộp báo cáo và theo dõi tiến độ thực hiện.
                        </p>
                        <p class="hero-desc-2">
                            Giảng viên dễ dàng quản lý, đánh giá và phản hồi trên một hệ thống thống nhất.
                        </p>
                        <div class="hero-features-chips">
                            <span class="chip-item"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Đăng
                                ký online</span>
                            <span class="chip-item"><i class="bi bi-shield-check text-success me-1"></i> Duyệt nhanh
                                chóng</span>
                            <span class="chip-item"><i class="bi bi-graph-up-arrow text-info me-1"></i> Theo dõi tiến
                                độ</span>
                        </div>
                        <div class="hero-features-chips">
                            <p class="hero-desc-1">
                                Phát triển bởi: Lâm Nhật Hào + Mai Hồng Lợi (DA22TTD).
                            </p>
                        </div>
                    </div>
                    <div class="hero-buttons">
                        
                         <a href="khoa_cntt.php" class="btn-primary-custom">
                            Thông tin Giảng viên
                        </a>
                        <a href="danh_sach_de_tai_cong_khai.php" class="btn-secondary-custom">
                            Danh sách Đề tài
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <!-- Modern Notifications Card with Glassmorphism -->
                    <div class="modern-notifications-card glass-card">
                        <div class="notifications-header">
                            <h2 class="notifications-title">Thông Báo Mới Nhất <span
                                    class="notification-count"><?php echo $soThongBao; ?></span></h2>
                        </div>

                        <div class="notifications-list-modern">
                            <?php if (!empty($dsThongBao)): ?>
                                <?php foreach ($dsThongBao as $index => $tb): ?>
                                    <?php
                                    $link = 'thong_bao_do_an.php';
                                    $isNew = $index === 0;
                                    $icon = 'bi-megaphone-fill';
                                    if ($tb['loai'] === 'success')
                                        $icon = 'bi-mortarboard-fill';
                                    elseif ($tb['loai'] === 'warning')
                                        $icon = 'bi-file-earmark-text-fill';
                                    elseif ($tb['loai'] === 'danger')
                                        $icon = 'bi-calendar-event-fill';

                                    // Hiển thị ngày bắt đầu và kết thúc nếu có
                                    $dateRange = '';
                                    if (!empty($tb['ngay_bat_dau']) && !empty($tb['ngay_ket_thuc'])) {
                                        $ngayBatDau = date('d/m/Y', strtotime($tb['ngay_bat_dau']));
                                        $ngayKetThuc = date('d/m/Y', strtotime($tb['ngay_ket_thuc']));
                                        $dateRange = 'Từ ngày ' . $ngayBatDau . ' đến ngày ' . $ngayKetThuc;
                                    } elseif (!empty($tb['ngay_bat_dau'])) {
                                        $dateRange = 'Từ ngày ' . date('d/m/Y', strtotime($tb['ngay_bat_dau']));
                                    } elseif (!empty($tb['ngay_ket_thuc'])) {
                                        $dateRange = 'Đến ngày ' . date('d/m/Y', strtotime($tb['ngay_ket_thuc']));
                                    } else {
                                        // Nếu không có ngày, hiển thị thời gian tạo
                                        $thoigian = strtotime($tb['created_at']);
                                        $hienTai = time();
                                        $diff = $hienTai - $thoigian;
                                        if ($diff < 3600)
                                            $dateRange = round($diff / 60) . ' phút trước';
                                        elseif ($diff < 86400)
                                            $dateRange = round($diff / 3600) . ' giờ trước';
                                        elseif ($diff < 604800)
                                            $dateRange = round($diff / 86400) . ' ngày trước';
                                        else
                                            $dateRange = date('d/m/Y', $thoigian);
                                    }
                                    ?>
                                    <!--<a href="#<?php //echo $link; ?>" class="notification-item-modern">-->
                            		<div class="notification-item-modern">
                                        <div class="notification-icon-modern <?php echo $isNew ? 'new-notification' : ''; ?>">
                                            <i class="bi <?php echo $icon; ?>"></i>
                                        </div>
                                        <div class="notification-content-modern">
                                            <div class="notification-title-modern">
                                                <?php echo htmlspecialchars($tb['tieu_de']); ?>
                                            </div>
                                            <?php if (!empty($tb['noi_dung'])): ?>
                                            <div class="notification-body-modern">
                                                <?php echo nl2br(htmlspecialchars(mb_strimwidth($tb['noi_dung'], 0, 180, '...'))); ?>
                                            </div>
                                            <?php endif; ?>
                                            <div class="notification-time-modern">
                                                <?php if (!empty($tb['ngay_bat_dau']) || !empty($tb['ngay_ket_thuc'])): ?>
                                                    <i class="bi bi-calendar-range me-1"></i>
                                                <?php endif; ?>
                                                <?php echo $dateRange; ?>
                                            </div>
                                        </div>
                                    <!--</a>-->
                           			</div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ROLE CARDS SECTION -->
    <section class="role-section"
        style="padding:80px 0; background: url('img/backgourd234.png') no-repeat center center/cover;">
        <div class="container">
            <!-- TIÊU ĐỀ -->
            <div class="text-center mb-5">
                <h2
                    style="color: #0052a3; font-weight: 800; font-size: 2.4rem; letter-spacing: -0.5px; position: relative;">
                    Đăng Nhập Hệ Thống</h2>
                <style>
                    .role-section h2::after,
                    .role-section h2::before {
                        content: none !important;
                        display: none !important;
                    }
                </style>
                <p style="color: #0052a3; font-size: 1.05rem; font-weight: 500; margin-top: 10px;">
                    Chọn đúng vai trò để truy cập hệ thống quản lý đề tài</p>
            </div>
            <div class="row g-4">
                <!-- Sinh Viên -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0"
                        style="border-radius: 20px; transition: all 0.3s ease; background: linear-gradient(145deg, #ffffff, #f8f9fa);">
                        <div class="card-body text-center p-4">
                            <div
                                style="width: 110px; height: 110px; margin: 0 auto 20px; background: linear-gradient(135deg, #28a745, #20c997); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(40, 167, 69, 0.35);">
                                <i class="bi bi-mortarboard"
                                    style="font-size: 56px; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"></i>
                            </div>
                            <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 1.3rem;">SINH VIÊN</h5>
                            <ul class="text-start list-unstyled mb-3" style="font-size: 0.9rem;">
                                <li class="mb-2">Xem danh sách đề
                                    tài đã duyệt</li>
                                <li class="mb-2">Đăng ký đề tài mình
                                    mong muốn</li>
                                <li class="mb-2">Theo dõi kết quả
                                    đăng ký đề tài</li>
                            </ul>
                            <a href="auth/login.php?role=sinh_vien" class="btn btn-lg w-100 fw-bold"
                                style="background: linear-gradient(135deg, #28a745, #20c997); border: none; border-radius: 12px; color: white; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3); transition: all 0.3s ease;">
                                <i class="bi bi-box-arrow-in-right me-2"></i>TRUY CẬP HỆ THỐNG
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Giảng Viên -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0"
                        style="border-radius: 20px; transition: all 0.3s ease; background: linear-gradient(145deg, #ffffff, #f8f9fa);">
                        <div class="card-body text-center p-4">
                            <div
                                style="width: 110px; height: 110px; margin: 0 auto 20px; background: linear-gradient(135deg, #0d6efd, #0dcaf0); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);">
                                <i class="bi bi-person-badge"
                                    style="font-size: 56px; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"></i>
                            </div>
                            <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 1.3rem;">GIẢNG VIÊN</h5>
                            <ul class="text-start list-unstyled mb-3" style="font-size: 0.9rem;">
                                <li class="mb-2">Tạo đề tài cơ sở
                                    ngành và chuyên ngành</li>
                                <li class="mb-2">Quản lý sinh viên
                                    đăng ký đề tài</li>
                                <li class="mb-2">Theo dõi trạng thái
                                    duyệt đề tài</li>
                            </ul>
                            <a href="auth/login.php?role=giang_vien" class="btn btn-lg w-100 fw-bold"
                                style="background: linear-gradient(135deg, #0d6efd, #0dcaf0); border: none; border-radius: 12px; color: white; box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3); transition: all 0.3s ease;">
                                <i class="bi bi-box-arrow-in-right me-2"></i>TRUY CẬP HỆ THỐNG
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Lãnh đạo -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0"
                        style="border-radius: 20px; transition: all 0.3s ease; background: linear-gradient(145deg, #ffffff, #f8f9fa);">
                        <div class="card-body text-center p-4">
                            <div
                                style="width: 110px; height: 110px; margin: 0 auto 20px; background: linear-gradient(135deg, #ffc107, #fd7e14); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(255, 193, 7, 0.35);">
                                <i class="bi bi-shield-lock"
                                    style="font-size: 56px; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"></i>
                            </div>
                            <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 1.3rem;">LÃNH ĐẠO KHOA</h5>
                            <ul class="text-start list-unstyled mb-3" style="font-size: 0.9rem;">
                                <li class="mb-2">Duyệt đề tài của
                                    giảng viên</li>
                                <li class="mb-2">Phân công cho giảng
                                    viên</li>
                                <li class="mb-2">Xem báo cáo và
                                    thống kê</li>
                            </ul>
                            <a href="auth/login.php?role=lanh_dao" class="btn btn-lg w-100 fw-bold"
                                style="background: linear-gradient(135deg, #ffc107, #fd7e14); border: none; border-radius: 12px; color: white; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); transition: all 0.3s ease;">
                                <i class="bi bi-box-arrow-in-right me-2"></i>TRUY CẬP HỆ THỐNG
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
                }

                .btn:hover {
                    transform: scale(1.02);
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2) !important;
                }
            </style>
        </div>
    </section>
  
    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- Left Section - Info -->
                <div class="col-md-8">
                    <div class="footer-title mb-3">
                        <h4>Khoa Công nghệ thông tin - Đại học Trà Vinh</h4>
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
                            <a href="mailto:ktcn@tvu.edu.vn">sit@tvu.edu.vn</a>
                        </p>
                        <p class="mb-3">
                            <i class="bi bi-globe me-2"></i>
                            <a href="https://cet.tvu.edu.vn/index.php/giang-vien/thong-tin-giang-vien/khoa-cong-nghe-thong-tin" target="_blank">https://cet.tvu.edu.vn</a>
                        </p>
                    </div>

                    <div class="footer-brand d-flex align-items-center gap-3">
                       <div class="brand-line"
                            style="width: 150px; height: 2px; background: linear-gradient(90deg, #ffcc00 0%, transparent 100%);">
                        </div>
                        <span style="font-weight: 600; color: #ffcc00;">Tra Vinh Univerity</span>
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
<!--
    <script>
        // Enhanced click effect for notifications
        document.addEventListener('DOMContentLoaded', function () {
            const notificationItems = document.querySelectorAll('.notification-item-modern');

            notificationItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    // Add clicked class for enhanced effect
                    this.classList.add('clicked');

                    // Create ripple effect
                    const ripple = document.createElement('div');
                    ripple.classList.add('ripple-effect');

                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';

                    this.appendChild(ripple);

                    // Remove ripple and clicked class after animation
                    setTimeout(() => {
                        this.classList.remove('clicked');
                        if (ripple.parentNode) {
                            ripple.parentNode.removeChild(ripple);
                        }
                    }, 600);
                });
            });

            // Click effect for view all button
            const viewAllBtn = document.querySelector('.btn-view-all-modern');
            if (viewAllBtn) {
                viewAllBtn.addEventListener('click', function (e) {
                    this.style.transform = 'translateY(0) scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            }
        });
    </script>
-->
    <script>
        // ============ DARK MODE TOGGLE ============
        function toggleDarkMode() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('theme-icon');
            const themeText = document.getElementById('theme-text');

            // Toggle dark mode
            if (html.getAttribute('data-theme') === 'dark') {
                html.setAttribute('data-theme', 'light');
                themeIcon.className = 'bi bi-moon-fill';
                themeText.textContent = 'Dark';
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                themeIcon.className = 'bi bi-sun-fill';
                themeText.textContent = 'Light';
                localStorage.setItem('theme', 'dark');
            }
        }

        // Check saved theme on page load
        document.addEventListener('DOMContentLoaded', function () {
            const savedTheme = localStorage.getItem('theme');
            const html = document.documentElement;
            const themeIcon = document.getElementById('theme-icon');
            const themeText = document.getElementById('theme-text');

            // Check system preference if no saved theme
            if (!savedTheme) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    html.setAttribute('data-theme', 'dark');
                    themeIcon.className = 'bi bi-sun-fill';
                    themeText.textContent = 'Light';
                }
            } else if (savedTheme === 'dark') {
                html.setAttribute('data-theme', 'dark');
                themeIcon.className = 'bi bi-sun-fill';
                themeText.textContent = 'Light';
            }
        });
    </script>

    <script>
        // handle submenu toggle for thông tin giảng viên
        document.addEventListener('DOMContentLoaded', function () {
            var infoLink = document.getElementById('info-giangvien');
            if (infoLink) {
                infoLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var list = document.querySelector('.dropdown-submenu-content');
                    if (list) { list.classList.toggle('show'); }
                });
            }

            // close any open submenu when clicking outside
            document.addEventListener('click', function (e) {
                var sub = document.querySelector('.dropdown-submenu-content.show');
                if (sub && !sub.contains(e.target) && e.target.id !== 'info-giangvien') {
                    sub.classList.remove('show');
                }
            });
        });
    </script>

</body>

</html>