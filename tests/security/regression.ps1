$ErrorActionPreference = 'Stop'
$base = 'http://localhost:8088/WebsiteQuanLyDeTai/'
$tokens = @{}
$results = [Collections.Generic.List[object]]::new()
function Record($name, $ok, $detail) {
    $results.Add([pscustomobject]@{ Check=$name; Passed=[bool]$ok; Detail=$detail })
    "$name : $ok : $detail"
}
function Sql($query) {
    $value = & E:/xampp/mysql/bin/mysql.exe --default-character-set=utf8mb4 -h 127.0.0.1 -u root --batch --skip-column-names qldt_security_test -e $query
    if ($LASTEXITCODE -ne 0) { throw "Test database query failed." }
    return ($value -join "`n")
}
function Request($path, $session, $body=$null) {
    $mutation = $path -match '^(giang_vien/(gui_duyet|xu_ly_duyet|xoa_de_tai)|lanh_dao/xu_ly_duyet|sinh_vien/dang_ky_de_tai|auth/logout)\.php'
    if ($mutation -and $null -eq $body) { $body=@{} }
    if ($mutation -and $null -ne $body -and $path.Contains('?')) {
        $parts = $path.Split('?',2); $path=$parts[0]
        foreach ($pair in $parts[1].Split('&')) { $kv=$pair.Split('=',2); $body[$kv[0]]=[uri]::UnescapeDataString($kv[1]) }
    }
    $args = @{ Uri=($base+$path); UseBasicParsing=$true; TimeoutSec=20; WebSession=$session }
    if ($null -ne $body) { $args.Method='POST'; $args.Body=$body; $args.Headers=@{'X-CSRF-Token'=$tokens[$session.GetHashCode()]} }
    try { $result = Invoke-WebRequest @args } catch {
        if (!$_.Exception.Response) { throw }
        $response=$_.Exception.Response
        $reader=New-Object IO.StreamReader($response.GetResponseStream())
        $result=[pscustomobject]@{StatusCode=[int]$response.StatusCode; Content=$reader.ReadToEnd(); BaseResponse=$response}
        $reader.Close()
    }
    $token=[regex]::Match($result.Content,'name="csrf-token" content="([^"]+)"').Groups[1].Value
    if ($token) { $tokens[$session.GetHashCode()]=$token }
    return $result
}
function Session { New-Object Microsoft.PowerShell.Commands.WebRequestSession }
function Login($email) {
    $s = Session
    $page = Request 'auth/login.php' $s
    $token = [regex]::Match($page.Content, 'name="csrf_token" value="([^"]+)"').Groups[1].Value
    if (!$token) { throw 'Login CSRF field missing.' }
    $page = Request 'auth/login.php' $s @{ email=$email; password='BaselineDemo!2026'; csrf_token=$token }
    Record "Login $email" ($page.BaseResponse.ResponseUri.AbsolutePath -match '/dashboard.php$') $page.BaseResponse.ResponseUri.AbsolutePath
    return $s
}
function Run {
    $guard = Request 'tests/security/environment.php' (Session)
    if ($guard.Content.Trim() -ne 'qldt_security_test') { throw 'Server is not using isolated test DB. Stopping.' }
    if ((Sql 'SELECT COUNT(*) FROM nguoi_dung') -ne '5') { throw 'Expected fresh synthetic seed.' }
    foreach ($path in @('', 'danh_sach_de_tai_cong_khai.php', 'thong_bao.php', 'thong_bao_do_an.php')) {
        $page = Request $path (Session)
        $errors = [regex]::Matches($page.Content, '(?i)<b>(Fatal error|Warning|Deprecated|Notice)</b>:').Count
        Record "Public /$path" ($page.StatusCode -eq 200 -and $errors -eq 0) "HTTP $($page.StatusCode), PHP diagnostics=$errors"
    }
    $page = Request 'sinh_vien/dashboard.php' (Session)
    Record 'Guest blocked from student dashboard' ($page.BaseResponse.ResponseUri.AbsolutePath -match '/auth/login.php$') $page.BaseResponse.ResponseUri.AbsolutePath
    # Capture login output separately because Login also records diagnostics.
    $out = @(Login 'sv1@example.test'); $sv = $out[-1]; $out[0..($out.Count-2)] | Write-Output
    $out = @(Login 'sv2@example.test'); $sv2 = $out[-1]; $out[0..($out.Count-2)] | Write-Output
    $out = @(Login 'gv1@example.test'); $gv = $out[-1]; $out[0..($out.Count-2)] | Write-Output
    $out = @(Login 'gv2@example.test'); $gv2 = $out[-1]; $out[0..($out.Count-2)] | Write-Output
    $out = @(Login 'ld1@example.test'); $ld = $out[-1]; $out[0..($out.Count-2)] | Write-Output
    foreach ($entry in @(@{Path='sinh_vien/dashboard.php';Session=$sv}, @{Path='giang_vien/dashboard.php';Session=$gv}, @{Path='lanh_dao/dashboard.php';Session=$ld})) {
        $page = Request $entry.Path $entry.Session
        $errors = [regex]::Matches($page.Content, '(?i)<b>(Fatal error|Warning|Deprecated|Notice)</b>:').Count
        Record "Dashboard $($entry.Path)" ($page.StatusCode -eq 200 -and $errors -eq 0) "HTTP $($page.StatusCode), PHP diagnostics=$errors"
    }
    $page = Request 'lanh_dao/dashboard.php' $sv
    Record 'Student blocked from leadership dashboard' ($page.BaseResponse.ResponseUri.AbsolutePath -notmatch '/lanh_dao/') $page.BaseResponse.ResponseUri.AbsolutePath
    $null = Request 'giang_vien/tao_de_tai.php?loai=co_so_nganh' $gv @{tieu_de='Baseline created topic';mo_ta='Synthetic HTTP check';action='save'}
    $created = Sql "SELECT COUNT(*) FROM de_tai WHERE tieu_de='Baseline created topic' AND trang_thai='nhap'"
    Record 'Lecturer creates draft' ($created -eq '1') "Matching rows=$created"
    $null = Request 'giang_vien/sua_de_tai.php?id=4' $gv @{tieu_de='Baseline edited topic';mo_ta='Synthetic edit';he_dao_tao='co_so_nganh';action='save'}
    Record 'Lecturer edits own draft' ((Sql "SELECT tieu_de FROM de_tai WHERE id=4") -eq 'Baseline edited topic') 'Checked persisted title'
    $null = Request 'giang_vien/gui_duyet.php?id=4' $gv
    Record 'Lecturer submits topic' ((Sql 'SELECT trang_thai FROM de_tai WHERE id=4') -eq 'cho_duyet') 'Checked persisted status'
    $null = Request 'lanh_dao/xu_ly_duyet.php?action=duyet&id=3' $ld
    Record 'Leadership approves topic' ((Sql 'SELECT trang_thai FROM de_tai WHERE id=3') -eq 'da_duyet') 'Checked persisted status'
    $null = Request 'sinh_vien/dang_ky_de_tai.php?id=1' $sv
    $reg = Sql 'SELECT id FROM dang_ky_de_tai WHERE sinh_vien_id=1 AND de_tai_id=1'
    Record 'Student registers topic' (![string]::IsNullOrWhiteSpace($reg)) "Registration ID=$reg"
    Record 'Registration is immediately approved (confirmed rule)' ((Sql "SELECT trang_thai FROM dang_ky_de_tai WHERE id=$reg") -eq 'da_duyet') (Sql "SELECT trang_thai FROM dang_ky_de_tai WHERE id=$reg")
    $null = Request 'sinh_vien/dang_ky_de_tai.php?id=1' $sv2
    Record 'Full topic blocks second student (sequential)' ((Sql 'SELECT COUNT(*) FROM dang_ky_de_tai WHERE de_tai_id=1') -eq '1') 'Sequential check only; no concurrency claim'
    $page = Request 'sinh_vien/de_tai_cua_toi.php' $sv
    Record 'Student views own topics' ($page.StatusCode -eq 200 -and $page.Content -match 'DEMO_CSN01|CSN') "HTTP $($page.StatusCode)"
    $page = Request 'sinh_vien/xoa_dang_ky.php' $sv2 @{dang_ky_id='1'}
    Record 'Student deletes rejected registration' (($page.Content | ConvertFrom-Json).success -and (Sql 'SELECT COUNT(*) FROM dang_ky_de_tai WHERE id=1') -eq '0') 'JSON success and row removed'
    $page = Request 'sinh_vien/xoa_dang_ky.php' $sv @{dang_ky_id=$reg}
    Record 'Student cannot delete approved registration' (!($page.Content | ConvertFrom-Json).success -and (Sql "SELECT COUNT(*) FROM dang_ky_de_tai WHERE id=$reg") -eq '1') 'Existing rule permits deletion only after rejection'
    # Cross-owner approval should be denied; this intentionally detects an existing defect.
    $null = Request "giang_vien/xu_ly_duyet.php?action=duyet&id=$reg" $gv2
    $wrongOwner = Sql "SELECT COALESCE(nguoi_duyet_id,0) FROM dang_ky_de_tai WHERE id=$reg"
    Record 'Other lecturer cannot approve registration' ($wrongOwner -ne '4') "Approver user ID=$wrongOwner (4 is other lecturer)"
    $null = Request "giang_vien/xu_ly_duyet.php?action=duyet&id=$reg" $gv
    $null = Request "giang_vien/xu_ly_duyet.php?action=duyet&id=$reg" $gv
    $count = Sql 'SELECT so_luong_da_dang_ky FROM de_tai WHERE id=1'
    Record 'Repeated approval preserves registration counter' ($count -eq '1') "Stored counter=$count; actual registrations=1"
    $otpTables = Sql "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='otp_verification'"
    $closed = Request 'auth/verify_otp.php' (Session)
    Record 'Unused OTP endpoint disabled' ($closed.StatusCode -eq 404) "HTTP $($closed.StatusCode)"
    $null = Request 'auth/logout.php' $sv
    $page = Request 'sinh_vien/dashboard.php' $sv
    Record 'Logout clears access' ($page.BaseResponse.ResponseUri.AbsolutePath -match '/auth/login.php$') $page.BaseResponse.ResponseUri.AbsolutePath
}
try { Run } finally {
    $results | ConvertTo-Json -Depth 4 | Set-Content -Encoding UTF8 storage/baseline/security-results.json
}
