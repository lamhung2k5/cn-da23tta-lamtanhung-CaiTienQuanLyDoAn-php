<?php
/**
 * XÓA ĐĂNG KÝ ĐỀ TÀI (CHO GIẢNG VIÊN)
 * Xóa đăng ký của sinh viên đã có đề tài được duyệt cùng loại
 */

require_once '../bootstrap.php';

// Cho phép cả giảng viên và lãnh đạo truy cập
if (!isLoggedIn() || ($_SESSION['vai_tro'] !== ROLE_GIANG_VIEN && $_SESSION['vai_tro'] !== ROLE_LANH_DAO)) {
    redirect('../auth/login.php');
}

$user = getCurrentUser();
$dangKyId = $_GET['id'] ?? null;
$loai = $_GET['loai'] ?? 'co_so_nganh';
$xem = $_GET['xem'] ?? null;

if (!$dangKyId) {
    setFlashMessage('error', 'Thiếu thông tin đăng ký');
    redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai);
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Lấy thông tin giảng viên
    $giangVienModel = new GiangVienModel();
    $giangVien = $giangVienModel->getByNguoiDungId($user['id']);
    
    // Kiểm tra đăng ký có thuộc về đề tài của giảng viên này không
    $stmt = $db->prepare("
        SELECT dk.*, dt.giang_vien_id, dt.tieu_de, dt.he_dao_tao
        FROM dang_ky_de_tai dk
        JOIN de_tai dt ON dk.de_tai_id = dt.id
        WHERE dk.id = ? AND dt.giang_vien_id = ?
    ");
    $stmt->execute([$dangKyId, $giangVien['id']]);
    $dangKy = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dangKy) {
        setFlashMessage('error', 'Không tìm thấy đăng ký hoặc bạn không có quyền xóa');
        redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai);
    }
    
    // Kiểm tra sinh viên đã có đề tài được duyệt cùng loại chưa
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM dang_ky_de_tai dk
        JOIN de_tai dt ON dk.de_tai_id = dt.id
        WHERE dk.sinh_vien_id = ? 
        AND dk.trang_thai = 'da_duyet'
        AND dt.he_dao_tao = ?
    ");
    $stmt->execute([$dangKy['sinh_vien_id'], $dangKy['he_dao_tao']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        setFlashMessage('error', 'Sinh viên này chưa có đề tài được duyệt cùng loại, không thể xóa');
        redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai . ($xem ? '&xem=' . $xem : ''));
    }
    
    // Xóa đăng ký
    $stmt = $db->prepare("DELETE FROM dang_ky_de_tai WHERE id = ?");
    $stmt->execute([$dangKyId]);
    
    setFlashMessage('success', 'Xóa đăng ký thành công');
    redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai . ($xem ? '&xem=' . $xem : ''));
    
} catch (Exception $e) {
    setFlashMessage('error', 'Lỗi: ' . $e->getMessage());
    redirect('giang_vien/duyet_sinh_vien.php?loai=' . $loai . ($xem ? '&xem=' . $xem : ''));
}
