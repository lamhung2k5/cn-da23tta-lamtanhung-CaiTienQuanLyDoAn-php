<?php
/**
 * XỬ LÝ PHÂN CÔNG CHẤM
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'phan_cong') {
    $dangKyId = (int)($_POST['dang_ky_id'] ?? 0);
    $giangVienId = $_POST['giang_vien_id'] ?? null;
    
    if (!$dangKyId) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đăng ký']);
        exit;
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Nếu giangVienId rỗng, set NULL (hủy phân công)
        if (empty($giangVienId)) {
            $stmt = $db->prepare("UPDATE dang_ky_de_tai SET giang_vien_cham_id = NULL WHERE id = :id");
            $stmt->execute(['id' => $dangKyId]);
        } else {
            $stmt = $db->prepare("UPDATE dang_ky_de_tai SET giang_vien_cham_id = :giang_vien_id WHERE id = :id");
            $stmt->execute([
                'giang_vien_id' => $giangVienId,
                'id' => $dangKyId
            ]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}
