<?php
/**
 * ĐĂNG NHẬP GIẢNG VIÊN
 * Chuyển hướng đến trang đăng nhập thống nhất với lọc vai trò
 */

require_once '../bootstrap.php';

// Chuyển hướng đến trang đăng nhập thống nhất với role filter
redirect('login.php?role=' . ROLE_GIANG_VIEN);