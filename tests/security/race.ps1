$ErrorActionPreference='Stop'
$fixture=php tests/security/race-setup.php | ConvertFrom-Json
if($LASTEXITCODE -ne 0){throw 'Race setup failed'}
$start=([DateTimeOffset]::UtcNow.ToUnixTimeMilliseconds()/1000.0+4).ToString([Globalization.CultureInfo]::InvariantCulture)
$workers=@();$index=0
foreach($job in $fixture.jobs){
    $out=Join-Path (Get-Location) "storage/baseline/race-$index.json"
    $err=Join-Path (Get-Location) "storage/baseline/race-$index.log"
    $process=Start-Process -FilePath E:/xampp/php/php.exe -ArgumentList @('tests/security/race-worker.php',$job.user,$job.profile,$job.topic,$start) -WindowStyle Hidden -PassThru -RedirectStandardOutput $out -RedirectStandardError $err
    $workers+=@{Process=$process;Output=$out;Group=$job.group};$index++
}
$results=@()
foreach($worker in $workers){$worker.Process.WaitForExit();$value=Get-Content $worker.Output -Raw -Encoding UTF8 | ConvertFrom-Json;$results+=[pscustomobject]@{Group=$worker.Group;Success=[bool]$value.success}}
$checks=@()
foreach($group in @('slot','lecturer','student')){
    $success=@($results|Where-Object {$_.Group -eq $group -and $_.Success}).Count
    $checks+=[pscustomobject]@{Check="Concurrent registration preserves $group limit";Passed=($success -eq 1);Detail="Successful registrations=$success"}
}
$mismatch=& E:/xampp/mysql/bin/mysql.exe -h 127.0.0.1 -u root --batch --skip-column-names qldt_security_test -e "SELECT COUNT(*) FROM de_tai dt WHERE dt.so_luong_da_dang_ky <> (SELECT COUNT(*) FROM dang_ky_de_tai dk WHERE dk.de_tai_id=dt.id AND dk.trang_thai='da_duyet');"
$checks+=[pscustomobject]@{Check='All counters match approved registrations';Passed=($mismatch -eq '0');Detail="Mismatched topics=$mismatch"}
$checks | ConvertTo-Json | Set-Content storage/baseline/security-race-results.json -Encoding UTF8
$checks | Format-Table -AutoSize
