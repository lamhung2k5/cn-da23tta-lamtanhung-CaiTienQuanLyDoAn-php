<?php
/**
 * XỬ LÝ DUYỆT/TỪ CHỐI SINH VIÊN
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

// Xử lý duyệt
if (isset($_GET['action']) && $_GET['action'] === 'duyet' && isset($_GET['id'])) {
    $dangKyId = (int)$_GET['id'];
    $loai = isset($_GET['loai']) ? $_GET['loai'] : 'co_so_nganh';
    $xem = isset($_GET['xem']) ? (int)$_GET['xem'] : null;
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
    
    $result = $dangKyModel->duyetSinhVien($dangKyId, $user['id']);
    
    if ($result['success']) {
        // Lấy thông tin để gửi thông báo
        $dangKy = $dangKyModel->findById($dangKyId);
        $deTai = $deTaiModel->findById($dangKy['de_tai_id']);
        
        // Gửi thông báo cho sinh viên
        $thongBaoModel->thongBaoSinhVienDuocDuyet($dangKy['sinh_vien_id'], $deTai['tieu_de']);
        
        setFlashMessage('success', 'Duyệt sinh viên thành công');
    } else {
        setFlashMessage('error', $result['message']);
    }
    
    // Redirect về trang phù hợp
    if ($redirect === 'dashboard') {
        redirect('giang_vien/dashboard.php');
    } else if ($xem) {
        redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai . '&xem=' . $xem);
    } else {
        redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai);
    }
}

// Xử lý từ chối
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'tu_choi') {
    $dangKyId = (int)$_POST['dang_ky_id'];
    $lyDo = sanitize($_POST['ly_do']);
    $loai = isset($_POST['loai']) ? $_POST['loai'] : 'co_so_nganh';
    $xem = isset($_POST['xem']) ? (int)$_POST['xem'] : null;
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';
    
    if (empty($lyDo)) {
        setFlashMessage('error', 'Vui lòng nhập lý do từ chối');
        if ($redirect === 'dashboard') {
            redirect('giang_vien/dashboard.php');
        } else {
            redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai);
        }
    }
    
    $result = $dangKyModel->tuChoiSinhVien($dangKyId, $lyDo);
    
    if ($result['success']) {
        // Lấy thông tin để gửi thông báo
        $dangKy = $dangKyModel->findById($dangKyId);
        $deTai = $deTaiModel->findById($dangKy['de_tai_id']);
        
        // Gửi thông báo cho sinh viên
        $thongBaoModel->thongBaoSinhVienBiTuChoi($dangKy['sinh_vien_id'], $deTai['tieu_de'], $lyDo);
        
        setFlashMessage('success', 'Từ chối sinh viên thành công');
    } else {
        setFlashMessage('error', $result['message']);
    }
    
    // Redirect về trang phù hợp
    if ($redirect === 'dashboard') {
        redirect('giang_vien/dashboard.php');
    } else if ($xem) {
        redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai . '&xem=' . $xem);
    } else {
        redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai);
    }
}

// Nếu không có action hợp lệ
redirect('giang_vien/duyet_sinh_vien.php');
