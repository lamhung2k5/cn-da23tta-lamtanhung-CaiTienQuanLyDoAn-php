<?php
/**
 * CHI TIẾT GIẢNG VIÊN - VERSION 2
 * Phiên bản đơn giản, không phụ thuộc vào framework
 */

// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Lấy email từ URL
$email = $_GET['email'] ?? '';
$filter = $_GET['filter'] ?? 'da_duyet'; // Mặc định chỉ hiển thị đề tài đã duyệt
$he_dao_tao_filter = $_GET['he_dao_tao'] ?? 'all'; // Bộ lọc hệ đào tạo

if (empty($email)) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <title>Lỗi - Thiếu tham số</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 40px;
            }

            .error {
                background: #f8d7da;
                color: #721c24;
                padding: 20px;
                border-radius: 5px;
            }
        </style>
    </head>

    <body>
        <div class="error">
            <h2>Lỗi: Thiếu tham số email</h2>
            <p>Cách sử dụng: <code>giang_vien_chi_tiet_v2.php?email=oane@tvu.edu.vn</code></p>
            <p><a href="khoa_cntt.php">Quay lại</a></p>
        </div>
    </body>

    </html>
    <?php
    exit;
}

// Kết nối database qua config chung (hỗ trợ cả localhost & InfinityFree)
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <title>Lỗi Database</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 40px;
            }

            .error {
                background: #f8d7da;
                color: #721c24;
                padding: 20px;
                border-radius: 5px;
            }
        </style>
    </head>

    <body>
        <div class="error">
            <h2>Lỗi kết nối Database</h2>
            <p><?= htmlspecialchars($e->getMessage()) ?></p>
            <p><a href="khoa_cntt.php">← Quay lại</a></p>
        </div>
    </body>

    </html>
    <?php
    exit;
}



// Tìm người dùng trong database
$stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE email = ?");
$stmt->execute([$email]);
$nguoiDung = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nguoiDung) {
    // Fallback: tìm trong danh sách faculty_members.php
    $allFaculty = require_once __DIR__ . '/config/faculty_members.php';
    $foundInFaculty = null;
    foreach ($allFaculty as $member) {
        if (strtolower($member['email']) === strtolower($email)) {
            $foundInFaculty = $member;
            break;
        }
    }

    if (!$foundInFaculty) {
        // Không tìm thấy ở đâu → redirect
        header('Location: khoa_cntt.php');
        exit;
    }

    // Dựng $nguoiDung giả từ faculty_members để trang vẫn hiển thị
    $nguoiDung = [
        'id' => null,
        'ho_ten' => $foundInFaculty['name'],
        'email' => $foundInFaculty['email'],
        'vai_tro' => 'giang_vien',
    ];
    // Dựng $giangVien giả
    $giangVien = [
        'id' => null,
        'nguoi_dung_id' => null,
        'khoa' => $foundInFaculty['khoa'] ?? '',
        'so_dien_thoai' => $foundInFaculty['phone'] ?? '',
        'chuc_danh' => $foundInFaculty['position'] ?? '',
        'avatar' => null,
    ];
    $danhSachDeTai = [];
    $tatCaDeTai = [];
    $thongKe = ['tong' => 0, 'nhap' => 0, 'cho_duyet' => 0, 'da_duyet' => 0, 'tu_choi' => 0, 'co_so_nganh' => 0, 'chuyen_nganh' => 0];
} else {
    // Tìm thông tin giảng viên
    $stmt = $pdo->prepare("SELECT * FROM giang_vien WHERE nguoi_dung_id = ?");
    $stmt->execute([$nguoiDung['id']]);
    $giangVien = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy danh sách đề tài (nếu có profile giảng viên)
    $danhSachDeTai = [];
    if ($giangVien) {
        $sql = "SELECT * FROM de_tai WHERE giang_vien_id = ? AND trang_thai = 'da_duyet'";
        $params = [$giangVien['id']];

        // Thêm điều kiện lọc theo hệ đào tạo
        if ($he_dao_tao_filter !== 'all') {
            $sql .= " AND he_dao_tao = ?";
            $params[] = $he_dao_tao_filter;
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $danhSachDeTai = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy thống kê đề tài (luôn lấy tất cả để hiển thị số liệu chính xác)
    $tatCaDeTai = [];
    if ($giangVien) {
        $stmt = $pdo->prepare("SELECT * FROM de_tai WHERE giang_vien_id = ?");
        $stmt->execute([$giangVien['id']]);
        $tatCaDeTai = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $thongKe = [
        'tong' => count($tatCaDeTai),
        'nhap' => 0,
        'cho_duyet' => 0,
        'da_duyet' => 0,
        'tu_choi' => 0,
        'co_so_nganh' => 0,
        'chuyen_nganh' => 0
    ];
} // end else (nguoiDung found in DB)

foreach ($tatCaDeTai as $dt) {
    $thongKe[$dt['trang_thai']]++;
    $thongKe[$dt['he_dao_tao']]++;
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết giảng viên - <?= htmlspecialchars($nguoiDung['ho_ten']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        /* ============ DARK MODE CSS VARIABLES ============ */
        :root {
            --bg-primary: #f5f7fa;
            --bg-secondary: #ffffff;
            --text-primary: #333;
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

        [data-theme="dark"] .card {
            background: var(--card-bg);
        }

        [data-theme="dark"] .card-body {
            color: var(--text-primary);
        }

        [data-theme="dark"] .profile-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        [data-theme="dark"] .stats-card {
            background: var(--card-bg);
        }

        [data-theme="dark"] .stats-number {
            color: var(--text-primary);
        }

        [data-theme="dark"] .topic-card {
            background: var(--card-bg);
        }

        [data-theme="dark"] .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
    </style>

    <style>
        .profile-header {
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            border: 3px solid white;
            color: white;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .topic-card {
            background: white;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #007bff;
        }

        .topic-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .topic-meta {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.75rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-nhap {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-cho_duyet {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-da_duyet {
            background: #e8f5e8;
            color: #388e3c;
        }

        .status-tu_choi {
            background: #ffebee;
            color: #d32f2f;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
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

        .filter-tabs {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border: 2px solid #e9ecef;
            background: white;
            color: #6c757d;
            text-decoration: none;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .filter-btn:hover {
            border-color: #0052a3;
            color: #0052a3;
            text-decoration: none;
        }

        .filter-btn.active {
            background: #0052a3;
            border-color: #0052a3;
            color: white;
        }

        .filter-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }

        .filter-btn.active .filter-count {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Action Buttons Styling */
        .action-buttons .btn {
            border-radius: 25px;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .action-buttons .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);
            border-color: #ffc107;
            color: #212529;
        }

        .action-buttons .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
            background: linear-gradient(135deg, #ffcd39 0%, #ffa000 100%);
        }

        .action-buttons .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border-color: #17a2b8;
            color: white;
        }

        .action-buttons .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
        }

        .action-buttons .btn-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background: #f8f9fa;
            border-color: #dee2e6;
        }

        /* Back Button Styling - giống như trong hình */
        .btn-back {
            background: white !important;
            border: 2px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 25px !important;
            color: #0052a3 !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            padding: 12px 24px !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2) !important;
            min-width: 120px !important;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.95) !important;
            color: #003d7a !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }

        .btn-back:active {
            transform: translateY(0) !important;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.2) !important;
        }

        .btn-back i {
            margin-right: 8px !important;
            font-size: 14px !important;
        }

        /* Topic Actions Styling */
        .topic-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .topic-actions .btn {
            border-radius: 20px;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .topic-actions .btn-outline-primary {
            border-color: #0052a3;
            color: #0052a3;
        }

        .topic-actions .btn-outline-primary:hover {
            background: #0052a3;
            border-color: #0052a3;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 82, 163, 0.3);
        }

        .topic-actions .btn-primary {
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
            border-color: #0052a3;
        }

        .topic-actions .btn-primary:hover {
            background: linear-gradient(135deg, #003d7a 0%, #002a54 100%);
            border-color: #003d7a;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 82, 163, 0.4);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="profile-avatar">
                        <?php
                        // Kiểm tra xem có ảnh đại diện không
                        $avatarContent = '<i class="bi bi-person-fill"></i>'; // Mặc định
                        
                        if ($giangVien && is_array($giangVien) && !empty($giangVien['avatar'])) {
                            $avatarPath = 'uploads/avatars/' . $giangVien['avatar'];
                            if (file_exists($avatarPath)) {
                                $avatarContent = '<img src="' . $avatarPath . '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
                            }
                        }

                        echo $avatarContent;
                        ?>
                    </div>
                </div>
                <div class="col">
                    <h2 class="mb-2"><?= htmlspecialchars($nguoiDung['ho_ten']) ?></h2>
                    <p class="mb-1"><i class="bi bi-envelope"></i> <?= htmlspecialchars($nguoiDung['email']) ?></p>
                    <?php if ($giangVien): ?>

                        <?php if ($giangVien['khoa']): ?>
                            <p class="mb-1"><i class="bi bi-building"></i> <?= htmlspecialchars($giangVien['khoa']) ?></p>
                        <?php endif; ?>
                        <?php
                        // Lấy số điện thoại từ database hoặc config
                        $soDienThoai = null;
                        if ($giangVien && is_array($giangVien) && !empty($giangVien['so_dien_thoai'])) {
                            $soDienThoai = $giangVien['so_dien_thoai'];
                        } else {
                            // Nếu không có trong database, lấy từ config faculty_members
                            $faculty_members = require_once __DIR__ . '/config/faculty_members.php';
                            foreach ($faculty_members as $member) {
                                if ($member['email'] === $nguoiDung['email']) {
                                    $soDienThoai = $member['phone'];
                                    break;
                                }
                            }
                        }

                        if ($soDienThoai): ?>
                            <p class="mb-0"><i class="bi bi-telephone"></i> <?= htmlspecialchars($soDienThoai) ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="col-auto">
                    <a href="khoa_cntt.php" class="btn btn-back">
                        Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (!$giangVien): ?>
            <!-- Thông báo chưa có profile -->
            <div class="alert alert-warning mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-3"></i>
                    <div>
                        <strong>Chưa có thông tin giảng viên</strong><br>
                        <small>Người dùng này chưa có profile giảng viên trong hệ thống.</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Bộ lọc hệ đào tạo -->
        <?php if ($giangVien && !empty($tatCaDeTai)): ?>
            <div class="filter-tabs">
                <div class="d-flex flex-wrap align-items-center">
                    <span class="me-3 fw-bold text-muted">Lọc theo hệ đề tài :</span>

                    <a href="?email=<?= urlencode($email) ?>&filter=all&he_dao_tao=co_so_nganh"
                        class="filter-btn <?= $he_dao_tao_filter === 'co_so_nganh' ? 'active' : '' ?>">
                        <span class="text-primary fw-bold">Cơ sở ngành</span>
                        <span class="filter-count"><?= $thongKe['co_so_nganh'] ?></span>
                    </a>

                    <a href="?email=<?= urlencode($email) ?>&filter=all&he_dao_tao=chuyen_nganh"
                        class="filter-btn <?= $he_dao_tao_filter === 'chuyen_nganh' ? 'active' : '' ?>">
                        <span class="text-success fw-bold">Chuyên ngành</span>
                        <span class="filter-count"><?= $thongKe['chuyen_nganh'] ?></span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Danh sách đề tài -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-journal-text"></i>
                    <?php
                    $filterText = '';
                    switch ($filter) {
                        case 'da_duyet':
                            $filterText = 'Đề tài đã duyệt';
                            break;
                        default:
                            $filterText = 'Danh sách đề tài';
                    }

                    // Thêm thông tin hệ đào tạo vào tiêu đề
                    if ($he_dao_tao_filter === 'co_so_nganh') {
                        $filterText .= ' - Cơ sở ngành';
                    } elseif ($he_dao_tao_filter === 'chuyen_nganh') {
                        $filterText .= ' - Chuyên ngành';
                    }

                    echo $filterText;
                    ?>
                </h5>
                <span class="badge bg-primary"><?= count($danhSachDeTai) ?> đề tài</span>
            </div>
            <div class="card-body">
                <?php if (empty($danhSachDeTai)): ?>
                    <div class="empty-state">
                        <i class="bi bi-journal-x" style="font-size: 3rem; opacity: 0.5;"></i>
                        <h5 class="mt-3">
                            <?php
                            switch ($filter) {
                                case 'nhap':
                                    echo 'Không có đề tài nháp';
                                    break;
                                case 'da_duyet':
                                    echo 'Không có đề tài đã duyệt';
                                    break;
                                default:
                                    echo 'Chưa có đề tài nào';
                            }
                            ?>
                        </h5>
                        <p>
                            <?php if ($filter !== 'all'): ?>
                                <a href="?email=<?= urlencode($email) ?>&filter=all" class="btn btn-outline-primary btn-sm">
                                    Xem tất cả đề tài
                                </a>
                            <?php else: ?>
                                Giảng viên này chưa tạo đề tài nào trong hệ thống
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($danhSachDeTai as $deTai): ?>
                        <div class="topic-card">
                            <div class="topic-title">
                                <?= htmlspecialchars($deTai['tieu_de']) ?>
                            </div>
                            <div class="topic-meta">
                                <span class="me-3">
                                    <i class="bi bi-diagram-3"></i>
                                    <span
                                        class="<?= $deTai['he_dao_tao'] === 'co_so_nganh' ? 'text-primary' : 'text-success' ?> fw-bold">
                                        <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?>
                                    </span>
                                </span>
                                <span class="me-3">
                                    <i class="bi bi-people"></i>
                                    <?= $deTai['so_luong_sv'] ?> sinh viên
                                </span>
                                <span class="me-3">
                                    <i class="bi bi-calendar"></i>
                                    <?= date('d/m/Y', strtotime($deTai['created_at'])) ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <?php
                                    $statusText = '';
                                    switch ($deTai['trang_thai']) {
                                        case 'nhap':
                                            $statusText = 'Nháp';
                                            break;
                                        case 'cho_duyet':
                                            $statusText = 'Chờ duyệt';
                                            break;
                                        case 'da_duyet':
                                            $statusText = 'Đã duyệt';
                                            break;
                                        case 'tu_choi':
                                            $statusText = 'Từ chối';
                                            break;
                                        default:
                                            $statusText = ucfirst($deTai['trang_thai']);
                                    }
                                    ?>
                                    <span class="status-badge status-<?= $deTai['trang_thai'] ?>">
                                        <?= $statusText ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <?php if (!empty($deTai['mo_ta'])): ?>
                                        <small class="text-muted">
                                            <strong>Mô tả:</strong>
                                            <?= mb_substr(strip_tags($deTai['mo_ta']), 0, 150) ?>
                                            <?= mb_strlen($deTai['mo_ta']) > 150 ? '...' : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="topic-actions">
                                    <a href="mailto:<?= htmlspecialchars($nguoiDung['email']) ?>"
                                        class="btn btn-outline-primary btn-sm me-2" title="Liên hệ giảng viên">
                                        <i class="bi bi-envelope"></i> Liên hệ
                                    </a>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#detailModal<?= $deTai['id'] ?>" title="Xem chi tiết đề tài">
                                        <i class="bi bi-search"></i> Xem chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal chi tiết đề tài -->
                        <div class="modal fade" id="detailModal<?= $deTai['id'] ?>" tabindex="-1"
                            aria-labelledby="detailModalLabel<?= $deTai['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="detailModalLabel<?= $deTai['id'] ?>">
                                            <i class="bi bi-journal-text"></i> Chi tiết đề tài
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <h4 class="text-primary mb-3"><?= htmlspecialchars($deTai['tieu_de']) ?></h4>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <strong><i class="bi bi-person-badge"></i> Giảng viên hướng dẫn:</strong><br>
                                                <span class="text-muted"><?= htmlspecialchars($nguoiDung['ho_ten']) ?></span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="bi bi-envelope"></i> Email liên hệ:</strong><br>
                                                <a href="mailto:<?= htmlspecialchars($nguoiDung['email']) ?>"
                                                    class="text-decoration-none">
                                                    <?= htmlspecialchars($nguoiDung['email']) ?>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <strong><i class="bi bi-diagram-3"></i> Loại đề tài:</strong><br>
                                                <span
                                                    class="badge <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? 'bg-primary' : 'bg-success' ?>">
                                                    <?= $deTai['he_dao_tao'] === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?>
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong><i class="bi bi-people"></i> Số lượng sinh viên:</strong><br>
                                                <span class="text-muted"><?= $deTai['so_luong_sv'] ?> sinh viên</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong><i class="bi bi-calendar"></i> Ngày tạo:</strong><br>
                                                <span
                                                    class="text-muted"><?= date('d/m/Y', strtotime($deTai['created_at'])) ?></span>
                                            </div>
                                        </div>



                                        <?php if (!empty($deTai['mo_ta'])): ?>
                                            <div class="row">
                                                <div class="col-12">
                                                    <strong><i class="bi bi-file-text"></i> Mô tả chi tiết:</strong>
                                                    <div class="mt-2 p-3 bg-light rounded">
                                                        <?= nl2br(htmlspecialchars($deTai['mo_ta'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($deTai['yeu_cau'])): ?>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <strong><i class="bi bi-list-check"></i> Yêu cầu:</strong>
                                                    <div class="mt-2 p-3 bg-light rounded">
                                                        <?= nl2br(htmlspecialchars($deTai['yeu_cau'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($deTai['ket_qua_mong_doi'])): ?>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <strong><i class="bi bi-trophy"></i> Kết quả mong đợi:</strong>
                                                    <div class="mt-2 p-3 bg-light rounded">
                                                        <?= nl2br(htmlspecialchars($deTai['ket_qua_mong_doi'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="mailto:<?= htmlspecialchars($nguoiDung['email']) ?>" class="btn btn-warning">
                                            <i class="bi bi-envelope"></i> Liên hệ giảng viên
                                        </a>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="bi bi-x-circle"></i> Đóng
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
    </script>
</body>

</html>