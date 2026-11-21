<?php
if (getenv('FOLLOWED_TEST_MODE') !== '1' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true)) { http_response_code(404); exit; }
require dirname(__DIR__,2).'/config/database.php';
if (DB_NAME !== 'qldt_follow_test') { http_response_code(409); exit; }
header('Content-Type: text/plain');
echo DB_NAME;
