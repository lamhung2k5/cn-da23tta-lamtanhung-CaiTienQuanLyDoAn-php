<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_follow_test');
require dirname(__DIR__, 2) . '/bootstrap.php';
$model = new DeTaiModel();
$db = Database::getInstance()->getConnection();
$checks = 0;
function checkTechnology($value, $label) {
    global $checks;
    if (!$value) { throw new RuntimeException($label); }
    ++$checks;
    echo "PASS: $label\n";
}
$tokens = DeTaiModel::technologyTokens(' PHP, php , MySQL, , C++, .NET, Node.js ');
checkTechnology(count($tokens) === 5, 'Case duplicates, whitespace and empty tokens');
checkTechnology(isset($tokens['c++'], $tokens['.net'], $tokens['node.js']), 'Punctuation retained');
checkTechnology(DeTaiModel::technologyTokens(null) === [], 'Missing technology');
$db->beginTransaction();
try {
    foreach (['co_so_nganh', 'chuyen_nganh'] as $type) {
        $filters = ['he_dao_tao'=>$type];
        $base = $model->getDeTaiDaDuyet($filters);
        checkTechnology(count($base) >= 2, "$type isolated fixtures available");
        // Changes exist only inside this transaction and are rolled back below.
        $stmt = $db->prepare('UPDATE de_tai SET cong_nghe=? WHERE id=?');
        foreach ($base as $topic) { $stmt->execute([null, $topic['id']]); }
        $stmt->execute([' Java , php, PHP, C++, .NET, Node.js ', $base[0]['id']]);
        $stmt->execute(['JavaScript, Python', $base[1]['id']]);
        foreach (['Java', ' PHP ', 'C++', '.NET', 'Node.js'] as $technology) {
            $found = $model->getDeTaiDaDuyet($filters + ['technology'=>$technology]);
            checkTechnology(array_column($found, 'id') === [$base[0]['id']], "$type exact technology $technology");
        }
        $found = $model->getDeTaiDaDuyet($filters + ['technology'=>'JavaScript']);
        checkTechnology(array_column($found, 'id') === [$base[1]['id']], "$type JavaScript distinct from Java");
        checkTechnology($model->getDeTaiDaDuyet($filters + ['technology'=>'%']) === [], "$type no wildcard matching");
        $map = (new DeTaiQuanTamModel())->savedMap(1);
        $combined = $model->getDeTaiDaDuyet($filters + ['technology'=>'php', 'giang_vien_id'=>$base[0]['giang_vien_id'], 'search'=>$base[0]['tieu_de'], 'title_teacher_only'=>true, 'student_id'=>1, 'followed'=>isset($map[$base[0]['id']]) ? 'saved' : 'unsaved', 'sort'=>'title']);
        checkTechnology(array_column($combined, 'id') === [$base[0]['id']], "$type combined filters");
    }
} finally {
    $db->rollBack();
}
echo "$checks checks passed; fixture changes rolled back.\n";
