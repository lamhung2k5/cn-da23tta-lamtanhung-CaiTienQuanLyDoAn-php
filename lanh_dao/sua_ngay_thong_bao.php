<?php
require_once __DIR__ . '/../bootstrap.php';
requireRole(ROLE_LANH_DAO);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { securityDeny(405, 'Use the announcement management screen.'); }
redirect('lanh_dao/quan_ly_thong_bao.php');
