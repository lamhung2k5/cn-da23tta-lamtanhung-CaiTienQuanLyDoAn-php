<?php
/**
 * DANH SÁCH ĐỀ TÀI - PHIÊN BẢN CÔNG KHAI
 * Hiển thị tất cả đề tài đã duyệt của tất cả giảng viên
 */

require_once 'bootstrap.php';

$pageTitle = 'Danh sách đề tài - Khoa CNTT';

// Lấy các tham số lọc
$heDaoTao = isset($_GET['he_dao_tao']) ? trim($_GET['he_dao_tao']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$giangVienFilter = isset($_GET['giang_vien']) ? trim($_GET['giang_vien']) : '';
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'created_at_desc';

// Kết nối database
$pdo = Database::getInstance()->getConnection();

// Lấy danh sách giảng viên cho bộ lọc
$sqlGiangVien = "SELECT gv.id, nd.ho_ten, nd.email 
                 FROM giang_vien gv 
                 JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id 
                 ORDER BY nd.ho_ten ASC";
$stmtGV = $pdo->query($sqlGiangVien);
$danhSachGiangVien = $stmtGV->fetchAll(PDO::FETCH_ASSOC);

// Xây dựng câu truy vấn
$sql = "SELECT dt.*, 
               gv.ma_giang_vien,
               nd.ho_ten as ten_giang_vien,
               nd.email as email_giang_vien,
               (dt.so_luong_sv - dt.so_luong_da_dang_ky) as con_lai
        FROM de_tai dt
        JOIN giang_vien gv ON dt.giang_vien_id = gv.id
        JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
        WHERE dt.trang_thai = 'da_duyet'
        AND dt.so_luong_da_dang_ky < dt.so_luong_sv";

$params = [];

if ($heDaoTao !== '' && in_array($heDaoTao, ['co_so_nganh', 'chuyen_nganh'])) {
    $sql .= " AND dt.he_dao_tao = :he_dao_tao";
    $params['he_dao_tao'] = $heDaoTao;
}

if ($giangVienFilter !== '') {
    $sql .= " AND gv.id = :giang_vien_id";
    $params['giang_vien_id'] = $giangVienFilter;
}

if ($search !== '') {
    $searchValue = '%' . $search . '%';
    $sql .= " AND (dt.tieu_de LIKE :search1 OR dt.mo_ta LIKE :search2 OR nd.ho_ten LIKE :search3)";
    $params['search1'] = $searchValue;
    $params['search2'] = $searchValue;
    $params['search3'] = $searchValue;
}

// Sắp xếp
switch ($sortBy) {
    case 'name_asc':
        $sql .= " ORDER BY dt.tieu_de ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY dt.tieu_de DESC";
        break;
    case 'slots_desc':
        $sql .= " ORDER BY con_lai DESC";
        break;
    case 'slots_asc':
        $sql .= " ORDER BY con_lai ASC";
        break;
    case 'created_at_asc':
        $sql .= " ORDER BY dt.created_at ASC";
        break;
    default:
        $sql .= " ORDER BY dt.created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$danhSachDeTai = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm để tạo URL với các tham số hiện tại
function buildFilterUrl($newParams = [])
{
    $params = $_GET;
    foreach ($newParams as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
            --glass-bg: rgba(255, 255, 255, 0.85);
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
            --glass-bg: rgba(30, 41, 59, 0.9);
        }

        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        [data-theme="dark"] .topic-card {
            background: var(--card-bg);
            box-shadow: 0 4px 20px var(--card-shadow);
        }

        [data-theme="dark"] .card-body {
            background: var(--card-bg);
            color: var(--text-primary);
        }

        [data-theme="dark"] .page-title,
        [data-theme="dark"] .breadcrumb-item a {
            color: #3b82f6;
        }

        [data-theme="dark"] .breadcrumb-item.active {
            color: var(--text-muted);
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        [data-theme="dark"] .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        [data-theme="dark"] .footer-title h4,
        [data-theme="dark"] .footer-subtitle {
            color: #f1f5f9;
        }

        [data-theme="dark"] .footer-contact p {
            color: #cbd5e1;
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
            text-shadow: none;
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
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
            color: white !important;
            transform: translateY(-3px) scale(1.05) !important;
            box-shadow: 0 12px 32px rgba(59, 130, 246, 0.4) !important;
        }

        .login-btn i {
            margin-right: 8px;
            font-size: 16px;
        }

        /* ============ DROPDOWN ============ */
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            padding: 10px;
        }

        .dropdown-item {
            padding: 12px 15px;
            border-radius: 8px;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            color: #0052a3;
        }

        /* ============ PAGE CONTENT ============ */
        .page-title {
            color: #0052a3;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 40px 0 30px;
            text-align: center;
            letter-spacing: -0.5px;
        }

        /* ============ TOPIC CARDS ============ */
        .topic-card {
            background: white;
            border: none;
            border-radius: 18px;
            margin-bottom: 25px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .topic-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 82, 163, 0.15);
        }

        .card-header-csn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            padding: 20px 25px;
        }

        .card-header-cn {
            background: linear-gradient(135deg, #10b981, #059669);
            padding: 20px 25px;
        }

        .card-body {
            padding: 25px;
        }

        /* ============ FOOTER ============ */
        .footer {
            background: linear-gradient(135deg, #0052a3 0%, #003d7a 100%);
            color: white;
            padding: 50px 0 25px;
            margin-top: 60px;
        }

        .footer-title h4 {
            color: white;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 1.3rem;
        }

        .footer-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        .footer-divider {
            width: 100px;
            height: 3px;
            background: linear-gradient(135deg, #ffcc00 0%, #ffa500 100%);
            margin: 15px 0 20px;
        }

        .footer-contact p {
            margin-bottom: 12px;
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
        }

        .footer a {
            color: #ffcc00;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer a:hover {
            color: #ffa500;
        }

        .social-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            transition: all 0.3s;
            text-decoration: none;
        }

        .social-icon:hover {
            transform: scale(1.1);
        }

        .facebook {
            background: #1877f2;
            color: white;
        }

        .youtube {
            background: #ff0000;
            color: white;
        }

        .github {
            background: #333;
            color: white;
        }

        .brand-line {
            height: 2px;
            background: linear-gradient(90deg, #ffcc00 0%, transparent 100%);
        }

        /* ============ BREADCRUMB ============ */
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 25px 0 20px 0;
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
            color: #7f8c8d;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <header class="top-header">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
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
                        <a href="#" class="nav-item dropdown-toggle" data-bs-toggle="dropdown">
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
                                    target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Biểu mẫu cho giảng
                                    viên</a></li>
                        </ul>
                    </li>
                    <li><a href="auth/login.php" class="login-btn"><i class="bi bi-box-arrow-in-right"></i> Đăng
                            nhập</a></li>
                    <li>

                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Breadcrumb -->


        <h1 class="page-title">
            Danh sách đề tài
        </h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px; font-size: 1.1rem;">Tất cả đề tài đã được duyệt
            của các giảng viên</p>

        <!-- Search and Filter -->
        <div class="card mb-4" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Loại đề tài</label>
                        <select name="he_dao_tao" class="form-select" style="border-radius: 10px;"
                            onchange="this.form.submit()">
                            <option value="">-- Tất cả --</option>
                            <option value="co_so_nganh" <?= $heDaoTao === 'co_so_nganh' ? 'selected' : '' ?>>Cơ sở ngành
                            </option>
                            <option value="chuyen_nganh" <?= $heDaoTao === 'chuyen_nganh' ? 'selected' : '' ?>>Chuyên ngành
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Giảng viên</label>
                        <select name="giang_vien" class="form-select" style="border-radius: 10px;"
                            onchange="this.form.submit()">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($danhSachGiangVien as $gv): ?>
                                <option value="<?= $gv['id'] ?>" <?= $giangVienFilter == $gv['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($gv['ho_ten']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tìm kiếm</label>
                        <input type="text" name="search" class="form-control" style="border-radius: 10px;"
                            placeholder="Tìm theo tên đề tài, giảng viên..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px;">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Results Count & Sort -->
        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <span class="badge bg-primary fs-6" style="padding: 10px 15px; border-radius: 20px;">
                Tổng: <?= count($danhSachDeTai) ?> đề tài
            </span>
            <div class="d-flex align-items-center gap-3">
                <div class="sort-dropdown d-flex align-items-center gap-2">
                    <label class="mb-0 text-nowrap">Sắp xếp theo:</label>
                    <select class="form-select" onchange="window.location.href=this.value" style="width: auto;">
                        <option
                            value="?he_dao_tao=<?= $heDaoTao ?>&search=<?= urlencode($search) ?>&giang_vien=<?= $giangVienFilter ?>&sort=created_at_desc"
                            <?= $sortBy === 'created_at_desc' ? 'selected' : '' ?>>Mới nhất</option>
                        <option
                            value="?he_dao_tao=<?= $heDaoTao ?>&search=<?= urlencode($search) ?>&giang_vien=<?= $giangVienFilter ?>&sort=created_at_asc"
                            <?= $sortBy === 'created_at_asc' ? 'selected' : '' ?>>Cũ nhất</option>

                    </select>
                </div>
                <a href="auth/login.php?role=sinh_vien" class="btn btn-primary"
                    style="border-radius: 25px; padding: 12px 30px; font-weight: 700; box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3); transition: all 0.3s ease; white-space: nowrap;">
                    Đăng nhập để đăng ký đề tài
                </a>
            </div>
        </div>

        <!-- Topic List -->
        <?php if (empty($danhSachDeTai)): ?>
            <div class="card topic-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3" style="font-size: 1.2rem;">Không tìm thấy đề tài nào</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($danhSachDeTai as $dt): ?>
                <div class="card topic-card">
                    <div
                        class="card-header text-white <?= $dt['he_dao_tao'] === 'co_so_nganh' ? 'card-header-csn' : 'card-header-cn' ?>">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <h5 class="mb-2" style="font-size: 1.2rem;"><?= htmlspecialchars($dt['tieu_de']) ?></h5>
                                <small><i class="bi bi-person"></i> Giảng viên:
                                    <strong><?= htmlspecialchars($dt['ten_giang_vien']) ?></strong></small>
                            </div>
                            <span class="badge bg-light text-dark" style="font-size: 0.9rem; padding: 8px 12px;">
                                <?= $dt['he_dao_tao'] === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <strong>Số lượng còn:</strong>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                    <?= $dt['con_lai'] ?> / <?= $dt['so_luong_sv'] ?> chỗ
                                </span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Email Giảng viên:</strong>
                                <a href="mailto:<?= htmlspecialchars($dt['email_giang_vien']) ?>" class="text-decoration-none fw-semibold">
                                    <?= htmlspecialchars($dt['email_giang_vien']) ?>
                                </a>
                            </div>
                        </div>

                        <!-- Mô tả đề tài -->
                        <?php if (!empty($dt['mo_ta'])): ?>
                            <div class="mb-3">
                                <strong class="d-block mb-1 text-secondary">
                                    Mô tả đề tài:
                                </strong>
                                <div class="p-3 bg-light rounded-3 border-start border-4 border-primary fs-6" style="white-space: pre-line;">
                                    <?= htmlspecialchars($dt['mo_ta']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Công nghệ sử dụng -->
                        <?php if (!empty($dt['cong_nghe'])): ?>
                            <div class="mb-3">
                                <strong class="d-block mb-1 text-secondary">
                                    Công nghệ sử dụng:
                                </strong>
                                <div>
                                    <?php 
                                    $congNghes = array_map('trim', explode(',', $dt['cong_nghe']));
                                    foreach ($congNghes as $cn):
                                        if (empty($cn)) continue;
                                    ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1 mb-1 px-3 py-2 rounded-pill fs-7">
                                            <?= htmlspecialchars($cn) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Yêu cầu sinh viên -->
                        <?php if (!empty($dt['yeu_cau_sinh_vien'])): ?>
                            <div class="mb-3">
                                <strong class="d-block mb-1 text-secondary">
                                    Yêu cầu sinh viên:
                                </strong>
                                <div class="alert alert-info border-0 rounded-3 mb-0" style="background-color: rgba(13, 202, 240, 0.08); color: #055160;">
                                    <?= htmlspecialchars($dt['yeu_cau_sinh_vien']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Ghi chú -->
                        <?php if (!empty($dt['ghi_chu'])): ?>
                            <div class="mb-0">
                                <strong class="d-block mb-1 text-secondary">
                                    Ghi chú:
                                </strong>
                                <div class="alert alert-warning border-0 rounded-3 mb-0" style="background-color: rgba(255, 193, 7, 0.08); color: #664d03;">
                                    <?= htmlspecialchars($dt['ghi_chu']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row">
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
</body>

</html>