<?php
/**
 * TRANG TRỢ GIÚP - REDESIGNED THEO INDEX.PHP
 */

require_once 'bootstrap.php';

// Nếu đã đăng nhập, chuyển đến dashboard theo vai trò
if (isLoggedIn()) {
    switch ($_SESSION['vai_tro']) {
        case ROLE_GIANG_VIEN:
            redirect('giang_vien/dashboard.php');
            break;
        case ROLE_SINH_VIEN:
            redirect('sinh_vien/dashboard.php');
            break;
        case ROLE_LANH_DAO:
            redirect('lanh_dao/dashboard.php');
            break;
        default:
            redirect('auth/login.php');
    }
}

$pageTitle = 'Trợ giúp - Hệ thống Quản lý Đề tài';
$isLoggedIn = isLoggedIn();
$user = $isLoggedIn ? getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }
        
        /* Header - Same as index.php */
        .header {
            background: #ffffff;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .header-top {
            background: #ffffff;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .header-top .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .logo-img {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .logo-text {
            flex: 1;
        }
        
        .logo-text .university-name {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            color: #d32f2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        
        .logo-text .faculty-name {
            font-size: 1.6rem;
            margin: 0;
            color: #1976d2;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .language-switch {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .language-switch a {
            color: #666;
            text-decoration: none;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .language-switch a:hover {
            background: #f5f5f5;
            color: #1976d2;
        }
        
        .language-switch .separator {
            color: #ccc;
        }
        
        .header-nav {
            background: #ffffff;
            padding: 1rem 0 0 0;
            width: 100%;
        }
        
        .header-nav .nav-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            background: #1976d2;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .header-nav .container {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
        
        .header-nav .nav {
            display: flex;
            justify-content: flex-start;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .header-nav .nav-item {
            margin: 0;
            position: relative;
        }
        
        .header-nav .nav-item.dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .header-nav .dropdown-menu {
            background: #ffffff;
            border: none;
            border-top: 2px solid #1976d2;
            margin: 0;
            padding: 0;
            min-width: 220px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 0;
        }
        
        .header-nav .dropdown-item {
            color: #333333;
            padding: 0.85rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .header-nav .dropdown-item:hover {
            background: #f8f9fc;
            color: #1976d2;
        }
        
        .header-nav .nav-link {
            color: white;
            padding: 1rem 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .header-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-bottom-color: #ffffff;
        }
        
        .header-nav .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            border-bottom-color: #ffffff;
        }
        
        /* Help Content */
        .help-hero {
            background: #ffffff;
            padding: 3rem 0;
            text-align: center;
        }
        
        .help-hero h1 {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1rem;
            color: #1976d2;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .help-hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            color: #333333;
            font-weight: 600;
        }
        
        .help-search {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .help-card {
            background: #ffffff;
            border: none;
            border-radius: 0.5rem;
            padding: 0;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            height: 100%;
        }
        
        .help-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }
        
        .help-card .card-body {
            padding: 1.5rem 1.25rem;
        }
        
        .help-icon {
            width: 60px;
            height: 60px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 15px auto;
            color: white;
        }
        
        .icon-primary { 
            background: #1976d2;
            border-top: 4px solid #1976d2;
        }
        .icon-success { 
            background: #1cc88a;
            border-top: 4px solid #1cc88a;
        }
        .icon-warning { 
            background: #ffd700;
            border-top: 4px solid #ffd700;
            color: #333333;
        }
        .icon-info { 
            background: #003d82;
            border-top: 4px solid #003d82;
        }
        
        .help-card.primary {
            border-top: 4px solid #1976d2;
        }
        
        .help-card.success {
            border-top: 4px solid #1cc88a;
        }
        
        .help-card.warning {
            border-top: 4px solid #ffd700;
        }
        
        .help-card.info {
            border-top: 4px solid #003d82;
        }
        
        .btn-help {
            padding: 0.5rem 1.5rem;
            font-weight: 700;
            border-radius: 0.35rem;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .btn-help:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .btn-help.primary {
            background: #1976d2;
            color: white;
        }
        
        .btn-help.primary:hover {
            background: #1565c0;
            color: white;
        }
        
        .btn-help.success {
            background: #1cc88a;
            color: white;
        }
        
        .btn-help.success:hover {
            background: #17a673;
            color: white;
        }
        
        .btn-help.warning {
            background: #ffd700;
            color: #333333;
        }
        
        .btn-help.warning:hover {
            background: #f6c23e;
            color: #333333;
        }
        
        .btn-help.info {
            background: #003d82;
            color: white;
        }
        
        .btn-help.info:hover {
            background: #002d5f;
            color: white;
        }
        
        .faq-item {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .faq-question {
            background: #f8f9fc;
            padding: 1rem 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
            font-weight: 600;
        }
        
        .faq-question:hover {
            background: #e9ecef;
        }
        
        .faq-answer {
            padding: 1rem 1.5rem;
            display: none;
            background: white;
            border-top: 1px solid #e0e0e0;
        }
        
        .faq-answer.show {
            display: block;
        }
        
        .step-card {
            background: white;
            border-left: 4px solid #1976d2;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .step-number {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: #1976d2;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 35px;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .contact-card {
            background: #1976d2;
            color: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .contact-item i {
            font-size: 24px;
            margin-right: 15px;
            width: 40px;
        }
        
        .contact-item a {
            color: white;
            text-decoration: none;
        }
        
        .contact-item a:hover {
            text-decoration: underline;
        }
        
        /* Footer - Same as index.php */
        .footer {
            background: #ffffff;
            border-top: 3px solid #003d82;
            padding: 2rem 0 1rem 0;
            margin-top: auto;
        }
        
        .footer-title h4 {
            color: #003d82;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .footer-subtitle {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }
        
        .footer-divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #ffd700 0%, #f6c23e 100%);
            margin: 1rem 0;
        }
        
        .footer-contact {
            color: #333;
            font-size: 0.9rem;
        }
        
        .footer-contact p {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .footer-contact i {
            color: #003d82;
            font-size: 1rem;
        }
        
        .footer-contact a {
            color: #003d82;
            text-decoration: none;
        }
        
        .footer-contact a:hover {
            text-decoration: underline;
        }
        
        .footer-brand {
            margin-top: 0.50rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .brand-line {
            width: 200px;
            height: 8px;
            background: #003d82;
            position: relative;
            overflow: visible;
        }
        
        .brand-line::before {
            content: '';
            position: absolute;
            left: 50px;
            top: 0;
            bottom: 0;
            width: 30px;
            background: #ffd700;
            transform: skewX(-20deg);
        }
        
        .brand-line::after {
            content: '';
            position: absolute;
            left: 75px;
            top: 0;
            bottom: 0;
            width: 125px;
            background: #003d82;
            transform: skewX(-20deg);
        }
        
        .footer-brand span {
            color: #003d82;
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .social-icons {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .social-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.5rem;
        }
        
        .social-icon.facebook {
            background: #1877f2;
            color: white;
        }
        
        .social-icon.facebook:hover {
            background: #145dbf;
            transform: translateY(-3px);
        }
        
        .social-icon.youtube {
            background: #ff0000;
            color: white;
        }
        
        .social-icon.youtube:hover {
            background: #cc0000;
            transform: translateY(-3px);
        }
        
        .social-icon.github {
            background: #333;
            color: white;
        }
        
        .social-icon.github:hover {
            background: #000;
            transform: translateY(-3px);
        }
        
        @media (max-width: 768px) {
            .help-hero h1 {
                font-size: 2rem;
            }
            
            .help-hero p {
                font-size: 1rem;
            }
            
            .help-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="header">
        <!-- Header Top - Logo & Title -->
        <div class="header-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <div class="logo-section">
                            <div class="logo-img">
                                <img src="assets/images/logo.png" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                            </div>
                            <div class="logo-text">
                                <div class="university-name">ĐẠI HỌC TRÀ VINH</div>
                                <div class="faculty-name">KHOA CÔNG NGHỆ THÔNG TIN</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="language-switch">
                            <a href="#" class="active">Tiếng Việt</a>
                            <span class="separator">|</span>
                            <a href="#">English</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Header Navigation -->
            <div class="header-nav">
                <div class="nav-wrapper">
                    <div class="container">
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link" href="index.php">
                                    Trang chủ
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#features" id="gioiThieuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Giới thiệu
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="gioiThieuDropdown">
                                    <li><a class="dropdown-item" href="gioi_thieu_giang_vien.php">Dành cho Giảng viên</a></li>
                                    <li><a class="dropdown-item" href="gioi_thieu_sinh_vien.php">Dành cho Sinh viên</a></li>
                                    <li><a class="dropdown-item" href="gioi_thieu_lanh_dao.php">Dành cho Lãnh đạo</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="help.php">
                                    Trợ giúp
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="auth/login.php">
                                    Đăng nhập
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

<div class="help-hero">
    <div class="container text-center">
        <h1>
            Trung tâm Trợ giúp
        </h1>
        <p>Tìm câu trả lời cho mọi thắc mắc của bạn</p>
        <div class="help-search">
            <div class="input-group input-group-lg">
                <input type="text" class="form-control" id="searchHelp" placeholder="Tìm kiếm câu hỏi...">
                <button class="btn btn-primary" type="button">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Quick Access Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-sm-6">
            <div class="card help-card primary text-center">
                <div class="card-body">
                    <div class="help-icon icon-primary">
                        <i class="bi bi-book"></i>
                    </div>
                    <h5>Hướng dẫn</h5>
                    <p class="text-muted mb-3">Tìm hiểu cách sử dụng hệ thống</p>
                    <a href="#huong-dan" class="btn btn-help primary">Xem ngay</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card help-card success text-center">
                <div class="card-body">
                    <div class="help-icon icon-success">
                        <i class="bi bi-patch-question"></i>
                    </div>
                    <h5>FAQ</h5>
                    <p class="text-muted mb-3">Câu hỏi thường gặp</p>
                    <a href="#faq" class="btn btn-help success">Xem ngay</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card help-card warning text-center">
                <div class="card-body">
                    <div class="help-icon icon-warning">
                        <i class="bi bi-play-circle"></i>
                    </div>
                    <h5>Video</h5>
                    <p class="text-muted mb-3">Hướng dẫn chi tiết qua video</p>
                    <a href="#video" class="btn btn-help warning">Xem ngay</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card help-card info text-center">
                <div class="card-body">
                    <div class="help-icon icon-info">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5>Hỗ trợ</h5>
                    <p class="text-muted mb-3">Liên hệ với chúng tôi</p>
                    <a href="#lien-he" class="btn btn-help info">Liên hệ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hướng dẫn sử dụng -->
    <div id="huong-dan" class="mb-5">
        <h2 class="mb-4">
            Hướng dẫn sử dụng
        </h2>
        
        <?php if ($isLoggedIn && $user['vai_tro'] === 'giang_vien'): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Hướng dẫn dành cho <strong>Giảng viên</strong>
            </div>
            <div class="step-card">
                <span class="step-number">1</span>
                <strong>Đăng nhập hệ thống</strong>
                <p class="mb-0 mt-2 text-muted">Sử dụng tài khoản được cấp để truy cập vào hệ thống</p>
            </div>
            <div class="step-card">
                <span class="step-number">2</span>
                <strong>Tạo đề tài mới</strong>
                <p class="mb-0 mt-2 text-muted">Tạo 10 đề tài Cơ sở ngành + 10 đề tài Chuyên ngành theo yêu cầu</p>
            </div>
            <div class="step-card">
                <span class="step-number">3</span>
                <strong>Gửi đề tài chờ duyệt</strong>
                <p class="mb-0 mt-2 text-muted">Sau khi hoàn tất, gửi đề tài để lãnh đạo xem xét và phê duyệt</p>
            </div>
            <div class="step-card">
                <span class="step-number">4</span>
                <strong>Quản lý đăng ký</strong>
                <p class="mb-0 mt-2 text-muted">Duyệt hoặc từ chối sinh viên đăng ký vào đề tài của bạn</p>
            </div>
            <div class="step-card">
                <span class="step-number">5</span>
                <strong>Theo dõi tiến độ</strong>
                <p class="mb-0 mt-2 text-muted">Quản lý danh sách sinh viên và theo dõi tiến độ thực hiện</p>
            </div>
        
        <?php elseif ($isLoggedIn && $user['vai_tro'] === 'sinh_vien'): ?>
            <div class="alert alert-success">
                <i class="bi bi-info-circle"></i> Hướng dẫn dành cho <strong>Sinh viên</strong>
            </div>
            <div class="step-card">
                <span class="step-number">1</span>
                <strong>Đăng nhập hệ thống</strong>
                <p class="mb-0 mt-2 text-muted">Sử dụng tài khoản sinh viên để truy cập</p>
            </div>
            <div class="step-card">
                <span class="step-number">2</span>
                <strong>Xem danh sách đề tài</strong>
                <p class="mb-0 mt-2 text-muted">Duyệt qua các đề tài đã được lãnh đạo phê duyệt</p>
            </div>
            <div class="step-card">
                <span class="step-number">3</span>
                <strong>Tìm kiếm và lọc</strong>
                <p class="mb-0 mt-2 text-muted">Sử dụng bộ lọc để tìm đề tài phù hợp với sở thích</p>
            </div>
            <div class="step-card">
                <span class="step-number">4</span>
                <strong>Đăng ký đề tài</strong>
                <p class="mb-0 mt-2 text-muted">Chọn đề tài mong muốn và gửi yêu cầu đăng ký</p>
            </div>
            <div class="step-card">
                <span class="step-number">5</span>
                <strong>Theo dõi trạng thái</strong>
                <p class="mb-0 mt-2 text-muted">Kiểm tra trạng thái đăng ký và nhận thông báo từ giảng viên</p>
            </div>
        
        <?php elseif ($isLoggedIn && $user['vai_tro'] === 'lanh_dao'): ?>
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> Hướng dẫn dành cho <strong>Lãnh đạo</strong>
            </div>
            <div class="step-card">
                <span class="step-number">1</span>
                <strong>Đăng nhập hệ thống</strong>
                <p class="mb-0 mt-2 text-muted">Truy cập với quyền lãnh đạo</p>
            </div>
            <div class="step-card">
                <span class="step-number">2</span>
                <strong>Xem đề tài chờ duyệt</strong>
                <p class="mb-0 mt-2 text-muted">Kiểm tra danh sách đề tài giảng viên đã gửi</p>
            </div>
            <div class="step-card">
                <span class="step-number">3</span>
                <strong>Duyệt đề tài</strong>
                <p class="mb-0 mt-2 text-muted">Phê duyệt hoặc từ chối đề tài với lý do cụ thể</p>
            </div>
            <div class="step-card">
                <span class="step-number">4</span>
                <strong>Xem thống kê</strong>
                <p class="mb-0 mt-2 text-muted">Theo dõi báo cáo tổng hợp và phân công</p>
            </div>
            <div class="step-card">
                <span class="step-number">5</span>
                <strong>Xuất báo cáo</strong>
                <p class="mb-0 mt-2 text-muted">Tải báo cáo dưới dạng Excel hoặc PDF</p>
            </div>
        
        <?php else: ?>
            <div class="alert alert-primary">
                Hướng dẫn chung cho người dùng
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5><i class="bi bi-person-check text-primary"></i> Đăng nhập</h5>
                            <p class="text-muted">Bằng tài khoản được cấp để truy cập hệ thống</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5><i class="bi bi-compass text-success"></i> Khám phá</h5>
                            <p class="text-muted">Tìm hiểu các tính năng theo vai trò của bạn</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5><i class="bi bi-life-preserver text-danger"></i> Hỗ trợ</h5>
                            <p class="text-muted">Liên hệ nếu gặp khó khăn</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- FAQ Section -->
    <div id="faq" class="mb-5">
        <h2 class="mb-4">
            <i class="bi bi-patch-question text-success"></i> Câu hỏi thường gặp
        </h2>
        
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span><i class="bi bi-lock"></i> Làm sao để đổi mật khẩu?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Để đổi mật khẩu, bạn thực hiện các bước sau:
                <ol class="mt-2 mb-0">
                    <li>Click vào avatar/tên người dùng ở góc trên bên phải</li>
                    <li>Chọn "Cài đặt" từ menu dropdown</li>
                    <li>Điền mật khẩu hiện tại và mật khẩu mới</li>
                    <li>Click "Cập nhật" để hoàn tất</li>
                </ol>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span><i class="bi bi-key"></i> Tôi quên mật khẩu, phải làm sao?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Nếu bạn quên mật khẩu, vui lòng liên hệ với quản trị viên hệ thống qua:
                <ul class="mt-2 mb-0">
                    <li>Email: <a href="mailto:support@qldt.edu.vn">support@qldt.edu.vn</a></li>
                    <li>Điện thoại: (028) 1234 5678</li>
                </ul>
                Cung cấp mã số sinh viên/giảng viên để được hỗ trợ reset mật khẩu.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span><i class="bi bi-bell"></i> Làm sao để xem thông báo?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Click vào icon chuông (<i class="bi bi-bell-fill"></i>) ở góc trên bên phải màn hình để xem danh sách thông báo. 
                Các thông báo chưa đọc sẽ được đánh dấu bằng dấu chấm đỏ.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span><i class="bi bi-file-earmark-text"></i> Sinh viên có thể đăng ký bao nhiêu đề tài?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Mỗi sinh viên chỉ được đăng ký và thực hiện 1 đề tài duy nhất. Sau khi đăng ký được giảng viên chấp nhận, 
                bạn không thể đăng ký thêm đề tài khác.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span><i class="bi bi-clock-history"></i> Thời gian đăng ký đề tài là khi nào?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Thời gian đăng ký đề tài được thông báo cụ thể qua email và trên trang chủ hệ thống. 
                Thường diễn ra vào đầu mỗi học kỳ. Vui lòng theo dõi thông báo để không bỏ lỡ.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span><i class="bi bi-pencil-square"></i> Có thể chỉnh sửa đề tài sau khi gửi duyệt không?</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Giảng viên có thể chỉnh sửa đề tài khi đề tài đang ở trạng thái "Chờ duyệt" hoặc "Từ chối". 
                Sau khi đã được phê duyệt, cần liên hệ lãnh đạo để yêu cầu chỉnh sửa.
            </div>
        </div>
    </div>

    <!-- Video Tutorials -->
    <div id="video" class="mb-5">
        <h2 class="mb-4">
            <i class="bi bi-play-circle text-danger"></i> Video hướng dẫn
        </h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card help-card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-play-btn-fill" style="font-size: 48px; color: #667eea;"></i>
                        </div>
                        <h5>Hướng dẫn đăng nhập</h5>
                        <p class="text-muted">Cách đăng nhập và thiết lập tài khoản lần đầu</p>
                        <button class="btn btn-sm btn-primary" disabled>Sắp ra mắt</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card help-card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-play-btn-fill" style="font-size: 48px; color: #667eea;"></i>
                        </div>
                        <h5>Tạo và quản lý đề tài</h5>
                        <p class="text-muted">Dành cho giảng viên</p>
                        <button class="btn btn-sm btn-primary" disabled>Sắp ra mắt</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card help-card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-play-btn-fill" style="font-size: 48px; color: #667eea;"></i>
                        </div>
                        <h5>Đăng ký đề tài</h5>
                        <p class="text-muted">Dành cho sinh viên</p>
                        <button class="btn btn-sm btn-primary" disabled>Sắp ra mắt</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Support -->
    <div id="lien-he">
        <h2 class="mb-4">
            <i class="bi bi-headset text-info"></i> Liên hệ hỗ trợ
        </h2>
        <div class="contact-card">
            <h4 class="mb-4">Chúng tôi luôn sẵn sàng hỗ trợ bạn</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="contact-item">
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <strong>Email</strong><br>
                            <a href="mailto:support@qldt.edu.vn" class="text-white">support@qldt.edu.vn</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <strong>Điện thoại</strong><br>
                            (028) 1234 5678
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="contact-item">
                        <i class="bi bi-clock-fill"></i>
                        <div>
                            <strong>Thời gian hỗ trợ</strong><br>
                            Thứ 2 - Thứ 6: 8:00 - 17:00<br>
                            Thứ 7: 8:00 - 12:00
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <strong>Địa chỉ</strong><br>
                            123 Đường ABC, Quận XYZ, TP.HCM
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Footer -->
            <footer class="footer">
                <div class="container">
                    <div class="row">
                        <!-- Left Section - Info -->
                        <div class="col-md-8">
                            <div class="footer-title">
                                <h4>Khoa Công nghệ thông tin - Đại học Trà Vinh © <?= date('Y') ?></h4>
                                <p class="footer-subtitle">Faculty of Information Technology - Tra Vinh University</p>
                            </div>
                            <div class="footer-divider"></div>
                            <div class="footer-contact">
                                <p>
                                    <i class="bi bi-geo-alt-fill"></i>
                                    Số 126, Nguyễn Thiện Thành, Khóm 4, Phường Hòa Thuận, Tỉnh Vĩnh Long
                                </p>
                                <p>
                                    <i class="bi bi-telephone-fill"></i>
                                    (+84) 294.3855246 (Ext: 135 - 203)
                                </p>
                                <p>
                                    <i class="bi bi-envelope-fill"></i>
                                    ktcn@tvu.edu.vn
                                </p>
                                <p>
                                    <i class="bi bi-globe"></i>
                                    <a href="https://cet.tvu.edu.vn" target="_blank">https://cet.tvu.edu.vn</a>
                                </p>
                            </div>
                            <div class="footer-brand">
                                <div class="brand-line"></div>
                                <span>Tra Vinh University</span>
                            </div>
                        </div>
                        
                        <!-- Right Section - Social -->
                        <div class="col-md-4 text-end">
                            <div class="social-icons">
                                <a href="https://facebook.com" target="_blank" class="social-icon facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="https://youtube.com" target="_blank" class="social-icon youtube">
                                    <i class="bi bi-youtube"></i>
                                </a>
                                <a href="https://github.com" target="_blank" class="social-icon github">
                                    <i class="bi bi-github"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </header>

<script>
// FAQ Toggle
function toggleFaq(element) {
    const answer = element.nextElementSibling;
    const icon = element.querySelector('.bi-chevron-down, .bi-chevron-up');
    
    // Close all other FAQs
    document.querySelectorAll('.faq-answer').forEach(item => {
        if (item !== answer) {
            item.classList.remove('show');
        }
    });
    
    document.querySelectorAll('.faq-question i:last-child').forEach(item => {
        if (item !== icon) {
            item.className = 'bi bi-chevron-down';
        }
    });
    
    // Toggle current FAQ
    answer.classList.toggle('show');
    icon.className = answer.classList.contains('show') ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
}

// Search functionality
document.getElementById('searchHelp').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question span').textContent.toLowerCase();
        const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
        
        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
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
</script>

</body>
</html>
