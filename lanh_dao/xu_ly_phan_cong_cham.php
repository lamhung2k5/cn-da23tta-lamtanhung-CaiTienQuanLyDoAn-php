<?php
/**
 * XỬ LÝ PHÂN CÔNG GIẢNG VIÊN CHẤM
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Phương thức không hợp lệ');
}

$dangKyId = (int)($_POST['dang_ky_id'] ?? 0);
$giangVienChamId = (int)($_POST['giang_vien_cham_id'] ?? 0);

if ($dangKyId <= 0) {
    jsonError('ID đăng ký không hợp lệ');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Kiểm tra đăng ký tồn tại
    $stmt = $db->prepare("SELECT * FROM dang_ky_de_tai WHERE id = :id");
    $stmt->execute(['id' => $dangKyId]);
    $dangKy = $stmt->fetch();
    
    if (!$dangKy) {
        jsonError('Không tìm thấy đăng ký');
    }
    
    // Nếu giangVienChamId = 0 thì xóa phân công
    if ($giangVienChamId === 0) {
        $stmt = $db->prepare("UPDATE dang_ky_de_tai 
                             SET giang_vien_cham_id = NULL, 
                                 ngay_phan_cong_cham = NULL 
                             WHERE id = :id");
        $stmt->execute(['id' => $dangKyId]);
        jsonSuccess('Đã xóa phân công giảng viên chấm');
    }
    
    // Kiểm tra giảng viên tồn tại
    $stmt = $db->prepare("SELECT * FROM giang_vien WHERE id = :id");
    $stmt->execute(['id' => $giangVienChamId]);
    $giangVien = $stmt->fetch();
    
    if (!$giangVien) {
        jsonError('Không tìm thấy giảng viên');
    }
    
    // Cập nhật phân công
    $stmt = $db->prepare("UPDATE dang_ky_de_tai 
                         SET giang_vien_cham_id = :giang_vien_cham_id,
                             ngay_phan_cong_cham = NOW()
                         WHERE id = :id");
    
    $stmt->execute([
        'giang_vien_cham_id' => $giangVienChamId,
        'id' => $dangKyId
    ]);
    
    jsonSuccess('Phân công giảng viên chấm thành công');
    
} catch (Exception $e) {
    jsonError('Lỗi: ' . $e->getMessage());
}
