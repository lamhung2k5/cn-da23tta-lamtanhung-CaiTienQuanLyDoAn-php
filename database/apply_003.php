<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/bootstrap.php';
$db = Database::getInstance()->getConnection();
$db->exec(file_get_contents(__DIR__ . '/migrations/003_giang_vien_cong_khai.sql'));
echo 'Migration 003 applied to ' . DB_NAME . PHP_EOL;
