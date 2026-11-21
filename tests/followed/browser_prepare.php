<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_follow_test');
require dirname(__DIR__,2).'/bootstrap.php';
if (DB_NAME !== 'qldt_follow_test') { exit(1); }
Database::getInstance()->getConnection()->exec('DELETE FROM de_tai_quan_tam WHERE sinh_vien_id=1');
echo "Prepared a clean saved list for browser checks only.\n";
