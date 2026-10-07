<?php
echo "<pre>";
echo "Host (gethostname): " . gethostname() . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "\n--- PDO Extensions ---\n";
echo "pdo_mysql loaded: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n";
echo "pdo loaded: " . (extension_loaded('pdo') ? 'YES' : 'NO') . "\n";
echo "\n--- InfinityFree Detection ---\n";
$host = $_SERVER['HTTP_HOST'] ?? '';
$dir  = __DIR__;
$isIF = strpos($dir, 'infinityfree.com') !== false
     || strpos($host, 'infinityfreeapp.com') !== false
     || strpos($host, 'infinityfree.net') !== false;
echo "isInfinityFree: " . ($isIF ? 'TRUE ✓' : 'FALSE ✗') . "\n";
echo "\n--- MySQL Connection Test ---\n";
$dbHost = 'sql301.infinityfree.com';
$dbPort = '3306';
$dbName = 'if0_42542434_qldt_database';
$dbUser = 'if0_42542434';
$dbPass = '12345lamnhathao';
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Kết nối MySQL: THÀNH CÔNG ✓\n";
} catch (PDOException $e) {
    echo "Kết nối MySQL: THẤT BẠI ✗\n";
    echo "Lỗi: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>
