<?php
/**
 * HEADER CHUNG
 * File header dùng chung cho toàn bộ hệ thống
 */

// Kiểm tra đã load bootstrap.php chưa
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../bootstrap.php';
}

// Lấy thông tin user hiện tại
$currentUser = getCurrentUser();
$isLoggedIn = isLoggedIn();

// Lấy số thông báo chưa đọc và danh sách thông báo (nếu đã đăng nhập)
$soThongBaoChuaDoc = 0;
$danhSachThongBao = [];
if ($isLoggedIn) {
    $thongBaoModel = new ThongBaoModel();
    $soThongBaoChuaDoc = $thongBaoModel->countChuaDoc($currentUser['id']);
    $danhSachThongBao = $thongBaoModel->getThongBaoByNguoiDung($currentUser['id'], 5); // Lấy 5 thông báo gần nhất
}

// Xác định trang hiện tại để highlight menu
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Lấy page title nếu chưa được định nghĩa
if (!isset($pageTitle)) {
    $pageTitle = 'Hệ thống Quản lý Đề tài';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hệ thống quản lý đề tài cơ sở ngành và chuyên ngành - Khoa Công nghệ thông tin">
    <meta name="author" content="Khoa CNTT - Đại học Trà Vinh">
    <title><?= htmlspecialchars($pageTitle) ?> - Hệ thống QLĐT</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= time() ?>">
    
    <!-- Fixed Navbar & Sidebar CSS -->
    <style>
        /* Fixed Navbar */
        .navbar.sticky-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        
        /* Dropdown menu z-index - cao hơn navbar */
        .dropdown-menu {
            z-index: 1050 !important;
        }
        
        /* Notification dropdown specific */
        #notificationDropdown + .dropdown-menu {
            z-index: 1050 !important;
            margin-top: 0.5rem !important;
            max-width: 90vw !important;
            right: 0 !important;
            left: auto !important;
            position: absolute !important;
            transform: none !important;
            top: 100% !important;
        }
        
        /* Ensure dropdown stays within viewport */
        .dropdown-menu-end {
            right: 0 !important;
            left: auto !important;
            position: absolute !important;
            transform: none !important;
        }
        
        /* Fix dropdown position - prevent shifting */
        .dropdown-menu[data-bs-popper] {
            right: 0 !important;
            left: auto !important;
            transform: none !important;
        }
        
        /* Remove background on notification icon hover/click */
        #notificationDropdown {
            background: transparent !important;
            border: none !important;
            padding: 0.5rem 0.75rem !important;
            border-radius: 0.5rem !important;
            transition: background-color 0.2s ease;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 48px !important;
            height: 48px !important;
        }
        
        #notificationDropdown:hover {
            background: rgba(255, 255, 255, 0.1) !important;
        }
        
        #notificationDropdown:focus,
        #notificationDropdown:active,
        #notificationDropdown.show {
            background: rgba(255, 255, 255, 0.15) !important;
            box-shadow: none !important;
            outline: none !important;
        }
        
        /* User menu button hover effect */
        .user-menu-btn {
            padding: 0.5rem 0.75rem !important;
            border-radius: 0.5rem !important;
            transition: background-color 0.2s ease;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 48px !important;
            height: 48px !important;
        }
        
        .user-menu-btn:hover {
            background: rgba(255, 255, 255, 0.1) !important;
        }
        
        .user-menu-btn:focus,
        .user-menu-btn:active,
        .user-menu-btn.show {
            background: rgba(255, 255, 255, 0.15) !important;
            box-shadow: none !important;
            outline: none !important;
        }
        
        /* Prevent layout shift */
        .nav-link {
            border: 2px solid transparent !important;
        }
        
        .nav-link:focus,
        .nav-link:active {
            border-color: transparent !important;
        }
        
        /* User dropdown menu */
        .user-dropdown {
            min-width: 300px !important;
            max-height: 600px;
            overflow-y: auto;
            z-index: 1050 !important;
            margin-top: 0.5rem !important;
        }
        
        /* Notification items */
        .notification-item .dropdown-item {
            white-space: normal !important;
            word-wrap: break-word;
        }
        
        .notification-item .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        /* Unread notification highlight */
        .notification-item .bg-light {
            background-color: #e3f2fd !important;
        }
        
        /* Dropdown hover effect */
        .nav-item.dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }
        
        .dropdown-menu {
            margin-top: 0 !important;
        }
        
        /* Body padding for fixed navbar */
        body {
            padding-top: 76px; /* Height of navbar */
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 76px;
            }
            .col-md-10.p-4 {
                margin-left: 0;
            }
            .flash-messages-container {
                margin-left: 0;
            }
            .user-dropdown {
                min-width: 320px !important;
            }
        }
    </style>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/images/logo.png">
</head>
<body>

<!-- Top Navigation Bar -->
<?php if ($isLoggedIn): ?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm" style="background: linear-gradient(135deg, #003d82 0%, #0052a8 100%);">
    <div class="container-fluid">
        <!-- Logo & Brand -->
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
            <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 5px; margin-right: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div>
                <div style="font-size: 1.1rem; font-weight: 700; line-height: 1.2; letter-spacing: 0.5px;">
                    HỆ THỐNG QUẢN LÝ ĐỀ TÀI
                </div>
                <div style="font-size: 0.75rem; font-weight: 400; opacity: 0.9; margin-top: 2px;">
                    Khoa Công nghệ thông tin
                </div>
            </div>
        </a>
        
        <!-- Toggle button for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
            
            <!-- Right side menu -->
            <ul class="navbar-nav ms-auto">
                <!-- Notification Menu (Bên trái) -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link d-flex align-items-center position-relative" href="#" id="notificationDropdown" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell fs-5" style="color: #ffffff;"></i>
                        <?php if ($soThongBaoChuaDoc > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                <?= $soThongBaoChuaDoc ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px; max-width: 400px; max-height: 500px; overflow-y: auto;" aria-labelledby="notificationDropdown">
                        <!-- Header thông báo -->
                        <li class="px-3 py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Thông báo</h6>
                                <?php if ($soThongBaoChuaDoc > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $soThongBaoChuaDoc ?></span>
                                <?php endif; ?>
                            </div>
                        </li>
                        
                        <!-- Danh sách thông báo -->
                        <?php if (!empty($danhSachThongBao)): ?>
                            <?php foreach ($danhSachThongBao as $tb): ?>
                                <li>
                                    <a class="dropdown-item <?= $tb['da_doc'] == 0 ? 'bg-light' : '' ?>" 
                                       href="<?= !empty($tb['link']) ? BASE_URL . $tb['link'] : '#' ?>" 
                                       style="padding: 0.75rem 1rem; border-bottom: 1px solid #f0f0f0; white-space: normal;">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-bell-fill text-danger mt-1 flex-shrink-0"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                                    <?= htmlspecialchars($tb['tieu_de']) ?>
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    <?= formatDate($tb['created_at'], 'd/m/Y H:i') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider my-0"></li>
                            <li class="text-center">
                                <?php 
                                // Xác định chế độ dựa vào thư mục hiện tại
                                $thongBaoUrl = BASE_URL . 'thong_bao.php';
                                if ($currentDir === 'giang_vien') {
                                    $thongBaoUrl .= '?mode=giang_vien';
                                }
                                ?>
                                <a class="dropdown-item small text-primary" href="<?= $thongBaoUrl ?>" style="padding: 0.5rem;">
                                    Xem tất cả thông báo
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <div class="text-center small text-muted py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <p class="mb-0 mt-2">Không có thông báo</p>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <!-- User Menu (Bên phải) -->
                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center user-menu-btn" href="#" id="userDropdown" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-fill fs-5" style="color: #ffffff; filter: brightness(1.3);"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="userDropdown">
                        <!-- User Info Section -->
                        <li class="user-info-section">
                            <div class="text-center">
                                <div class="user-avatar">
                                    <img src="<?= BASE_URL ?>assets/images/hinh.png" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                </div>
                                <div class="user-name"><?= htmlspecialchars($currentUser['ho_ten']) ?></div>
                            </div>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Menu Items -->
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>profile.php">
                                <i class="bi bi-person-fill"></i>
                                <span>Thông tin cá nhân</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- Flash Messages -->
<?php
$flashSuccess = getFlashMessage('success');
$flashError = getFlashMessage('error');
$flashWarning = getFlashMessage('warning');
$flashInfo = getFlashMessage('info');
?>

<?php if ($flashSuccess || $flashError || $flashWarning || $flashInfo): ?>
<style>
/* Custom Alert Styles - Nổi bật hơn */
.custom-alert {
    border: none;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    animation: slideInDown 0.4s ease-out;
    position: relative;
    overflow: hidden;
}

.custom-alert::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
}

.custom-alert i {
    font-size: 1.5rem;
    margin-right: 0.75rem;
    vertical-align: middle;
}

.custom-alert .btn-close {
    padding: 0.75rem;
    opacity: 0.7;
}

.custom-alert .btn-close:hover {
    opacity: 1;
}

/* Success Alert */
.custom-alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border-left: 5px solid #28a745;
}

.custom-alert-success::before {
    background: #28a745;
}

.custom-alert-success i {
    color: #28a745;
}

/* Error Alert */
.custom-alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
    border-left: 5px solid #dc3545;
}

.custom-alert-danger::before {
    background: #dc3545;
}

.custom-alert-danger i {
    color: #dc3545;
}

/* Warning Alert */
.custom-alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
    border-left: 5px solid #ffc107;
}

.custom-alert-warning::before {
    background: #ffc107;
}

.custom-alert-warning i {
    color: #ffc107;
}

/* Info Alert */
.custom-alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
    border-left: 5px solid #17a2b8;
}

.custom-alert-info::before {
    background: #17a2b8;
}

.custom-alert-info i {
    color: #17a2b8;
}

@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Auto dismiss animation */
.custom-alert.dismissing {
    animation: slideOutUp 0.3s ease-out forwards;
}

@keyframes slideOutUp {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(-100%);
        opacity: 0;
    }
}
</style>

<div class="container-fluid flash-messages-container" style="padding-top: 1rem; padding-right: 1rem; padding-left: 1rem;">
    <?php if ($flashSuccess): ?>
        <div class="alert custom-alert custom-alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <strong>Thành công</strong> <?= $flashSuccess ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($flashError): ?>
        <div class="alert custom-alert custom-alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Lỗi</strong> <?= $flashError ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($flashWarning): ?>
        <div class="alert custom-alert custom-alert-warning alert-dismissible fade show" role="alert">
            <strong>Cảnh báo</strong> <?= $flashWarning ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($flashInfo): ?>
        <div class="alert custom-alert custom-alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle-fill"></i>
            <strong>Thông tin</strong> <?= $flashInfo ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto dismiss alerts after 5 seconds (longer for error messages)
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.custom-alert');
    alerts.forEach(function(alert) {
        // Thời gian hiển thị: 5 giây cho lỗi, 3 giây cho success
        const dismissTime = alert.classList.contains('custom-alert-danger') ? 5000 : 3000;
        
        setTimeout(function() {
            alert.classList.add('dismissing');
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 300);
        }, dismissTime);
    });
});
</script>
<?php endif; ?>

