<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_security_test');
require dirname(__DIR__,2) . '/bootstrap.php';
if (PHP_SAPI !== 'cli' || DB_NAME !== 'qldt_security_test') { exit(1); }
$results = [];
$record = function ($name, $passed) use (&$results) {
    $results[] = ['Check' => $name, 'Passed' => (bool)$passed, 'Detail' => 'CLI on synthetic test database'];
};
$users = new NguoiDungModel();
$record('Leadership-issued reset password authenticates', $users->login('sv1@example.test', 'ResetDemo!2026')['success']);
$record('Old password no longer authenticates after reset', !$users->login('sv1@example.test', 'BaselineDemo!2026')['success']);
$record('Plaintext password fallback is disabled', !verifyPassword('DemoPassword!', 'DemoPassword!'));
foreach (['column', 'order', 'table'] as $case) {
    $rejected = false;
    try {
        if ($case === 'column') { $users->findBy(['email OR 1=1' => 'x']); }
        if ($case === 'order') { $users->findAll('id DESC; SELECT 1'); }
        if ($case === 'table') { $users->findById(1, 'nguoi_dung; SELECT 1'); }
    } catch (InvalidArgumentException $expected) { $rejected = true; }
    $record('Dynamic SQL rejects invalid ' . $case, $rejected);
}
$record('CSV formula output is neutralized', safeCsvRow(['=1+1'])[0] === "'=1+1");
$record('Unsafe URL scheme is neutralized', safeHref('javascript:alert(1)') === '#');
$singleton = Database::getInstance();
$connection = $singleton->getConnection();
$oldId = (int)$connection->query('SELECT CONNECTION_ID()')->fetchColumn();
$killer = new PDO('mysql:host=127.0.0.1;port=3306;dbname=qldt_security_test', 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$killer->exec('KILL CONNECTION ' . $oldId);
$newId = (int)$singleton->getConnection()->query('SELECT CONNECTION_ID()')->fetchColumn();
$record('Database singleton reconnects after lost connection', $oldId !== $newId);
file_put_contents(dirname(__DIR__,2) . '/storage/baseline/security-model-results.json', json_encode($results, JSON_PRETTY_PRINT));
echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
exit(count(array_filter($results, function ($row) { return !$row['Passed']; })) ? 1 : 0);
