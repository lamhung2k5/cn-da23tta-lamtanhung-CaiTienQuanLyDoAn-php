<?php
/**
 * XÓA ĐĂNG KÝ ĐỀ TÀI (CHỈ CHO TRẠNG THÁI TỪ CHỐI)
 */

require_once '../bootstrap.php';
requireRole(ROLE_SINH_VIEN);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$user = getCurrentUser();
$dangKyId = $_POST['dang_ky_id'] ?? null;

if (!$dangKyId) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đăng ký']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Lấy thông tin sinh viên
    $sinhVienModel = new SinhVienModel();
    $sinhVien = $sinhVienModel->getByNguoiDungId($user['id']);
    
    // Kiểm tra đăng ký có thuộc về sinh viên này không và trạng thái có phải là từ chối không
    $stmt = $db->prepare("
        SELECT dk.* 
        FROM dang_ky_de_tai dk
        WHERE dk.id = ? AND dk.sinh_vien_id = ?
    ");
    $stmt->execute([$dangKyId, $sinhVien['id']]);
    $dangKy = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dangKy) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đăng ký hoặc bạn không có quyền xóa']);
        exit;
    }
    
    // Chỉ cho phép xóa nếu trạng thái là từ chối
    if ($dangKy['trang_thai'] !== 'tu_choi') {
        echo json_encode(['success' => false, 'message' => 'Chỉ có thể xóa đăng ký bị từ chối']);
        exit;
    }
    
    // Xóa đăng ký
    $stmt = $db->prepare("DELETE FROM dang_ky_de_tai WHERE id = ?");
    $stmt->execute([$dangKyId]);
    
    echo json_encode(['success' => true, 'message' => 'Xóa đăng ký thành công']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
