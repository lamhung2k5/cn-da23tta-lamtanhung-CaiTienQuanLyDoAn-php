<?php
/**
 * XỬ LÝ DUYỆT/TỪ CHỐI SINH VIÊN HÀNG LOẠT
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$dangKyModel = new DangKyDeTaiModel();
$thongBaoModel = new ThongBaoModel();
$deTaiModel = new DeTaiModel();
$giangVienModel = new GiangVienModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

if (!$giangVien) {
    setFlashMessage('error', 'Không tìm thấy thông tin giảng viên');
    redirect('../index.php');
}

// Xử lý duyệt hàng loạt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'duyet_hang_loat') {
    $dangKyIds = explode(',', $_POST['dang_ky_ids']);
    $dangKyIds = array_map('intval', array_filter($dangKyIds));
    
    if (empty($dangKyIds)) {
        setFlashMessage('error', 'Không có sinh viên nào được chọn');
        redirect('duyet_sinh_vien.php');
    }
    
    $result = $dangKyModel->duyetHangLoat($dangKyIds, $user['id']);
    
    if ($result['success']) {
        setFlashMessage('success', "Duyệt thành công {$result['so_duyet']} sinh viên");
    } else {
        setFlashMessage('error', $result['message']);
    }
    
    redirect('duyet_sinh_vien.php');
}

// Xử lý từ chối hàng loạt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'tu_choi_hang_loat') {
    $dangKyIds = explode(',', $_POST['dang_ky_ids']);
    $dangKyIds = array_map('intval', array_filter($dangKyIds));
    $lyDo = sanitize($_POST['ly_do']);
    
    if (empty($dangKyIds)) {
        setFlashMessage('error', 'Không có sinh viên nào được chọn');
        redirect('duyet_sinh_vien.php');
    }
    
    if (empty($lyDo)) {
        setFlashMessage('error', 'Vui lòng nhập lý do từ chối');
        redirect('duyet_sinh_vien.php');
    }
    
    $result = $dangKyModel->tuChoiHangLoat($dangKyIds, $lyDo);
    
    if ($result['success']) {
        setFlashMessage('success', "Từ chối thành công {$result['so_tu_choi']} sinh viên");
    } else {
        setFlashMessage('error', $result['message']);
    }
    
    redirect('duyet_sinh_vien.php');
}

// Xử lý duyệt tự động thông minh
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'duyet_tu_dong') {
    $duyetCSN = $_POST['duyet_csn'] === '1';
    $duyetCN = $_POST['duyet_cn'] === '1';
    
    if (!$duyetCSN && !$duyetCN) {
        setFlashMessage('error', 'Vui lòng chọn ít nhất một loại đề tài để duyệt');
        redirect('duyet_sinh_vien.php');
    }
    
    $result = $dangKyModel->duyetTuDongThongMinh($giangVien['id'], $duyetCSN, $duyetCN);
    
    if ($result['success']) {
        $message = "Duyệt tự động thành công: ";
        $message .= "Đã duyệt {$result['so_duyet']} sinh viên, ";
        $message .= "từ chối {$result['so_tu_choi']} sinh viên";
        setFlashMessage('success', $message);
    } else {
        setFlashMessage('error', $result['message']);
    }
    
    redirect('duyet_sinh_vien.php');
}

// Nếu không có action hợp lệ
redirect('duyet_sinh_vien.php');