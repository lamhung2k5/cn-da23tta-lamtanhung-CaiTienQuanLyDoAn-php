<?php
// One-time preparation of an isolated, synthetic baseline database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__, 2);
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$name = 'qldt_security_test';
$exists = $pdo->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '$name'")->fetchColumn();
if ($exists) { throw new RuntimeException('Test database already exists; refusing to overwrite.'); }
$pdo->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `$name`");
foreach (['schema.sql', 'seed.sql'] as $file) {
    $pdo->exec(file_get_contents($root . '/database/' . $file));
}
echo 'Imported schema and synthetic seed into ' . $name . PHP_EOL;
echo 'Tables: ' . $pdo->query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->fetchColumn() . PHP_EOL;