<?php
/**
 * SIDEBAR LÃNH ĐẠO - Template chung
 * Sử dụng: include 'includes/sidebar_lanh_dao.php';
 * Biến $currentPage cần được set trước khi include
 */

// Nếu không có $currentPage, lấy từ tên file hiện tại
if (!isset($currentPage)) {
    $currentPage = basename($_SERVER['PHP_SELF']);
}
?>

<!-- Sidebar -->
<div class="col-md-2 sidebar">
    <nav class="nav flex-column">
        <div class="nav-section-title">QUẢN LÝ HỆ THỐNG</div>
        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
            <i class="bi bi-house-door"></i> Trang chủ
        </a>
        <a class="nav-link <?= $currentPage === 'duyet_de_tai.php' ? 'active' : '' ?>" href="duyet_de_tai.php">
            <i class="bi bi-journal-check"></i> Duyệt đề tài
        </a>
        <a class="nav-link" href="de_tai_da_duyet.php">
             <i class="bi bi-journal-check"></i> Đề tài đã duyệt
        </a>
        <a class="nav-link <?= $currentPage === 'danh_sach_phan_cong.php' ? 'active' : '' ?>" href="danh_sach_phan_cong.php">
            <i class="bi bi-person-check"></i> Phân công chấm
        </a>
        <a class="nav-link <?= $currentPage === 'xuat_bao_cao.php' ? 'active' : '' ?>" href="xuat_bao_cao.php">
            <i class="bi bi-file-earmark-text"></i> Xuất danh sách
        </a>
        <a class="nav-link <?= $currentPage === 'cai_dat_thong_so.php' ? 'active' : '' ?>" href="cai_dat_thong_so.php">
            <i class="bi bi-gear"></i> Cài đặt thông số
        </a>
        <a class="nav-link <?= $currentPage === 'quan_ly_nguoi_dung.php' ? 'active' : '' ?>" href="quan_ly_nguoi_dung.php">
            <i class="bi bi-people"></i> Quản lý tài khoản
        </a>
        
        <div class="nav-section-title">QUẢN LÝ NỘI DUNG</div>
        <a class="nav-link <?= $currentPage === 'quan_ly_noi_dung_do_an.php' ? 'active' : '' ?>" href="quan_ly_noi_dung_do_an.php">
            <i class="bi bi-file-earmark-text"></i> Thông báo đồ án
        </a>
        <a class="nav-link <?= $currentPage === 'quan_ly_thong_bao.php' ? 'active' : '' ?>" href="quan_ly_thong_bao.php">
            <i class="bi bi-megaphone"></i> Thông báo chung
        </a>
        <a class="nav-link <?= $currentPage === 'cau_hinh_menu.php' ? 'active' : '' ?>" href="cau_hinh_menu.php">
            <i class="bi bi-link-45deg"></i> Cập nhật liên kết
        </a>

        <div class="nav-section-title">CHỨC NĂNG GIẢNG VIÊN</div>
        <a class="nav-link" href="../giang_vien/dashboard.php">
            <i class="bi bi-person-workspace"></i> Chế độ Giảng viên
        </a>
    </nav>
</div>