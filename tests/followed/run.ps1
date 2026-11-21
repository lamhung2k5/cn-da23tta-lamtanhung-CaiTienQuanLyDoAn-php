param([switch]$ResetTestDatabase,[switch]$Browser)
$ErrorActionPreference='Stop'
$root=Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
Set-Location -LiteralPath $root
if(Get-NetTCPConnection -State Listen -LocalPort 8089 -ErrorAction SilentlyContinue){throw 'Test port 8089 is in use'}
if($ResetTestDatabase){& E:/xampp/mysql/bin/mysql.exe -h 127.0.0.1 -u root -e 'DROP DATABASE IF EXISTS qldt_follow_test;';if($LASTEXITCODE -ne 0){throw 'Cannot reset synthetic followed database'}}
New-Item -ItemType Directory -Force storage/baseline|Out-Null
& E:/xampp/php/php.exe tests/followed/prepare.php
if($LASTEXITCODE -ne 0){throw 'Followed fixture setup failed'}
$oldDb=$env:LOCAL_DB_NAME;$oldMode=$env:FOLLOWED_TEST_MODE;$server=$null;$edge=$null
try{
 $env:LOCAL_DB_NAME='qldt_follow_test';$env:FOLLOWED_TEST_MODE='1'
 $server=Start-Process -FilePath E:/xampp/php/php.exe -ArgumentList @('-S','127.0.0.1:8089','-t','E:/xampp/htdocs') -WindowStyle Hidden -PassThru -RedirectStandardOutput "$root/storage/baseline/followed-server.out.log" -RedirectStandardError "$root/storage/baseline/followed-server.err.log"
 $env:LOCAL_DB_NAME=$oldDb;$env:FOLLOWED_TEST_MODE=$oldMode
 $identity=Invoke-WebRequest -UseBasicParsing http://127.0.0.1:8089/WebsiteQuanLyDeTai/tests/followed/environment.php -TimeoutSec 10
 if($identity.Content.Trim() -ne 'qldt_follow_test'){throw 'Wrong followed test server identity'}
 & ./tests/followed/http.ps1
 & ./tests/followed/race.ps1
 & E:/xampp/php/php.exe tests/followed/period.php
 if($LASTEXITCODE -ne 0){throw 'Period policy check failed'}
 if($Browser){
  & E:/xampp/php/php.exe tests/followed/browser_prepare.php
  if($LASTEXITCODE -ne 0){throw 'Followed browser fixture failed'}
  if(Get-NetTCPConnection -State Listen -LocalPort 9222 -ErrorAction SilentlyContinue){throw 'Browser debug port 9222 in use'}
  $edge=Start-Process -FilePath 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe' -ArgumentList @('--headless','--disable-gpu','--no-first-run','--remote-debugging-address=127.0.0.1','--remote-debugging-port=9222',"--user-data-dir=$root/storage/baseline/edge-followed",'about:blank') -WindowStyle Hidden -PassThru
  for($i=0;$i -lt 20;$i++){try{$null=Invoke-WebRequest -UseBasicParsing http://127.0.0.1:9222/json/version -TimeoutSec 1;break}catch{Start-Sleep -Milliseconds 100}}
  & node tests/followed/browser.mjs
  if($LASTEXITCODE -ne 0){throw 'Followed browser tests failed'}
 }
 $all=[Collections.Generic.List[object]]::new()
 $files=@('http','race','period');if($Browser){$files+='browser'}
 foreach($name in $files){$data=Get-Content "storage/baseline/followed-$name-results.json" -Raw -Encoding UTF8|ConvertFrom-Json;foreach($row in $data){$all.Add($row)}}
 $all|ConvertTo-Json -Depth 5|Set-Content storage/baseline/followed-all-results.json -Encoding UTF8
 $failed=@($all|Where-Object {!$_.Passed});"Followed checks: $($all.Count); failures: $($failed.Count)"
 if($failed.Count){$failed|Format-Table -AutoSize;throw 'Followed checks failed'}
}finally{
 $env:LOCAL_DB_NAME=$oldDb;$env:FOLLOWED_TEST_MODE=$oldMode
 if($server -and !$server.HasExited){Stop-Process -Id $server.Id -ErrorAction SilentlyContinue}
 if($edge -and !$edge.HasExited){Stop-Process -Id $edge.Id -ErrorAction SilentlyContinue}
}
