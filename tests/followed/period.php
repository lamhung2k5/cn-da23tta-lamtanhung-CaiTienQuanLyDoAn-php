<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
putenv('LOCAL_DB_NAME=qldt_follow_test');
require dirname(__DIR__,2).'/bootstrap.php';
$model=new CaiDatModel();
foreach (['cho_phep_dang_ky'=>'1','dang_ky_csn_bat_dau'=>'2030-01-01 07:00:00','dang_ky_csn_ket_thuc'=>'2030-01-01 08:00:00'] as $key=>$value) { $model->setValue($key,$value); }
$window=new DangKyThoiGian();
$start=strtotime('2030-01-01 07:00:00');$end=strtotime('2030-01-01 08:00:00');
$results=[];
foreach ([['Just before opening',$start-1,false],['Exact opening time',$start,true],['Exact closing time',$end,true],['Just after closing',$end+1,false]] as $case) {
    $results[]=['Check'=>$case[0],'Passed'=>$window->status('co_so_nganh',$case[1])['can_register']===$case[2],'Detail'=>'Clock injected directly in policy test, never from HTTP parameters'];
}
$model->setValue('dang_ky_csn_bat_dau','invalid');
$results[]=['Check'=>'Invalid stored schedule fails closed','Passed'=>!(new DangKyThoiGian())->status('co_so_nganh',$start)['can_register'],'Detail'=>'Invalid date does not allow registration'];
foreach (['cho_phep_dang_ky'=>'1','gioi_han_de_tai_quan_tam'=>'6','dang_ky_csn_bat_dau'=>'2000-01-01 00:00:00','dang_ky_csn_ket_thuc'=>'2100-01-01 00:00:00','dang_ky_cn_bat_dau'=>'2000-01-01 00:00:00','dang_ky_cn_ket_thuc'=>'2100-01-01 00:00:00'] as $key=>$value) { $model->setValue($key,$value); }
file_put_contents(dirname(__DIR__,2).'/storage/baseline/followed-period-results.json',json_encode($results,JSON_PRETTY_PRINT));
echo json_encode($results,JSON_PRETTY_PRINT).PHP_EOL;
