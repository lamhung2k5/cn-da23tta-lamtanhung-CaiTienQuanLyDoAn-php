<?php
/**
 * Nút quay lại trang lãnh đạo (chỉ hiển thị cho lãnh đạo)
 */
if (isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === ROLE_LANH_DAO): ?>
    <a class="nav-link btn btn-primary text-white mb-3" href="../lanh_dao/dashboard.php">
        <i class="bi bi-arrow-left-circle"></i> Quay lại trang lãnh đạo
    </a>
    <hr class="my-2">
<?php endif; ?>
