$ErrorActionPreference='Stop'
$source=Get-Content tests/security/regression.ps1 -Raw -Encoding UTF8
Invoke-Expression $source.Substring(0,$source.IndexOf('try { Run } finally'))
$utf8=New-Object System.Text.UTF8Encoding($false)
function Upload($path,$session,$filename) {
    $cookie=($session.Cookies.GetCookies([uri]$base) | ForEach-Object {$_.Name+'='+$_.Value}) -join '; '
    $target=Join-Path (Get-Location) 'storage/baseline/import-response.html'
    $token=$tokens[$session.GetHashCode()]
    $status=& curl.exe -sS --max-time 20 -o $target -w '%{http_code}' --cookie $cookie -H "X-CSRF-Token: $token" -F "file=@$filename" ($base+$path)
    if ($LASTEXITCODE -ne 0) {throw 'Upload failed'}
    return [int]$status
}
function Fixture($name,$text) {
    $path=Join-Path (Get-Location) "storage/baseline/$name"
    [IO.File]::WriteAllText($path,$text,$utf8)
    return $path
}
try {
    $out=@(Login 'ld1@example.test');$ld=$out[-1]
    $out=@(Login 'gv1@example.test');$gv=$out[-1]
    $good=Fixture 'accounts-good.csv' "email,ho_ten,mat_khau,ma_sinh_vien`nimported@example.test,Imported Demo,ImportDemo!2026,DEMO_IMPORT`n"
    $status=Upload 'auth/import_sinh_vien.php' $ld $good
    Record 'Leadership imports valid CSV' ($status -eq 200 -and (Sql "SELECT COUNT(*) FROM nguoi_dung nd JOIN sinh_vien sv ON sv.nguoi_dung_id=nd.id WHERE nd.email='imported@example.test'") -eq '1') "HTTP $status"
    $badRole=Fixture 'accounts-role.csv' "email,ho_ten,mat_khau,ma_giang_vien,vai_tro`nrole-attack@example.test,Attack,ImportDemo!2026,DEMO_ROLE,lanh_dao`n"
    $status=Upload 'auth/import_giang_vien.php' $ld $badRole
    Record 'Import cannot grant leadership role' ((Sql "SELECT COUNT(*) FROM nguoi_dung WHERE email='role-attack@example.test'") -eq '0') "HTTP $status"
    $partial=Fixture 'accounts-rollback.csv' "email,ho_ten,mat_khau,ma_sinh_vien`nrollback@example.test,Rollback Demo,ImportDemo!2026,DEMO_ROLLBACK`nsv2@example.test,Duplicate Demo,ImportDemo!2026,DEMO_DUP`n"
    $status=Upload 'auth/import_sinh_vien.php' $ld $partial
    Record 'Import rolls back earlier rows after duplicate' ((Sql "SELECT COUNT(*) FROM nguoi_dung WHERE email='rollback@example.test'") -eq '0') "HTTP $status"
    $status=Upload 'auth/import_sinh_vien.php' $gv $good
    Record 'Lecturer cannot import accounts' ($status -eq 302 -or $status -eq 403) "HTTP $status"
    $mismatch=Fixture 'accounts-malformed.csv' "email,ho_ten,mat_khau,ma_sinh_vien`nmalformed@example.test,Demo,ImportDemo!2026,DEMO_BAD,EXTRA`n"
    $status=Upload 'auth/import_sinh_vien.php' $ld $mismatch
    Record 'Malformed CSV row rejected' ((Sql "SELECT COUNT(*) FROM nguoi_dung WHERE email='malformed@example.test'") -eq '0') "HTTP $status"
    $oversize=Fixture 'accounts-oversize.csv' ('x'*(2*1024*1024+1))
    $status=Upload 'auth/import_sinh_vien.php' $ld $oversize
    $html=Get-Content storage/baseline/import-response.html -Raw -Encoding UTF8
    Record 'Oversized import rejected' ($html -match '2 MB|không hợp lệ') "HTTP $status"
} finally {
    $results | ConvertTo-Json -Depth 4 | Set-Content storage/baseline/security-import-results.json -Encoding UTF8
}
