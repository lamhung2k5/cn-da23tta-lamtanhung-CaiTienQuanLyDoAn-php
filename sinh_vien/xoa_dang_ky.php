<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_SINH_VIEN);
requirePost();
$id = filter_var($_POST['dang_ky_id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) { jsonError('ID đăng ký không hợp lệ.', 400); }
$result = (new DangKyDeTaiModel())->xoaDangKyAnToan($id, true);
jsonResponse($result, $result['success'] ? 200 : 403);