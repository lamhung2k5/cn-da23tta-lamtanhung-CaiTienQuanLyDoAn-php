<?php
/**
 * GIỚI THIỆU DÀNH CHO SINH VIÊN
 */

require_once 'bootstrap.php';

$pageTitle = 'Giới thiệu - Dành cho Sinh viên';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <style>
        /* Dark Mode */
        [data-theme="dark"] body {
            background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.95)), url('img/backgourd234.png');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            color: #f1f5f9;
        }
        [data-theme="dark"] .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }
        [data-theme="dark"] .steps-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        [data-theme="dark"] .steps-section h2 {
            color: #f1f5f9 !important;
        }
        [data-theme="dark"] .step-item {
            background: #1e293b;
        }
        [data-theme="dark"] .step-content h4 {
            color: #f1f5f9;
        }
        [data-theme="dark"] .step-content p {
            color: #cbd5e1;
        }
        [data-theme="dark"] .feature-card {
            background: #1e293b;
        }
        [data-theme="dark"] .feature-title {
            color: #f1f5f9;
        }
        [data-theme="dark"] .feature-text {
            color: #cbd5e1;
        }
        [data-theme="dark"] .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: #ffffff;
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            color: #2c3e50;
            min-height: 100vh;
            padding-top: 0;
            overflow-x: hidden;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #1d4ed8 100%);
            padding: 0;
            box-shadow: 0 4px 20px rgba(30, 58, 138, 0.3);
        }
        
        .header-top {
            padding: 12px 0;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(30, 58, 138, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid #fbbf24;
            transition: padding 0.3s ease;
        }
        
        .header-top.scrolled {
            padding: 8px 0;
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.3);
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
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
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
        
        .logo-text {
            color: white;
        }
        
        .logo-text h1 {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .logo-text p {
            font-size: 0.8rem;
            margin: 0;
            opacity: 0.95;
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
        }

        /* Dark Mode Toggle Button */
        #theme-toggle {
            min-width: 80px;
        }

        #theme-toggle:hover {
            background: rgba(255, 215, 0, 0.2) !important;
            color: #ffd700 !important;
        }

        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
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
        
        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid #003d82;
        }
        
        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #003d82 0%, #0052a8 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .feature-icon i {
            font-size: 1.5rem;
            color: white;
        }
        
        .feature-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.75rem;
        }
        
        .feature-description {
            color: #000000;
            line-height: 1.6;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #000000;
            font-size: 0.9rem;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-list i {
            color: #1cc88a;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        /* Steps Section */
        .steps-section {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .step-item {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px dashed #e3e6f0;
        }
        
        .step-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #003d82 0%, #0052a8 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        
        .step-content h4 {
            color: #000000;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .step-content p {
            color: #000000;
            margin: 0;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #003d82 0%, #0052a8 100%);
            color: white;
            padding: 3rem;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .cta-section h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cta-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
         .btn-cta {
            background: #0077ffff;
            color: #ffffffff;
            padding: 1rem 3rem;
            font-size: 1.1rem;
            font-weight: 700;
            border: none;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-cta:hover {
            background: #0077ffff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-back {
            background: white;
            color: #003d82;
            border: 2px solid white;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: white;
            color: #003d82;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
        }
        
        /* ============ DROPDOWN ============ */
        .dropdown-menu { border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.15); border-radius: 12px; padding: 10px; }
        .dropdown-item { padding: 12px 15px; border-radius: 8px; font-weight: 500; }
        .dropdown-item:hover { background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%); color: #0052a3; }
        
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

        /* ============ BREADCRUMB ============ */
        .breadcrumb {
            background: transparent;
            padding: 20px 0;
            margin: 0;
        }
        .breadcrumb-item a {
            color: #0052a3;
            text-decoration: none;
            font-weight: 500;
        }
        .breadcrumb-item a:hover {
            color: #003d7a;
        }
        .breadcrumb-item.active {
            color: #6c757d;
            font-weight: 600;
        }

        
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
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
                            <li><a href="auth/login.php" class="nav-item login-btn"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    
        <!-- Quy trình đăng ký -->
        <div class="steps-section">
            <div class="container">
            <h2 class="text-center mb-5" style="color: #000000; font-weight: 800;">
                Quy trình đăng ký đề tài dành cho sinh viên 
            </h2>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Đăng nhập vào hệ thống</h4>
                    <p>Sử dụng tài khoản sinh viên được cấp để đăng nhập vào hệ thống quản lý đề tài.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Tìm kiếm đề tài phù hợp</h4>
                    <p>Truy cập menu "Đề tài có thể đăng ký" để xem, lọc theo hệ đào tạo hoặc tìm kiếm đề tài theo tên và giảng viên.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Xem chi tiết đề tài</h4>
                    <p>Click vào đề tài để xem thông tin chi tiết bao gồm: mô tả, yêu cầu, công nghệ sử dụng, số lượng sinh viên còn trống, thông tin giảng viên hướng dẫn.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Đăng ký đề tài</h4>
                    <p>Sinh viên nhấn "Đăng ký" để gửi yêu cầu. Hệ thống kiểm tra điều kiện (chưa đăng ký đề tài cùng hệ và đề tài còn chỗ trống) trước khi chấp nhận.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h4>Chờ giảng viên duyệt</h4>
                    <p>Sau khi đăng ký. Giảng viên sẽ xem xét hồ sơ của bạn và quyết định phê duyệt hoặc từ chối. Bạn sẽ nhận được thông báo kết quả.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">6</div>
                <div class="step-content">
                    <h4>Theo dõi kết quả</h4>
                    <p>Xem trạng thái các đề tài đã đăng ký trong mục "Đề tài của tôi", bao gồm thông tin duyệt, lý do từ chối và đăng ký lại đề tài khác.</p>
                </div>
            </div>
        </div>

     

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
                    
                    <div class="footer-divider mb-3" style="width: 100px; height: 3px; background: linear-gradient(135deg, #ffcc00 0%, #ffa500 100%);"></div>
                    
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
                        <div class="brand-line" style="width: 150px; height: 2px; background: linear-gradient(90deg, #ffcc00 0%, transparent 100%);"></div>
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
    // Sticky header effect on scroll
    window.addEventListener('scroll', function() {
        const headerTop = document.querySelector('.header-top');
        if (window.scrollY > 50) {
            headerTop.classList.add('scrolled');
        } else {
            headerTop.classList.remove('scrolled');
        }
    });
    </script>
</body>
</html>

