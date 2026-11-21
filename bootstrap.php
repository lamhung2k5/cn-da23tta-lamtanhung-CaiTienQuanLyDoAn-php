<?php
/**
 * BOOTSTRAP FILE
 * Load tất cả các file cần thiết
 */

// Load config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Load helpers
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/security.php';
require_once __DIR__ . '/helpers/ThongBaoPolicy.php';
require_once __DIR__ . '/helpers/email.php';
require_once __DIR__ . '/helpers/phpmailer.php';

// Kiểm tra và áp dụng HTTPS nếu cần
enforceHttps();

// Load models
require_once __DIR__ . '/models/BaseModel.php';
require_once __DIR__ . '/models/NguoiDungModel.php';
require_once __DIR__ . '/models/VaiTroModel.php';
require_once __DIR__ . '/models/GiangVienModel.php';
require_once __DIR__ . '/models/SinhVienModel.php';
require_once __DIR__ . '/models/LanhDaoModel.php';
require_once __DIR__ . '/models/DeTaiModel.php';
require_once __DIR__ . '/models/DangKyDeTaiModel.php';
require_once __DIR__ . '/models/ThongBaoModel.php';
require_once __DIR__ . '/models/ThongBaoDoAnModel.php';
require_once __DIR__ . '/models/CaiDatModel.php';
require_once __DIR__ . '/models/DeTaiQuanTamModel.php';
require_once __DIR__ . '/helpers/DangKyThoiGian.php';
require_once __DIR__ . '/helpers/DeTaiQuanTamService.php';

initializeSecurity();
