$ErrorActionPreference='Stop'
function SqlFollow($query){$v=& E:/xampp/mysql/bin/mysql.exe -h 127.0.0.1 -u root --batch --skip-column-names qldt_follow_test -e $query;if($LASTEXITCODE -ne 0){throw 'Followed race SQL failed'};return ($v -join "`n")}
$null=SqlFollow "DELETE FROM de_tai_quan_tam WHERE sinh_vien_id=1; DELETE FROM dang_ky_de_tai WHERE sinh_vien_id IN (1,2); UPDATE de_tai dt SET so_luong_da_dang_ky=(SELECT COUNT(*) FROM dang_ky_de_tai dk WHERE dk.de_tai_id=dt.id AND dk.trang_thai='da_duyet'); UPDATE cai_dat SET value='3' WHERE key_name='gioi_han_de_tai_quan_tam';"
function Workers($student,$topics,$prefix){
 $start=([DateTimeOffset]::UtcNow.ToUnixTimeMilliseconds()/1000.0+3).ToString([Globalization.CultureInfo]::InvariantCulture)
 $workers=@();$i=0
 foreach($topic in $topics){
  $out=Join-Path (Get-Location) "storage/baseline/follow-race-$prefix-$i.json";$err=Join-Path (Get-Location) "storage/baseline/follow-race-$prefix-$i.log"
  $p=Start-Process -FilePath E:/xampp/php/php.exe -ArgumentList @('tests/followed/race-worker.php',$student,$topic,$start) -WindowStyle Hidden -PassThru -RedirectStandardOutput $out -RedirectStandardError $err
  $workers+=@{Process=$p;Output=$out};$i++
 }
 foreach($worker in $workers){$worker.Process.WaitForExit();$value=Get-Content $worker.Output -Raw -Encoding UTF8|ConvertFrom-Json;if($null -eq $value.success){throw 'Malformed worker result'}}
}
Workers 1 (7..16) 'limit'
$count=SqlFollow 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1'
$checks=@([pscustomobject]@{Check='Ten simultaneous saves respect limit of three';Passed=($count -eq '3');Detail="Saved rows=$count"})
Workers 2 (@(16)*8) 'duplicate'
$count=SqlFollow 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=2 AND de_tai_id=16'
$checks+=[pscustomobject]@{Check='Eight simultaneous duplicate saves create one row';Passed=($count -eq '1');Detail="Matching rows=$count"}
$checks|ConvertTo-Json|Set-Content storage/baseline/followed-race-results.json -Encoding UTF8
$checks|Format-Table -AutoSize
