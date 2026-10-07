<?php
session_start();
require_once __DIR__ . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin nhân sự Khoa Công nghệ thông tin </title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">

    <style>
        /* ============ DARK MODE CSS VARIABLES ============ */
        :root {
            --bg-primary: #f5f7fa;
            --bg-secondary: #ffffff;
            --text-primary: #333;
            --text-secondary: #555;
            --text-muted: #6c757d;
            --card-bg: #ffffff;
            --card-shadow: rgba(0, 0, 0, 0.08);
            --border-color: #e9ecef;
        }

        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --card-bg: #1e293b;
            --card-shadow: rgba(0, 0, 0, 0.3);
            --border-color: #334155;
        }

        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        [data-theme="dark"] .faculty-card {
            background: var(--card-bg);
            box-shadow: 0 4px 20px var(--card-shadow);
        }

        [data-theme="dark"] .faculty-name,
        [data-theme="dark"] .faculty-position {
            color: #3b82f6;
        }

        [data-theme="dark"] .faculty-info span,
        [data-theme="dark"] .faculty-phone,
        [data-theme="dark"] .faculty-email-info {
            color: var(--text-secondary);
        }

        [data-theme="dark"] .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        [data-theme="dark"] .page-title {
            color: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f4f8 100%);
            min-height: 100vh;
            color: #333;
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

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 8px;
        }

        .header-top-left,
        .header-top-right {
            display: flex;
            gap: 20px;
            font-size: 13px;
        }

        .header-top-left a,
        .header-top-right a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .header-top-left a:hover,
        .header-top-right a:hover {
            color: #ffcc00;
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

        /* ============ PAGE CONTENT ============ */
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 30px 0 20px 0;
        }

        .breadcrumb-item a {
            color: #0052a3;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: #003d7a;
        }

        .breadcrumb-item.active {
            color: #7f8c8d;
            font-weight: 600;
        }

        .page-title {
            color: #0052a3;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: -0.5px;
        }

        /* ============ FILTER TABS ============ */
        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 12px 28px;
            border: 2px solid #0052a3;
            border-radius: 30px;
            background: transparent;
            color: #0052a3;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-tab:hover {
            background: rgba(0, 82, 163, 0.1);
            transform: translateY(-2px);
        }

        .filter-tab.active {
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(0, 82, 163, 0.3);
        }

        .filter-tab i {
            font-size: 16px;
        }

        /* ============ BACK TO TOP BUTTON ============ */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            cursor: pointer;
            box-shadow: 0 6px 25px rgba(0, 82, 163, 0.35);
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(0, 82, 163, 0.45);
        }

        /* ============ MEMBER COUNT BADGE ============ */
        .member-count {
            text-align: center;
            margin-bottom: 25px;
            font-size: 15px;
            color: #6c757d;
        }

        .member-count span {
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* ============ NO RESULTS MESSAGE ============ */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-results i {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .no-results h4 {
            color: #495057;
            margin-bottom: 10px;
        }

        /* ============ FACULTY CARDS ============ */
        .faculty-card {
            background: white;
            border: none;
            border-radius: 18px;
            padding: 35px 30px;
            margin-bottom: 30px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            min-height: 480px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
        }

        .faculty-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 82, 163, 0.05) 0%, transparent 100%);
            pointer-events: none;
        }

        .faculty-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 82, 163, 0.15);
            border-top: 3px solid #0052a3;
        }

        .faculty-avatar {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%) !important;
            border: 5px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white !important;
            font-size: 3.5rem;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 82, 163, 0.3);
            flex-shrink: 0;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .faculty-card:hover .faculty-avatar {
            transform: scale(1.05);
        }

        .faculty-avatar i {
            margin: 0;
            padding: 0;
            line-height: 1;
        }

        .faculty-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .faculty-name {
            font-weight: 700;
            color: #0052a3;
            margin-bottom: 12px;
            font-size: 1.15rem;
            line-height: 1.3;
            text-align: center;
            letter-spacing: -0.3px;
        }

        .faculty-position {
            color: #666;
            font-size: 1rem;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
            background: rgba(0, 82, 163, 0.05);
            padding: 8px 12px;
            border-radius: 8px;
            width: 100%;
        }

        .faculty-info {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 12px;
            font-size: 1rem;
            color: #34495e;
            margin-bottom: 12px;
            line-height: 1.5;
            width: 100%;
        }

        .faculty-info strong {
            text-align: left;
            color: #0052a3;
            font-weight: 600;
            white-space: nowrap;
        }

        .faculty-info span {
            text-align: left;
            word-break: break-word;
            color: #555;
        }

        .faculty-phone {
            font-size: 1rem;
            color: #34495e;
            margin-bottom: 12px;
            line-height: 1.5;
            width: 100%;
        }

        .faculty-email-info {
            font-size: 1rem;
            color: #34495e;
            margin-bottom: 20px;
            word-break: break-all;
            line-height: 1.5;
            width: 100%;
        }

        .btn-detail {
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
            color: white !important;
            border: none;
            padding: 15px 35px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: auto;
            white-space: nowrap;
            box-shadow: 0 4px 15px rgba(0, 82, 163, 0.3);
            cursor: pointer;
            position: relative;
            z-index: 2;
        }

        .btn-detail:hover {
            background: linear-gradient(135deg, #003d7a 0%, #002650 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 82, 163, 0.4);
        }

        .btn-detail:active {
            transform: translateY(-1px);
        }

        /* ============ FOOTER ============ */
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

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                gap: 10px;
            }

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

            .page-title {
                font-size: 1.8rem;
                margin-bottom: 30px;
            }

            .faculty-card {
                min-height: 450px;
                padding: 30px 25px;
            }

            .faculty-avatar {
                width: 110px;
                height: 110px;
                font-size: 3rem;
                margin-bottom: 20px;
                background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%) !important;
                color: white !important;
            }
        }

        @media (max-width: 576px) {
            .logo-text h2 {
                font-size: 14px;
            }

            .logo-img {
                width: 60px;
                height: 60px;
            }

            .page-title {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }

            .faculty-card {
                min-height: 450px;
                padding: 25px 20px;
            }

            .faculty-info {
                font-size: 0.95rem;
                gap: 10px;
            }

            .btn-detail {
                width: 100%;
                padding: 12px 20px;
            }
        }



        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            padding: 10px;
            position: absolute;
            z-index: 9999;
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





        /* PAGE SPECIFIC STYLES */
        .breadcrumb {
            background: none;
            padding: 0;
            margin-bottom: 20px;
        }

        .breadcrumb-item a {
            color: #007bff;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        .page-title {
            color: #0052a3;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }

        .faculty-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            padding: 35px 30px;
            margin-bottom: 30px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            min-height: 480px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .faculty-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            border-color: #0052a3;
        }

        .faculty-avatar {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%) !important;
            border: 5px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white !important;
            font-size: 3.5rem;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 82, 163, 0.3) !important;
            flex-shrink: 0;
        }

        .faculty-avatar i {
            margin: 0;
            padding: 0;
            line-height: 1;
        }

        .faculty-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .faculty-name {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 12px;
            font-size: 1.1rem;
            line-height: 1.3;
            text-align: center;
        }

        .faculty-position {
            color: #7f8c8d;
            font-size: 1rem;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .faculty-phone {
            font-size: 0.9rem;
            color: #34495e;
            margin-bottom: 8px;
            line-height: 1.5;
            text-align: center;
            width: 100%;
        }

        .faculty-email-info {
            font-size: 0.9rem;
            color: #34495e;
            margin-bottom: 20px;
            word-break: break-all;
            line-height: 1.5;
            text-align: center;
            width: 100%;
        }

        .faculty-info {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 12px;
            font-size: 1rem;
            color: #34495e;
            margin-bottom: 12px;
            line-height: 1.5;
            width: 100%;
        }

        .faculty-info strong {
            text-align: left;
        }

        .faculty-info span {
            text-align: left;
            word-break: break-word;
        }

        .btn-detail {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: auto;
            white-space: nowrap;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-detail:hover {
            background: linear-gradient(135deg, #2980b9, #1f5f8b);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        /* FOOTER STYLES */
        footer {
            background: #0052a3;
            color: white;
            padding: 40px 0 20px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
            text-align: center;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
        }

        @media (max-width: 576px) {
            .logo-text h2 {
                font-size: 16px;
            }

            .section-title h2 {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
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
                            <a href="#" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-bell"></i> Thông báo
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="thong_bao_do_an.php"><i class="bi bi-bell me-2"></i>Thông báo đồ án</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-search"></i> Tra cứu
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="khoa_cntt.php"><i class="bi bi-person-badge me-2"></i>Khoa Công nghệ thông tin</a></li>
                                <li><a class="dropdown-item" href="danh_sach_de_tai_cong_khai.php"><i class="bi bi-journal-text me-2"></i>Danh sách đề tài</a></li>
                                <?php $menuLinks = getMenuLinks(); ?>
                                <?php if (!empty($menuLinks['ket_qua_thi'])): ?>
                                    <li><a class="dropdown-item" href="<?= $menuLinks['ket_qua_thi'] ?>" target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Kết quả thi</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <li class="dropdown">
                            <a href="#" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-info-circle"></i> Giới thiệu
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="gioi_thieu_sinh_vien.php"><i class="bi bi-mortarboard me-2"></i>Sinh viên</a></li>
                                <li><a class="dropdown-item" href="gioi_thieu_giang_vien.php"><i class="bi bi-person-badge me-2"></i>Giảng viên</a></li>
                                <li><a class="dropdown-item" href="gioi_thieu_lanh_dao.php"><i class="bi bi-shield-lock me-2"></i>Lãnh đạo</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-people"></i> Biểu mẫu
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="https://drive.google.com/drive/folders/1mSIO416XjD_t_J3RVunTQWK2qB4kRvNn" target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Biểu mẫu cho sinh viên</a></li>
                                <li><a class="dropdown-item" href="https://drive.google.com/drive/folders/1m0knxZO_grEEt3bDSLmATW5cTu2Bhzv5" target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Biểu mẫu cho giảng viên</a></li>
                            </ul>
                        </li>
                        <li><a href="auth/login.php" class="nav-item login-btn"><i class="bi bi-box-arrow-in-right"></i>
                                Đăng nhập</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" style="color: #0052a3;">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Khoa Công nghệ thông tin</li>
            </ol>
        </nav>

        <!-- Page Title -->
        <h1 class="page-title">Thông tin giảng viên hướng dẫn đồ án</h1>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">
                <i class="bi bi-people-fill"></i> Tất cả
            </button>
            <button class="filter-tab" data-filter="Trưởng khoa">
                <i class="bi bi-person-badge-fill"></i> Trưởng khoa
            </button>
            <button class="filter-tab" data-filter="Phó Trưởng khoa">
                <i class="bi bi-person-badge"></i> Phó trưởng khoa
            </button>
            <button class="filter-tab" data-filter="Giảng viên">
                <i class="bi bi-person-fill"></i> Giảng viên
            </button>
        </div>

        <!-- Member Count -->
        <div class="member-count">
            Hiển thị <span id="memberCount">0</span> giảng viên
        </div>

        <!-- Faculty Grid -->
        <div class="row" id="facultyGrid">
            <?php
            $faculty_members = require_once __DIR__ . '/config/faculty_members.php';
            $totalMembers = count($faculty_members);

            foreach ($faculty_members as $index => $member) {
                // Check if lecturer exists in database by joining with nguoi_dung table
                $gvModel = new GiangVienModel();
                $nguoiDungModel = new NguoiDungModel();

                // First find user by email
                $user = $nguoiDungModel->findByEmail($member['email']);
                $existing = null;

                if ($user) {
                    // Then find lecturer profile by user ID
                    $existing = $gvModel->getByNguoiDungId($user['id']);
                }

                // Lấy thông tin từ database nếu có, nếu không thì dùng từ config
                $displayPhone = $member['phone']; // Mặc định từ config
                $avatarContent = '<i class="bi bi-person-circle"></i>'; // Mặc định
            
                // Nếu có trong database và có số điện thoại, ưu tiên từ database
                if ($existing && !empty($existing['so_dien_thoai'])) {
                    $displayPhone = $existing['so_dien_thoai'];
                }

                // Kiểm tra ảnh đại diện
                if ($existing && !empty($existing['avatar'])) {
                    $avatarPath = 'uploads/avatars/' . $existing['avatar'];
                    if (file_exists($avatarPath)) {
                        $avatarContent = '<img src="' . $avatarPath . '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
                    }
                }

                // Get position class for filtering
                $positionClass = strtolower(str_replace(' ', '-', $member['position']));

                echo '<div class="col-lg-4 col-md-5 col-sm-12 mb-4 faculty-item" data-position="' . htmlspecialchars($member['position']) . '" data-name="' . strtolower(htmlspecialchars($member['name'])) . '">';
                echo '<div class="faculty-card">';
                echo '<div class="faculty-avatar">';
                echo $avatarContent;
                echo '</div>';
                echo '<div class="faculty-content">';
                echo '<div class="faculty-info"><strong style="color: #d2691e;">Họ tên:</strong> <span>' . htmlspecialchars($member['name']) . '</span></div>';
                echo '<div class="faculty-info"><strong style="color: #d2691e;">Chức vụ:</strong> <span>' . htmlspecialchars($member['position']) . '</span></div>';
                echo '<div class="faculty-info"><strong style="color: #d2691e;">Số nội bộ:</strong> <span>' . htmlspecialchars($member['room']) . '</span></div>';
                echo '<div class="faculty-info"><strong style="color: #d2691e;">Điện thoại:</strong> <span>' . htmlspecialchars($displayPhone) . '</span></div>';
                echo '<div class="faculty-info"><strong style="color: #d2691e;">Email:</strong> <span>' . htmlspecialchars($member['email']) . '</span></div>';

                // Luôn hiển thị link danh sách đề tài cho tất cả giảng viên
                echo '<a href="giang_vien_chi_tiet_v2.php?email=' . urlencode($member['email']) . '" class="btn-detail">Danh sách đề tài</a>';

                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- No Results Message (hidden by default) -->
        <div class="no-results" id="noResults" style="display: none;">
            <i class="bi bi-person-x"></i>
            <h4>Không tìm thấy giảng viên</h4>
            <p>Vui lòng chọn bộ lọc khác</p>
        </div>

        <!-- Back to Top Button -->
        <div class="back-to-top" id="backToTop">
            <i class="bi bi-arrow-up"></i>
        </div>


    </div>

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

    <!-- Bootstrap JS -->
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
        // Sticky Header functionality
        document.addEventListener('DOMContentLoaded', function () {
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

    <script>
        // Filter Tabs, Search and Back to Top functionality
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize member count
            const totalMembers = document.querySelectorAll('.faculty-item').length;
            document.getElementById('memberCount').textContent = totalMembers;

            // Filter Tabs functionality
            const filterTabs = document.querySelectorAll('.filter-tab');
            const facultyItems = document.querySelectorAll('.faculty-item');
            const noResults = document.getElementById('noResults');

            filterTabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    // Remove active class from all tabs
                    filterTabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');

                    const filterValue = this.getAttribute('data-filter');
                    let visibleCount = 0;

                    facultyItems.forEach(item => {
                        const position = item.getAttribute('data-position');

                        if (filterValue === 'all' || position === filterValue) {
                            item.style.display = 'block';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Update member count
                    document.getElementById('memberCount').textContent = visibleCount;

                    // Show/hide no results message
                    if (visibleCount === 0) {
                        noResults.style.display = 'block';
                    } else {
                        noResults.style.display = 'none';
                    }
                });
            });

            // Back to Top functionality
            const backToTopBtn = document.getElementById('backToTop');

            window.addEventListener('scroll', function () {
                if (window.pageYOffset > 300) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            });

            backToTopBtn.addEventListener('click', function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>
