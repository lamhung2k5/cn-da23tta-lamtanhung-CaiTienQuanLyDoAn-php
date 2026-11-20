<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_LANH_DAO);
$importRole = ROLE_SINH_VIEN;
require ROOT_PATH . 'includes/account_import.php';
