<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_security_test');
require dirname(__DIR__,2).'/bootstrap.php';
$db=Database::getInstance()->getConnection();
if ($db->query('SELECT DATABASE()')->fetchColumn()!=='qldt_security_test') { throw new RuntimeException('Wrong database'); }
function newUser($role,$tag) {
    global $db;
    $stmt=$db->prepare('INSERT INTO nguoi_dung(email,mat_khau,ho_ten,vai_tro_id) SELECT ?,?,?,id FROM vai_tro WHERE ma_vai_tro=?');
    $stmt->execute([$tag.'@example.test',password_hash('RaceDemo!2026',PASSWORD_BCRYPT),$tag,$role]);
    $uid=$db->lastInsertId();
    $table=$role==='sinh_vien'?'sinh_vien':'giang_vien';
    $column=$role==='sinh_vien'?'ma_sinh_vien':'ma_giang_vien';
    $stmt=$db->prepare("INSERT INTO $table(nguoi_dung_id,$column) VALUES(?,?)");
    $stmt->execute([$uid,$tag]);
    return ['user'=>(int)$uid,'profile'=>(int)$db->lastInsertId()];
}
function newTopic($lecturer,$tag,$capacity) {
    global $db;
    $stmt=$db->prepare("INSERT INTO de_tai(ten_de_tai,tieu_de,mo_ta,giang_vien_id,he_dao_tao,so_luong_sv,trang_thai) VALUES(?,?,?,?,'co_so_nganh',?,'da_duyet')");
    $stmt->execute([$tag,$tag,'Synthetic concurrency test',$lecturer['profile'],$capacity]);
    return (int)$db->lastInsertId();
}
$a=newUser('giang_vien','race-lecturer-a');
$b=newUser('giang_vien','race-lecturer-b');
$c=newUser('giang_vien','race-lecturer-c');
$d=newUser('giang_vien','race-lecturer-d');
$db->prepare('UPDATE giang_vien SET gioi_han_sv_csn=1 WHERE id=?')->execute([$b['profile']]);
$topics=[newTopic($a,'race-single-slot',1),newTopic($b,'race-quota-b1',5),newTopic($b,'race-quota-b2',5),newTopic($c,'race-student-c',5),newTopic($d,'race-student-d',5)];
$jobs=[];
for($i=0;$i<10;$i++) { $student=newUser('sinh_vien','race-student-'.$i);$jobs[]=$student+['topic'=>$topics[0],'group'=>'slot']; }
for($i=0;$i<2;$i++) { $student=newUser('sinh_vien','race-quota-student-'.$i);$jobs[]=$student+['topic'=>$topics[$i+1],'group'=>'lecturer']; }
$student=newUser('sinh_vien','race-shared-student');
$jobs[]=$student+['topic'=>$topics[3],'group'=>'student'];
$jobs[]=$student+['topic'=>$topics[4],'group'=>'student'];
echo json_encode(['jobs'=>$jobs,'topics'=>$topics]);
