<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__,2);
$db = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if ($db->query("SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='qldt_follow_test'")->fetchColumn()) { throw new RuntimeException('Followed test database already exists; refusing overwrite.'); }
$db->exec('CREATE DATABASE qldt_follow_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db->exec('USE qldt_follow_test');
foreach (['schema.sql','seed.sql'] as $file) { $db->exec(file_get_contents($root.'/database/'.$file)); }
for ($i=7;$i<=16;$i++) {
    $type = $i % 2 ? 'co_so_nganh' : 'chuyen_nganh';
    $stmt=$db->prepare("INSERT INTO de_tai (id,ma_de_tai,ten_de_tai,tieu_de,mo_ta,giang_vien_id,he_dao_tao,so_luong_sv,trang_thai) VALUES (?,?,?,?,?,1,?,1,'da_duyet')");
    $stmt->execute([$i,'FOLLOW_'.$i,'Đề tài quan tâm mẫu '.$i,'Đề tài quan tâm mẫu '.$i,'Dữ liệu giả lập',$type]);
}
echo "Prepared qldt_follow_test with synthetic topics.\n";
