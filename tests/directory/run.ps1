param([switch]$ReuseTestDatabase,[switch]$Browser)
$ErrorActionPreference='Stop'
$root=Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
Set-Location -LiteralPath $root
if(Get-NetTCPConnection -State Listen -LocalPort 8090 -ErrorAction SilentlyContinue){throw 'Port 8090 in use'}
if(!$ReuseTestDatabase) {
    & E:/xampp/php/php.exe tests/directory/prepare.php
    if($LASTEXITCODE -ne 0){throw 'Directory fixtures failed; existing test DB is never overwritten'}
}
$previousDb=$env:LOCAL_DB_NAME; $previousMode=$env:DIRECTORY_TEST_MODE; $server=$null; $edge=$null
try {
    $env:LOCAL_DB_NAME='qldt_directory_test'; $env:DIRECTORY_TEST_MODE='1'
    $server=Start-Process -FilePath E:/xampp/php/php.exe -ArgumentList @('-S','127.0.0.1:8090','-t','E:/xampp/htdocs') -WindowStyle Hidden -PassThru -RedirectStandardOutput "$root/storage/baseline/directory-server.out.log" -RedirectStandardError "$root/storage/baseline/directory-server.err.log"
    for($attempt=0;$attempt -lt 30;$attempt++) {
        try { $response=Invoke-WebRequest -UseBasicParsing 'http://127.0.0.1:8090/WebsiteQuanLyDeTai/tests/directory/environment.php' -TimeoutSec 2; if($response.Content.Trim() -eq 'qldt_directory_test'){break} } catch { Start-Sleep -Milliseconds 100 }
    }
    & node tests/directory/http.mjs
    if($LASTEXITCODE -ne 0){throw 'Directory HTTP tests failed'}
    if($Browser) {
        if(Get-NetTCPConnection -State Listen -LocalPort 9223 -ErrorAction SilentlyContinue){throw 'Port 9223 in use'}
        $edge=Start-Process -FilePath 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe' -ArgumentList @('--headless','--disable-gpu','--no-first-run','--remote-debugging-address=127.0.0.1','--remote-debugging-port=9223',"--user-data-dir=$root/storage/baseline/edge-directory",'about:blank') -WindowStyle Hidden -PassThru
        for($attempt=0;$attempt -lt 30;$attempt++){try{$null=Invoke-WebRequest -UseBasicParsing http://127.0.0.1:9223/json/version -TimeoutSec 1;break}catch{Start-Sleep -Milliseconds 100}}
        & node tests/directory/browser.mjs
        if($LASTEXITCODE -ne 0){throw 'Directory browser checks failed'}
    }
} finally {
    $env:LOCAL_DB_NAME=$previousDb; $env:DIRECTORY_TEST_MODE=$previousMode
    if($server -and !$server.HasExited){Stop-Process -Id $server.Id -ErrorAction SilentlyContinue}
    if($edge -and !$edge.HasExited){Stop-Process -Id $edge.Id -ErrorAction SilentlyContinue}
}
