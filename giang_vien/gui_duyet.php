<?php
/**
 * GỬI ĐỀ TÀI CHỜ DUYỆT
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$giangVienModel = new GiangVienModel();
$deTaiModel = new DeTaiModel();

$giangVien = $giangVienModel->getByNguoiDungId($user['id']);

// Xử lý gửi duyệt hàng loạt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_approval'])) {
    $selectedTopics = $_POST['selected_topics'] ?? [];
    $loaiDeTai = sanitize($_POST['loai'] ?? 'co_so_nganh'); // Lấy loại đề tài từ form
    
    if (empty($selectedTopics)) {
        setFlashMessage('error', 'Vui lòng chọn ít nhất một đề tài để gửi duyệt');
        redirect('giang_vien/danh_sach_de_tai.php?loai=' . $loaiDeTai);
    }
    
    // Không còn kiểm tra giới hạn số đề tài nữa - giảng viên có thể tạo không giới hạn
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($selectedTopics as $deTaiId) {
        $deTaiId = (int)$deTaiId;
        $deTai = $deTaiModel->findById($deTaiId);
        
        // Kiểm tra quyền và trạng thái
        if (!$deTai || $deTai['giang_vien_id'] != $giangVien['id']) {
            $errors[] = "Không có quyền gửi đề tài ID: $deTaiId";
            $errorCount++;
            continue;
        }
        
        if ($deTai['trang_thai'] !== 'nhap') {
            $errors[] = "Đề tài \"{$deTai['tieu_de']}\" không ở trạng thái nháp";
            $errorCount++;
            continue;
        }
        
        try {
            $deTaiModel->guiChoDuyet($deTaiId);
            
            // Gửi thông báo cho tất cả lãnh đạo
            $thongBaoModel = new ThongBaoModel();
            $lanhDaoModel = new LanhDaoModel();
            
            // Lấy danh sách tất cả lãnh đạo
            $danhSachLanhDao = $lanhDaoModel->findAll();
            
            foreach ($danhSachLanhDao as $lanhDao) {
                $thongBaoModel->taoThongBao(
                    $lanhDao['nguoi_dung_id'],
                    'Đề tài mới chờ duyệt',
                    "Giảng viên {$user['ho_ten']} đã gửi đề tài \"{$deTai['tieu_de']}\" chờ duyệt.",
                    'warning',
                    'lanh_dao/duyet_de_tai.php'
                );
            }
            
            $successCount++;
        } catch (Exception $e) {
            $errors[] = "Lỗi khi gửi đề tài \"{$deTai['tieu_de']}\": " . $e->getMessage();
            $errorCount++;
        }
    }
    
    // Hiển thị kết quả
    if ($successCount > 0) {
        setFlashMessage('success', "Đã gửi duyệt thành công $successCount đề tài");
    }
    
    if ($errorCount > 0) {
        setFlashMessage('error', "Có $errorCount lỗi: " . implode('; ', $errors));
    }
    
    redirect('giang_vien/danh_sach_de_tai.php?loai=' . $loaiDeTai);
}

// Xử lý gửi duyệt đơn lẻ (giữ nguyên logic cũ)
$deTaiId = (int)($_GET['id'] ?? 0);

if (!$deTaiId) {
    setFlashMessage('error', 'Đề tài không tồn tại');
    redirect('giang_vien/danh_sach_de_tai.php');
}

$deTai = $deTaiModel->findById($deTaiId);

if (!$deTai || $deTai['giang_vien_id'] != $giangVien['id']) {
    setFlashMessage('error', 'Bạn không có quyền gửi đề tài này');
    redirect('giang_vien/danh_sach_de_tai.php?loai=' . ($deTai['he_dao_tao'] ?? 'co_so_nganh'));
}

// Chỉ cho phép gửi đề tài nháp
if ($deTai['trang_thai'] !== 'nhap') {
    setFlashMessage('error', 'Chỉ có thể gửi đề tài đang ở trạng thái nháp');
    redirect('giang_vien/danh_sach_de_tai.php?loai=' . $deTai['he_dao_tao']);
}

// Lưu loại đề tài
$loaiDeTai = $deTai['he_dao_tao'];

// Không còn kiểm tra giới hạn số đề tài nữa - giảng viên có thể tạo không giới hạn

// Gửi chờ duyệt
try {
    $deTaiModel->guiChoDuyet($deTaiId);
    
    // Gửi thông báo cho tất cả lãnh đạo
    $thongBaoModel = new ThongBaoModel();
    $lanhDaoModel = new LanhDaoModel();
    
    // Lấy danh sách tất cả lãnh đạo
    $danhSachLanhDao = $lanhDaoModel->findAll();
    
    foreach ($danhSachLanhDao as $lanhDao) {
        $thongBaoModel->taoThongBao(
            $lanhDao['nguoi_dung_id'],
            'Đề tài mới chờ duyệt',
            "Giảng viên {$user['ho_ten']} đã gửi đề tài \"{$deTai['tieu_de']}\" chờ duyệt.",
            'warning',
            'lanh_dao/duyet_de_tai.php'
        );
    }
    
    setFlashMessage('success', 'Gửi đề tài chờ duyệt thành công');
} catch (Exception $e) {
    setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
}

redirect('giang_vien/danh_sach_de_tai.php?loai=' . $loaiDeTai);
