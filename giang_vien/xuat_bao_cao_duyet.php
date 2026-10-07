<?php
/**
 * XUẤT BÁO CÁO DUYỆT SINH VIÊN
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$giangVienModel = new GiangVienModel();
$dangKyModel = new DangKyDeTaiModel();
$deTaiModel = new DeTaiModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

if (!$giangVien) {
    setFlashMessage('error', 'Không tìm thấy thông tin giảng viên');
    redirect('../index.php');
}

// Lấy danh sách sinh viên chờ duyệt
$danhSachChoDuyet = $dangKyModel->getDanhSachDangKy($giangVien['id'], ['trang_thai' => STATUS_CHO_DUYET]);

// Lọc dữ liệu theo filter
$filter = $_GET['filter'] ?? 'all';
$filteredData = $danhSachChoDuyet;

if ($filter === 'csn') {
    $filteredData = array_filter($danhSachChoDuyet, function($dk) {
        return $dk['he_dao_tao'] === 'co_so_nganh';
    });
} elseif ($filter === 'cn') {
    $filteredData = array_filter($danhSachChoDuyet, function($dk) {
        return $dk['he_dao_tao'] === 'chuyen_nganh';
    });
}

// Lấy thông tin đề tài
$thongTinDeTai = [];
foreach ($filteredData as $dk) {
    if (!isset($thongTinDeTai[$dk['de_tai_id']])) {
        $thongTinDeTai[$dk['de_tai_id']] = $deTaiModel->findById($dk['de_tai_id']);
    }
}

// Thiết lập header cho file Excel
$filename = 'danh_sach_duyet_sinh_vien_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Tạo output stream
$output = fopen('php://output', 'w');

// Thêm BOM để Excel hiển thị đúng tiếng Việt
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header của file CSV
fputcsv($output, [
    'STT',
    'Mã sinh viên',
    'Tên sinh viên',
    'Lớp',
    'Khóa học',
    'Chuyên ngành',
    'Email',
    'Tên đề tài',
    'Loại đề tài',
    'Số lượng hiện tại',
    'Số lượng tối đa',
    'Trạng thái',
    'Ngày đăng ký',
    'Ghi chú'
]);

// Dữ liệu
$index = 1;
foreach ($filteredData as $dk) {
    $deTai = $thongTinDeTai[$dk['de_tai_id']];
    $daDuSoLuong = ($deTai['so_luong_da_dang_ky'] >= $deTai['so_luong_sv']);
    
    fputcsv($output, [
        $index++,
        $dk['ma_sinh_vien'],
        $dk['ten_sinh_vien'],
        $dk['lop'],
        $dk['khoa_hoc'],
        $dk['chuyen_nganh'],
        $dk['email_sinh_vien'],
        $dk['tieu_de'],
        getHeDaoTaoLabel($dk['he_dao_tao']),
        $deTai['so_luong_da_dang_ky'],
        $deTai['so_luong_sv'],
        $daDuSoLuong ? 'Đề tài đã đủ' : 'Có thể duyệt',
        formatDate($dk['ngay_dang_ky']),
        $daDuSoLuong ? 'Không thể duyệt do đề tài đã đủ số lượng' : ''
    ]);
}

fclose($output);
exit;