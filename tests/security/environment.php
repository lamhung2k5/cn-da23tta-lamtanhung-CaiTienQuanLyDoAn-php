<?php
// Read-only identity check for the loopback-only integration server.
if (getenv('SECURITY_TEST_MODE') !== '1' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true)) {
    http_response_code(404); exit;
}
require dirname(__DIR__,2) . '/config/database.php';
if (DB_NAME !== 'qldt_security_test') { http_response_code(409); exit; }
header('Content-Type: text/plain');
echo DB_NAME;
