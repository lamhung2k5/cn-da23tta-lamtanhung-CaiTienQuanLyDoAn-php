<?php
// Only school-issued accounts may sign in through auth/login.php.
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo 'Chức năng này không được sử dụng. Vui lòng liên hệ Lãnh đạo.';
exit;
