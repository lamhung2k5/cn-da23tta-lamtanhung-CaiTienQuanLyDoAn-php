<?php
// Preview by default. Never creates accounts or publishes profiles.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/bootstrap.php';
$apply = in_array('--apply', $argv, true);
$db = Database::getInstance()->getConnection();
$legacy = require __DIR__ . '/../config/faculty_members.php';
$profiles = (new GiangVienModel())->getDirectory(false);
$matched = 0; $changed = 0;
$db->beginTransaction();
try {
    foreach ($profiles as $profile) {
        foreach ($legacy as $old) {
            if (strcasecmp($profile['email'], $old['email']) !== 0) { continue; }
            ++$matched;
            $fields = ['so_noi_bo'=>'room','so_dien_thoai'=>'phone','khoa'=>'khoa','chuyen_mon'=>'chuyen_mon'];
            foreach ($fields as $column=>$key) {
                if (trim((string)($profile[$column] ?? '')) !== '' || trim((string)($old[$key] ?? '')) === '') { continue; }
                ++$changed;
                if ($apply) {
                    $stmt = $db->prepare("UPDATE giang_vien SET `$column`=? WHERE id=? AND (`$column` IS NULL OR TRIM(`$column`)='')");
                    $stmt->execute([trim($old[$key]), $profile['id']]);
                }
            }
            // Position stays under leadership review, including default values.
            break;
        }
    }
    if ($apply) { $db->commit(); } else { $db->rollBack(); }
} catch (Throwable $error) {
    if ($db->inTransaction()) { $db->rollBack(); }
    throw $error;
}
echo ($apply ? 'Applied' : 'Preview') . ' on ' . DB_NAME . ": $matched matched profiles, $changed empty fields eligible. No accounts created; visibility and positions unchanged.\n";
