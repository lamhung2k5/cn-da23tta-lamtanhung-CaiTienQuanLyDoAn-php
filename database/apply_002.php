<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/bootstrap.php';
$db = Database::getInstance()->getConnection();
$db->exec(file_get_contents(__DIR__ . '/migrations/002_de_tai_quan_tam.sql'));
echo 'Migration 002 applied to ' . DB_NAME . PHP_EOL;
