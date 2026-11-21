<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_announcement_test');
$root = dirname(__DIR__, 2);
$db = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if ($db->query("SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='qldt_announcement_test'")->fetchColumn()) { throw new RuntimeException('Test DB exists; refusing overwrite.'); }
$db->exec('CREATE DATABASE qldt_announcement_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db->exec('USE qldt_announcement_test');
foreach (['schema.sql','seed.sql'] as $file) { $db->exec(file_get_contents($root . '/database/' . $file)); }
require $root . '/bootstrap.php';
$m = new ThongBaoModel(); $thesis = new ThongBaoDoAnModel(); $checks = 0;
function noticeCheck($condition, $label) { global $checks; if (!$condition) { throw new RuntimeException($label); } ++$checks; echo "PASS: $label\n"; }
$base = ['tieu_de'=>'PUBLIC_ACTIVE', 'noi_dung'=>"Full announcement <script>alert(1)</script>\nSecond line", 'loai'=>'info', 'trang_thai'=>'mo'];
$m->savePublicAnnouncement(1, $base);
$ids = ['active'=>1];
foreach (['scheduled'=>['ngay_bat_dau'=>'2099-01-01'], 'expired'=>['ngay_ket_thuc'=>'2000-01-01'], 'hidden'=>['trang_thai'=>'khoa'], 'today'=>['ngay_bat_dau'=>date('Y-m-d'),'ngay_ket_thuc'=>date('Y-m-d')]] as $state=>$override) {
    $ids[$state] = (int)$m->savePublicAnnouncement(null, array_replace($base, ['tieu_de'=>'PUBLIC_' . strtoupper($state)], $override));
}
$ids['private'] = (int)$m->taoThongBao(1, 'PRIVATE_SENTINEL', 'Private personal content');
foreach (['scheduled','expired','hidden','private'] as $key) { noticeCheck(!$m->getPublicAnnouncement($ids[$key]), "$key not publicly readable"); }
noticeCheck((bool)$m->getPublicAnnouncement($ids['today']), 'End date inclusive');
noticeCheck((bool)$m->getPublicAnnouncement($ids['hidden'], true) && !$m->getPublicAnnouncement($ids['private'], true), 'Preview scope only public announcements');
foreach (['hidden','scheduled','expired','active'] as $state) { noticeCheck(count($m->listPublicForManagement('', $state)) > 0, "$state management filter"); }
noticeCheck(count($m->listPublicForManagement('PUBLIC_ACTIVE')) === 1, 'Search management content');
foreach ([['ngay_bat_dau'=>'2026-02-30'], ['ngay_bat_dau'=>'2027-01-02','ngay_ket_thuc'=>'2027-01-01'], ['loai'=>'invalid'], ['tieu_de'=>['bad']], ['trang_thai'=>'invalid']] as $invalid) {
    $rejected = false; try { $m->savePublicAnnouncement(null, array_replace($base, $invalid)); } catch (DomainException $e) { $rejected = true; }
    noticeCheck($rejected, 'Invalid announcement rejected');
}
foreach (['delete','toggle_status','update'] as $action) {
    $rejected = false;
    try { if ($action === 'update') { $m->savePublicAnnouncement($ids['private'], $base); } else { $m->changePublicAnnouncement($ids['private'], $action); } } catch (DomainException $e) { $rejected = true; }
    noticeCheck($rejected && $m->findById($ids['private']), "Cannot $action personal notification via public management");
}
$timeline = ['loai'=>'co_so_nganh','thu_tu'=>'1','tieu_de'=>'THESIS_MILESTONE','noi_dung'=>'Submit report','ngay'=>'28','thang'=>'2','nam'=>'2028','yeu_cau'=>'Document|PDF'];
$bad = false; try { $thesis->addTimelineItem(array_replace($timeline,['nam'=>'2027','ngay'=>'29'])); } catch (DomainException $e) { $bad = true; }
noticeCheck($bad, 'Invalid calendar milestone rejected');
$thesis->addTimelineItem($timeline);
noticeCheck(count($thesis->getTimelineItems('co_so_nganh')) === 1, 'Valid milestone saved');
$thesis->updatePageContent(1, ['page_title'=>'THESIS_ACTIVE','subtitle'=>'Thesis notice','date_badge'=>'2026-2027','trang_thai'=>'mo']);
$thesis->updateNotice(1, ['tieu_de'=>'NOTICE_OLD','noi_dung'=>'Old content','trang_thai'=>'mo']);
$thesis->updateNotice(null, ['tieu_de'=>'NOTICE_LATEST','noi_dung'=>'Latest content','trang_thai'=>'khoa']);
noticeCheck($thesis->getNotice() === null, 'Hidden latest notice cannot resurrect older notice');
$notice = $thesis->getNoticeForAdmin(); $ids['notice'] = (int)$notice['id'];
$thesis->updateNotice($notice['id'], ['tieu_de'=>'NOTICE_LATEST','noi_dung'=>'Latest content','trang_thai'=>'mo']);
for ($i=0;$i<21;$i++) { $m->savePublicAnnouncement(null, array_replace($base, ['tieu_de'=>'BULK_HIDDEN_' . $i, 'trang_thai'=>'khoa'])); }
file_put_contents($root . '/storage/baseline/announcement-fixture.json', json_encode($ids));
echo "$checks model checks passed.\n";
