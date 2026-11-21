<?php
require dirname(__DIR__, 2) . '/bootstrap.php';
if (getenv('DIRECTORY_TEST_MODE') !== '1' || DB_NAME !== 'qldt_directory_test' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true)) { http_response_code(404); exit; }
echo DB_NAME;
