<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_LANH_DAO);
requirePost();
$db = Database::getInstance()->getConnection();
try {
    $limit = filter_var($_POST['gioi_han_de_tai_quan_tam'] ?? null, FILTER_VALIDATE_INT);
    if ($limit === false || $limit < 1 || $limit > 50) { throw new DomainException('Giới hạn quan tâm phải là số nguyên từ 1 đến 50.'); }
    $enabled = $_POST['cho_phep_dang_ky'] ?? '';
    if (!in_array($enabled, ['0','1'], true)) { throw new DomainException('Trạng thái đăng ký không hợp lệ.'); }
    $values = ['gioi_han_de_tai_quan_tam'=>(string)$limit,'cho_phep_dang_ky'=>$enabled];
    foreach (['csn','cn'] as $type) {
        foreach (['bat_dau','ket_thuc'] as $field) {
            $key = 'dang_ky_' . $type . '_' . $field;
            $input = $_POST[$key] ?? null;
            $date = false;
            if (is_string($input)) {
                foreach (['!Y-m-d\TH:i:s','!Y-m-d\TH:i'] as $format) {
                    $candidate = DateTimeImmutable::createFromFormat($format, $input);
                    if ($candidate && $candidate->format(substr($format,1)) === $input) { $date = $candidate; break; }
                }
            }
            if (!$date) { throw new DomainException('Ngày giờ mở/đóng không hợp lệ.'); }
            $values[$key] = $date->format('Y-m-d H:i:s');
        }
        if ($values['dang_ky_' . $type . '_bat_dau'] >= $values['dang_ky_' . $type . '_ket_thuc']) {
            throw new DomainException('Thời gian đóng đăng ký phải sau thời gian mở đăng ký.');
        }
    }
    $db->beginTransaction();
    $settings = new CaiDatModel();
    // The limit is written first, matching the add-to-followed transaction's lock order.
    foreach ($values as $key=>$value) { $settings->setValue($key, $value); }
    auditSecurity('settings.registration_and_followed');
    $db->commit();
    setFlashMessage('success', 'Đã lưu giới hạn quan tâm và thời gian đăng ký.');
} catch (Throwable $error) {
    if ($db->inTransaction()) { $db->rollBack(); }
    setFlashMessage('error', $error instanceof DomainException ? $error->getMessage() : safeError($error));
}
redirect('lanh_dao/cai_dat_thong_so.php#tab-dang-ky');
