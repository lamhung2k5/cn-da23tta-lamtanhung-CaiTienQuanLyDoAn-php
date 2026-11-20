<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$dir=dirname(__DIR__,2).'/storage/baseline';
function cell($column,$row,$value) {
    return '<c r="'.$column.$row.'" t="inlineStr"><is><t>'.htmlspecialchars($value,ENT_XML1).'</t></is></c>';
}
$xml='<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
$xml.='<row r="1">';
foreach(['email','ho_ten','mat_khau','ma_sinh_vien'] as $i=>$value){$xml.=cell(chr(65+$i),1,$value);}
$xml.='</row><row r="2">';
foreach(['xlsx-demo@example.test','XLSX demo','XlsxDemo!2026','DEMO_XLSX'] as $i=>$value){$xml.=cell(chr(65+$i),2,$value);}
$xml.='</row></sheetData></worksheet>';
foreach(['valid'=>$xml,'entity'=>'<?xml version="1.0"?><!DOCTYPE worksheet [<!ENTITY test SYSTEM "file:///not-read">]><worksheet/>'] as $name=>$source){
    $zip=new ZipArchive();
    $zip->open($dir.'/accounts-'.$name.'.xlsx',ZipArchive::CREATE|ZipArchive::OVERWRITE);
    $zip->addFromString('xl/worksheets/sheet1.xml',$source);
    $zip->close();
}
echo "XLSX fixtures generated.\n";
