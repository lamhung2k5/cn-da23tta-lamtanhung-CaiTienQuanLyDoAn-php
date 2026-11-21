<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_follow_test');
require dirname(__DIR__,2).'/bootstrap.php';
$_SESSION=['user_id'=>(int)$argv[1],'vai_tro'=>ROLE_SINH_VIEN];
while (microtime(true)<(float)$argv[3]) { usleep(10000); }
try {
    $message=(new DeTaiQuanTamModel())->addForCurrentStudent((int)$argv[1],(int)$argv[2]);
    echo json_encode(['success'=>true,'message'=>$message],JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) { echo json_encode(['success'=>false,'message'=>$error->getMessage()],JSON_UNESCAPED_UNICODE); }
