<?php
require_once __DIR__ . '/../bootstrap.php';
requireLogin();
requirePost();
$id = (int)($_POST['id'] ?? 0);
$registration = requireRegistrationOwner($id);
// Derive the topic from the registration, not from a request-supplied topic ID.
$result = (new DangKyDeTaiModel())->xoaDangKyAnToan($id);
setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
redirect('giang_vien/chi_tiet_de_tai.php?id=' . (int)$registration['de_tai_id']);