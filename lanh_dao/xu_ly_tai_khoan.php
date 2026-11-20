<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_LANH_DAO);
requirePost();
$db = Database::getInstance()->getConnection();
$targetId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
$actor = (new NguoiDungModel())->findById($_SESSION['user_id']);
if (!$targetId || !authenticateSchoolAccount($actor['email'], $_POST['current_password'] ?? '')['success']) {
    securityDeny(403, 'Thông tin hoặc mật khẩu xác nhận không hợp lệ.');
}
try {
    $db->beginTransaction();
    // Serialize account management, including the last active leadership account.
    $leaders = $db->query("SELECT nd.id FROM nguoi_dung nd JOIN vai_tro vt ON vt.id=nd.vai_tro_id WHERE vt.ma_vai_tro='lanh_dao' AND nd.trang_thai='active' ORDER BY nd.id FOR UPDATE")->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $db->prepare('SELECT nd.*,vt.ma_vai_tro FROM nguoi_dung nd JOIN vai_tro vt ON vt.id=nd.vai_tro_id WHERE nd.id=? FOR UPDATE');
    $stmt->execute([$targetId]);
    $target = $stmt->fetch();
    if (!$target) { throw new DomainException('Tài khoản không tồn tại.'); }
    if ($action === 'set_status') {
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['active','inactive'], true)) { throw new DomainException('Trạng thái không hợp lệ.'); }
        if ($status === 'inactive' && ((int)$targetId === (int)$_SESSION['user_id'] ||
            ($target['ma_vai_tro'] === ROLE_LANH_DAO && $target['trang_thai'] === 'active' && count($leaders)<=1))) {
            throw new DomainException('Không thể tự khóa hoặc khóa Lãnh đạo cuối cùng.');
        }
        $stmt = $db->prepare('UPDATE nguoi_dung SET trang_thai=? WHERE id=?');
        $stmt->execute([$status, $targetId]);
    } elseif ($action === 'reset_password') {
        $password = $_POST['new_password'] ?? '';
        if (!is_string($password) || strlen($password)<PASSWORD_MIN_LENGTH || strlen($password)>72 || $password !== ($_POST['confirm_password'] ?? null)) {
            throw new DomainException('Mật khẩu mới phải có 8–72 byte và khớp xác nhận.');
        }
        $stmt = $db->prepare('UPDATE nguoi_dung SET mat_khau=?, reset_token=NULL, reset_token_expiry=NULL WHERE id=?');
        $stmt->execute([hashPassword($password), $targetId]);
    } else { throw new DomainException('Thao tác không hợp lệ.'); }
    invalidateAccountSessions($targetId);
    auditSecurity('account.' . $action, (int)$targetId);
    $db->commit();
    setFlashMessage('success', 'Đã cập nhật tài khoản. Phiên dùng thông tin cũ sẽ không còn hợp lệ.');
} catch (Throwable $error) {
    if ($db->inTransaction()) { $db->rollBack(); }
    setFlashMessage('error', $error instanceof DomainException ? $error->getMessage() : safeError($error));
}
redirect('lanh_dao/quan_ly_nguoi_dung.php');
