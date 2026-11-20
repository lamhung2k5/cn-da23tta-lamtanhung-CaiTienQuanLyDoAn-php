$ErrorActionPreference='Stop'
$source=Get-Content tests/security/import.ps1 -Raw -Encoding UTF8
$cut=$source.IndexOf("try {`n    `$out=")
if($cut -lt 0){$cut=$source.IndexOf("try {`r`n    `$out=")}
if($cut -lt 0){throw 'Import helper definitions not found'}
Invoke-Expression $source.Substring(0,$cut)
& E:/xampp/php/php.exe -d extension=zip tests/security/make-xlsx.php
if($LASTEXITCODE -ne 0){throw 'XLSX fixture generation failed'}
try {
    $out=@(Login 'ld1@example.test');$ld=$out[-1]
    $status=Upload 'auth/import_sinh_vien.php' $ld (Join-Path (Get-Location) 'storage/baseline/accounts-valid.xlsx')
    Record 'Valid XLSX imported with ZIP enabled' ((Sql "SELECT COUNT(*) FROM nguoi_dung WHERE email='xlsx-demo@example.test'") -eq '1') "HTTP $status"
    $status=Upload 'auth/import_sinh_vien.php' $ld (Join-Path (Get-Location) 'storage/baseline/accounts-entity.xlsx')
    $html=Get-Content storage/baseline/import-response.html -Raw -Encoding UTF8
    Record 'XLSX external entity rejected' ($html -match 'XML không hợp lệ') "HTTP $status"
} finally {
    $results | ConvertTo-Json -Depth 4 | Set-Content storage/baseline/security-xlsx-results.json -Encoding UTF8
}
