<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_directory_test');
$root = dirname(__DIR__, 2);
$db = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if ($db->query("SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='qldt_directory_test'")->fetchColumn()) { throw new RuntimeException('Test DB already exists; refusing overwrite.'); }
$db->exec('CREATE DATABASE qldt_directory_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db->exec('USE qldt_directory_test');
foreach (['schema.sql','seed.sql'] as $file) { $db->exec(file_get_contents($root . '/database/' . $file)); }
require $root . '/bootstrap.php';
$model = new GiangVienModel();
$checks = 0;
function directoryCheck($condition, $label) {
    global $checks;
    if (!$condition) { throw new RuntimeException($label); }
    ++$checks; echo "PASS: $label\n";
}
directoryCheck(count($model->getDirectory(false)) === 2 && $model->getDirectory() === [], 'Profiles hidden by default');
$input = ['chuc_vu_cong_khai'=>'Trưởng khoa', 'so_noi_bo'=>'123', 'hien_thi_cong_khai'=>'1', 'hoc_ham_hoc_vi'=>'TS.', 'khoa'=>'Demo khoa', 'chuyen_mon'=>'Web', 'so_dien_thoai'=>'0123456789'];
$model->updateDirectoryProfile(1, $input + ['vai_tro_id'=>3, 'la_lanh_dao'=>1]);
directoryCheck(count($model->getDirectory()) === 1 && $model->getPublicProfile(1), 'Publish selected profile');
directoryCheck(!$model->getPublicProfile(2), 'Hidden profile absent from direct lookup');
$row = $model->queryOne('SELECT nd.vai_tro_id,gv.la_lanh_dao FROM nguoi_dung nd JOIN giang_vien gv ON gv.nguoi_dung_id=nd.id WHERE gv.id=1');
directoryCheck((int)$row['vai_tro_id'] === 2 && (int)$row['la_lanh_dao'] === 0, 'Public position never changes privileges');
$db->exec("UPDATE nguoi_dung SET trang_thai='inactive' WHERE id=3");
directoryCheck(!$model->getPublicProfile(1), 'Locked account hidden');
$db->exec("UPDATE nguoi_dung SET trang_thai='active' WHERE id=3");
$db->exec('UPDATE nguoi_dung SET vai_tro_id=1 WHERE id=3');
directoryCheck(!$model->getPublicProfile(1), 'Non-lecturer role excluded even with leftover profile');
$db->exec('UPDATE nguoi_dung SET vai_tro_id=2 WHERE id=3');
foreach ([['chuc_vu_cong_khai'=>'Admin'], ['so_noi_bo'=>str_repeat('x',31)], ['hien_thi_cong_khai'=>['1']]] as $invalid) {
    $rejected = false;
    try { $model->updateDirectoryProfile(1, array_replace($input, $invalid)); } catch (DomainException $error) { $rejected = true; }
    directoryCheck($rejected, 'Invalid profile rejected');
}
$topics = $model->getPublicTopics(1);
directoryCheck(count($topics) === 3 && count($model->getPublicTopics(1, 'chuyen_nganh')) === 1, 'Only approved topics and correct type counts');
$db->exec(file_get_contents($root . '/database/migrations/003_giang_vien_cong_khai.sql'));
directoryCheck((bool)$model->getPublicProfile(1), 'Migration rerun preserves published profile');
$db->exec("UPDATE nguoi_dung SET ho_ten='Faculty <script>alert(1)</script>' WHERE id=3");
$db->exec("UPDATE de_tai SET tieu_de='PRIVATE_DRAFT_SENTINEL' WHERE id=4");
echo "$checks model checks passed; synthetic database prepared.\n";
