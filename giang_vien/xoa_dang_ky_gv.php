<?php
require_once __DIR__ . '/../bootstrap.php';
requireLogin();
requirePost();
$id = (int)($_POST['id'] ?? 0);
requireRegistrationOwner($id);
$result = (new DangKyDeTaiModel())->xoaDangKyAnToan($id, false, true);
setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
$type = ($_POST['loai'] ?? '') === 'chuyen_nganh' ? 'chuyen_nganh' : 'co_so_nganh';
redirect('giang_vien/duyet_sinh_vien.php?loai=' . $type);