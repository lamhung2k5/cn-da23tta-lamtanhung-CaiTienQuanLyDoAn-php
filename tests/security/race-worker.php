<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_security_test');
require dirname(__DIR__,2).'/bootstrap.php';
$_SESSION=['user_id'=>(int)$argv[1],'vai_tro'=>ROLE_SINH_VIEN];
while(microtime(true)<(float)$argv[4]) { usleep(10000); }
$result=(new DangKyDeTaiModel())->dangKyDeTai((int)$argv[2],(int)$argv[3]);
echo json_encode($result,JSON_UNESCAPED_UNICODE);
