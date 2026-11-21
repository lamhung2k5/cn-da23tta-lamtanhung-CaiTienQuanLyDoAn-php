<?php
// Read-only checks against the isolated feature test database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_follow_test');
require dirname(__DIR__, 2) . '/bootstrap.php';
$model = new DeTaiModel();
$checks = 0;
function checkSearch($condition, $label) {
    global $checks;
    if (!$condition) { throw new RuntimeException($label); }
    ++$checks;
    echo "PASS: $label\n";
}
foreach (['co_so_nganh', 'chuyen_nganh'] as $type) {
    $filters = ['he_dao_tao'=>$type];
    $base = $model->getDeTaiDaDuyet($filters);
    checkSearch(count($base) > 0, "$type fixture has available topics");
    $teacher = (int)$base[0]['giang_vien_id'];
    $actual = $model->getDeTaiDaDuyet($filters + ['giang_vien_id'=>$teacher]);
    $expected = array_values(array_filter($base, static function ($row) use ($teacher) { return (int)$row['giang_vien_id'] === $teacher; }));
    checkSearch(array_column($actual, 'id') === array_column($expected, 'id'), "$type teacher filter");
    $saved = $model->getDeTaiDaDuyet($filters + ['student_id'=>1, 'followed'=>'saved']);
    $unsaved = $model->getDeTaiDaDuyet($filters + ['student_id'=>1, 'followed'=>'unsaved']);
    $savedIds = array_column($saved, 'id'); $unsavedIds = array_column($unsaved, 'id');
    checkSearch(!array_intersect($savedIds, $unsavedIds) && count($saved)+count($unsaved) === count($base), "$type saved and unsaved partition");
    $map = (new DeTaiQuanTamModel())->savedMap(1);
    checkSearch(count(array_filter($saved, static function ($row) use ($map) { return !isset($map[$row['id']]); })) === 0
        && count(array_filter($unsaved, static function ($row) use ($map) { return isset($map[$row['id']]); })) === 0, "$type own saved records only");
    $oldest = $model->getDeTaiDaDuyet($filters + ['sort'=>'oldest']);
    checkSearch(array_column($oldest, 'id') === array_reverse(array_column($base, 'id')), "$type chronological ordering");
    $title = $model->getDeTaiDaDuyet($filters + ['sort'=>'title']);
    checkSearch(count($title) === count($base), "$type title ordering executes");
    $none = $model->getDeTaiDaDuyet($filters + ['search'=>"' OR 1=1 -- unmatched-search-92745", 'title_teacher_only'=>true]);
    checkSearch(count($none) === 0, "$type search bound as data");
    $combined = $model->getDeTaiDaDuyet($filters + ['search'=>$base[0]['tieu_de'], 'title_teacher_only'=>true, 'giang_vien_id'=>$teacher, 'student_id'=>1, 'followed'=>isset($map[$base[0]['id']]) ? 'saved' : 'unsaved', 'sort'=>'title']);
    checkSearch(in_array($base[0]['id'], array_column($combined, 'id')), "$type combined filters");
}
echo "$checks checks passed.\n";
