param([switch]$ResetTestDatabase,[switch]$Browser)
$ErrorActionPreference='Stop'
$projectRoot=Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
Set-Location -LiteralPath $projectRoot
$php='E:/xampp/php/php.exe'
$mysql='E:/xampp/mysql/bin/mysql.exe'
New-Item -ItemType Directory -Force storage/baseline | Out-Null
$existing=Get-NetTCPConnection -State Listen -LocalPort 8088 -ErrorAction SilentlyContinue
if($existing){throw 'Port 8088 is already in use; stop the old test server first.'}
if($ResetTestDatabase){
    # Only this hardcoded synthetic database may be reset. Never use the application database name here.
    & $mysql -h 127.0.0.1 -u root -e 'DROP DATABASE IF EXISTS qldt_security_test;'
    if($LASTEXITCODE -ne 0){throw 'Cannot reset synthetic test database'}
}
& $php tests/security/prepare.php
if($LASTEXITCODE -ne 0){throw 'Test database must be new; use -ResetTestDatabase only for qldt_security_test.'}
# A newly seeded test database also needs fresh synthetic sessions/throttles.
# Preserve audit logs and never touch the namespace belonging to qldt_database.
$sha=[Security.Cryptography.SHA256]::Create()
try{$testKey=([BitConverter]::ToString($sha.ComputeHash([Text.Encoding]::UTF8.GetBytes('127.0.0.1:3306/qldt_security_test')))).Replace('-','').ToLowerInvariant()}finally{$sha.Dispose()}
$securityRoot=[IO.Path]::GetFullPath((Join-Path $projectRoot 'storage/security'))
$testSecurity=[IO.Path]::GetFullPath((Join-Path $securityRoot $testKey))
if(!$testSecurity.StartsWith($securityRoot+[IO.Path]::DirectorySeparatorChar,[StringComparison]::OrdinalIgnoreCase) -or $testKey -notmatch '^[a-f0-9]{64}$'){throw 'Invalid test security namespace'}
if(Test-Path -LiteralPath $testSecurity){
    Get-ChildItem -LiteralPath $testSecurity -File | Where-Object {$_.Name -match '^(login-[a-f0-9]{64}\.json|account-[0-9]+\.version)$'} | ForEach-Object {Remove-Item -LiteralPath $_.FullName}
}
$previousDb=$env:LOCAL_DB_NAME
$previousMode=$env:SECURITY_TEST_MODE
$server=$null;$browserProcess=$null
try {
    $env:LOCAL_DB_NAME='qldt_security_test';$env:SECURITY_TEST_MODE='1'
    $server=Start-Process -FilePath $php -ArgumentList @('-d','extension=zip','-S','127.0.0.1:8088','-t','E:/xampp/htdocs') -WindowStyle Hidden -PassThru -RedirectStandardOutput "$projectRoot/storage/baseline/suite-server.out.log" -RedirectStandardError "$projectRoot/storage/baseline/suite-server.err.log"
    $env:LOCAL_DB_NAME=$previousDb;$env:SECURITY_TEST_MODE=$previousMode
    $ready=$false
    $startupError='No response'
    for($attempt=0;$attempt -lt 5;$attempt++){
        try {$identity=Invoke-WebRequest -UseBasicParsing http://127.0.0.1:8088/WebsiteQuanLyDeTai/tests/security/environment.php -TimeoutSec 5;if($identity.Content.Trim() -eq 'qldt_security_test'){$ready=$true;break};$startupError='Unexpected database identity: '+$identity.Content}catch{$startupError=$_.Exception.Message}
        Start-Sleep -Milliseconds 100
    }
    if(!$ready){throw ('Test server identity check failed: '+$startupError)}
    & ./tests/security/regression.ps1
    & ./tests/security/negative.ps1
    & $php tests/security/model_checks.php
    if($LASTEXITCODE -ne 0){throw 'Model security checks failed'}
    & ./tests/security/import.ps1
    & ./tests/security/xlsx.ps1
    & ./tests/security/race.ps1
    if($Browser){
        if(Get-NetTCPConnection -State Listen -LocalPort 9222 -ErrorAction SilentlyContinue){throw 'Port 9222 already in use'}
        $browserProcess=Start-Process -FilePath 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe' -ArgumentList @('--headless','--disable-gpu','--no-first-run','--remote-debugging-address=127.0.0.1','--remote-debugging-port=9222',"--user-data-dir=$projectRoot/storage/baseline/edge-suite",'about:blank') -WindowStyle Hidden -PassThru
        for($attempt=0;$attempt -lt 30;$attempt++){
            try {$null=Invoke-WebRequest -UseBasicParsing http://127.0.0.1:9222/json/version -TimeoutSec 1;break}catch{Start-Sleep -Milliseconds 100}
        }
        & node tests/security/browser.mjs
        if($LASTEXITCODE -ne 0){throw 'Browser test runner failed'}
    }
    $all=[Collections.Generic.List[object]]::new()
    foreach($name in @('security-results','security-negative-results','security-model-results','security-import-results','security-xlsx-results','security-race-results')){
        $parsed=Get-Content "storage/baseline/$name.json" -Raw -Encoding UTF8|ConvertFrom-Json
        foreach($entry in $parsed){$all.Add($entry)}
    }
    if($Browser){$parsed=Get-Content storage/baseline/security-browser-results.json -Raw -Encoding UTF8|ConvertFrom-Json;foreach($entry in $parsed){$all.Add($entry)}}
    $all|ConvertTo-Json -Depth 5|Set-Content storage/baseline/security-all-results.json -Encoding UTF8
    $failed=@($all|Where-Object {!$_.Passed})
    Write-Output "Total checks: $($all.Count); failures: $($failed.Count)"
    if($failed.Count){$failed|Format-Table -AutoSize;throw 'Security checks failed'}
} finally {
    $env:LOCAL_DB_NAME=$previousDb;$env:SECURITY_TEST_MODE=$previousMode
    if($server -and !$server.HasExited){Stop-Process -Id $server.Id -ErrorAction SilentlyContinue}
    if($browserProcess -and !$browserProcess.HasExited){Stop-Process -Id $browserProcess.Id -ErrorAction SilentlyContinue}
}
