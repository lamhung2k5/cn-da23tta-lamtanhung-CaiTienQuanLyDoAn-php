<?php
/**
 * ĐĂNG XUẤT
 */

require_once '../bootstrap.php';



// Xóa toàn bộ session và cookie an toàn
destroySecureSession();

// Chuyển về trang chủ
redirect('index.php');