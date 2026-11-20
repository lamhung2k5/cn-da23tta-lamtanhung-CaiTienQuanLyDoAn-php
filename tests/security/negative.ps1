$ErrorActionPreference='Stop'
# Load only helper definitions from the existing harness; do not re-run its seed-dependent checks.
$source = Get-Content tests/security/regression.ps1 -Raw -Encoding UTF8
$definitions = $source.Substring(0, $source.IndexOf('try { Run } finally'))
Invoke-Expression $definitions
function RawRequest($path,$session,$body=$null,$headers=@{}) {
    $args=@{Uri=($base+$path);UseBasicParsing=$true;TimeoutSec=20;WebSession=$session;Headers=$headers}
    if ($null -ne $body) {$args.Method='POST';$args.Body=$body}
    try {Invoke-WebRequest @args} catch {
        if (!$_.Exception.Response) {throw}
        $response=$_.Exception.Response
        $reader=New-Object IO.StreamReader($response.GetResponseStream())
        $result=[pscustomobject]@{StatusCode=[int]$response.StatusCode;Content=$reader.ReadToEnd();BaseResponse=$response}
        $reader.Close();$result
    }
}
function RunNegative {
    foreach ($path in @('register','verify_otp','google_login','google_callback','github_login','github_callback','microsoft_login','microsoft_callback','forgot_password','reset_password','new_reset','dat-lai-mat-khau','update_profile_code','login_ajax','refresh_captcha')) {
        $get=RawRequest "auth/$path.php" (Session)
        $post=RawRequest "auth/$path.php" (Session) @{email='attacker@example.test';vai_tro='lanh_dao'}
        Record "Disabled $path GET/POST" ($get.StatusCode -eq 404 -and $post.StatusCode -eq 404) "GET=$($get.StatusCode), POST=$($post.StatusCode)"
    }
    $out=@(Login 'sv1@example.test');$sv=$out[-1]
    $out=@(Login 'sv2@example.test');$sv2=$out[-1]
    $out=@(Login 'gv1@example.test');$gv=$out[-1]
    $out=@(Login 'gv2@example.test');$gv2=$out[-1]
    $out=@(Login 'ld1@example.test');$ld=$out[-1]
    $before=Sql 'SELECT COUNT(*) FROM nguoi_dung'
    foreach ($path in @('auth/import_sinh_vien.php','auth/import_giang_vien.php','lanh_dao/sua_ngay_thong_bao.php','lanh_dao/quan_ly_nguoi_dung.php')) {
        $r=Request $path $sv
        Record "Student blocked $path" ($r.BaseResponse.ResponseUri.AbsolutePath -notmatch 'import_|sua_ngay|quan_ly_nguoi') $r.BaseResponse.ResponseUri.AbsolutePath
    }
    $r=RawRequest 'giang_vien/tao_de_tai.php?loai=co_so_nganh' $gv @{tieu_de='CSRF attack';mo_ta='attack'}
    Record 'Missing CSRF rejected' ($r.StatusCode -eq 403) "HTTP $($r.StatusCode)"
    $r=RawRequest 'giang_vien/tao_de_tai.php?loai=co_so_nganh' $gv @{tieu_de='CSRF attack';mo_ta='attack';'csrf_token[]'='bad'}
    Record 'Array CSRF rejected without exception' ($r.StatusCode -eq 403) "HTTP $($r.StatusCode)"
    $r=RawRequest 'lanh_dao/xu_ly_cai_dat.php' $ld @{key_name='attack';value='1';csrf_token='wrong'}
    Record 'Wrong CSRF rejected' ($r.StatusCode -eq 403) "HTTP $($r.StatusCode)"
    foreach ($path in @('sinh_vien/dang_ky_de_tai.php?id=2','giang_vien/xu_ly_duyet.php?action=duyet&id=2','giang_vien/xoa_de_tai.php?id=4','giang_vien/gui_duyet.php?id=4','lanh_dao/xu_ly_duyet.php?action=duyet&id=4','auth/logout.php')) {
        $session=if($path.StartsWith('sinh_vien/')){$sv}elseif($path.StartsWith('lanh_dao/')){$ld}else{$gv}
        $r=RawRequest $path $session
        Record "GET cannot mutate $path" ($r.StatusCode -eq 405) "HTTP $($r.StatusCode)"
    }
    $r=Request 'giang_vien/sua_de_tai.php?id=5' $gv @{tieu_de='attack';mo_ta='attack';he_dao_tao='co_so_nganh'}
    Record 'Cross-owner topic edit denied' ($r.StatusCode -eq 403) "HTTP $($r.StatusCode)"
    $r=Request 'giang_vien/chi_tiet_de_tai.php?id=5' $gv
    Record 'Cross-owner topic details denied' ($r.StatusCode -eq 403) "HTTP $($r.StatusCode)"
    $r=Request 'giang_vien/xu_ly_duyet.php' $gv2 @{action='tu_choi';dang_ky_id='2';ly_do='attack'}
    Record 'Cross-owner rejection denied' ($r.StatusCode -eq 403) "HTTP $($r.StatusCode)"
    $r=Request 'giang_vien/xu_ly_duyet_hang_loat.php' $gv2 @{action='duyet_hang_loat';dang_ky_ids='2'}
    Record 'Cross-owner bulk approval denied' ($r.StatusCode -eq 403) "HTTP $($r.StatusCode)"
    $r=Request 'sinh_vien/xoa_dang_ky.php' $sv2 @{dang_ky_id='2'}
    Record 'Student cannot delete another registration' ($r.StatusCode -eq 403) "HTTP $($r.StatusCode)"
    $r=Request 'lanh_dao/xu_ly_tao_nguoi_dung.php' $ld @{vai_tro='admin';email='attack@example.test';ho_ten='Attack';mat_khau='ExamplePassword!';xac_nhan_mat_khau='ExamplePassword!'}
    Record 'Unknown role rejected' ($r.StatusCode -eq 400 -and (Sql 'SELECT COUNT(*) FROM nguoi_dung') -eq $before) "HTTP $($r.StatusCode)"
    $r=Request 'lanh_dao/xu_ly_tao_nguoi_dung.php' $ld @{vai_tro='lanh_dao';email='attack@example.test';ho_ten='Attack';mat_khau='ExamplePassword!';xac_nhan_mat_khau='ExamplePassword!';ma_lanh_dao='DEMO_ATTACK'}
    Record 'Leadership creation requires password confirmation' ($r.StatusCode -eq 403 -and (Sql 'SELECT COUNT(*) FROM nguoi_dung') -eq $before) "HTTP $($r.StatusCode)"
    $r=Request 'lanh_dao/xu_ly_tao_nguoi_dung.php' $ld @{vai_tro='sinh_vien';email='created@example.test';ho_ten='Created demo';mat_khau='ExamplePassword!';xac_nhan_mat_khau='ExamplePassword!';ma_sinh_vien='DEMO_CREATED'}
    Record 'Leadership creates complete student account' ((Sql "SELECT COUNT(*) FROM nguoi_dung nd JOIN sinh_vien sv ON sv.nguoi_dung_id=nd.id WHERE nd.email='created@example.test'") -eq '1') "HTTP $($r.StatusCode)"
    $payload='<img src=x onerror=alert(1)>'
    $r=Request 'giang_vien/tao_de_tai.php?loai=co_so_nganh' $gv @{tieu_de=$payload;mo_ta=''}
    Record 'Rejected topic form escapes submitted HTML' ($r.Content -notmatch '<img src=x onerror=alert\(1\)>' -and $r.Content.Contains('&lt;img')) "HTTP $($r.StatusCode)"
    $payload='</script><script>alert(99)</script>'
    $null=Sql "UPDATE de_tai SET tieu_de='$payload' WHERE id=5"
    $r=Request 'lanh_dao/de_tai_da_duyet.php' $ld
    Record 'JSON embedded in scripts escapes closing tag' (!$r.Content.Contains($payload) -and $r.Content.Contains('\u003C')) 'Checked script data encoding'
    $r=Request 'lanh_dao/cau_hinh_menu.php' $ld @{ket_qua_thi='javascript:alert(1)';bieu_mau_sinh_vien='';bieu_mau_giang_vien=''}
    Record 'Unsafe URL scheme rejected' ($r.StatusCode -eq 400) "HTTP $($r.StatusCode)"
    $r=Request 'lanh_dao/xu_ly_tai_khoan.php' $ld @{action='set_status';user_id='5';status='inactive';current_password='BaselineDemo!2026'}
    Record 'Last leadership account remains active' ((Sql 'SELECT trang_thai FROM nguoi_dung WHERE id=5') -eq 'active') 'Self-lock denied'
    $r=Request 'lanh_dao/xu_ly_tai_khoan.php' $ld @{action='set_status';user_id='2';status='inactive';current_password='BaselineDemo!2026'}
    $r=Request 'sinh_vien/dashboard.php' $sv2
    Record 'Lock invalidates an existing student session' ((Sql 'SELECT trang_thai FROM nguoi_dung WHERE id=2') -eq 'inactive' -and $r.BaseResponse.ResponseUri.AbsolutePath -match '/auth/login.php$') $r.BaseResponse.ResponseUri.AbsolutePath
    $null=Request 'lanh_dao/xu_ly_tai_khoan.php' $ld @{action='set_status';user_id='2';status='active';current_password='BaselineDemo!2026'}
    $null=Request 'lanh_dao/xu_ly_tai_khoan.php' $ld @{action='reset_password';user_id='1';new_password='ResetDemo!2026';confirm_password='ResetDemo!2026';current_password='BaselineDemo!2026'}
    $r=Request 'sinh_vien/dashboard.php' $sv
    Record 'Password reset invalidates existing session' ($r.BaseResponse.ResponseUri.AbsolutePath -match '/auth/login.php$') $r.BaseResponse.ResponseUri.AbsolutePath
    # Create a legacy hash only in synthetic data; it must no longer authenticate.
    $null=Sql "UPDATE nguoi_dung SET mat_khau=MD5('LegacyDemo!2026') WHERE email='created@example.test'"
    $legacy=Session;$r=Request 'auth/login.php' $legacy
    $r=Request 'auth/login.php' $legacy @{email='created@example.test';password='LegacyDemo!2026';csrf_token=$tokens[$legacy.GetHashCode()]}
    Record 'Legacy MD5 password rejected' ($r.BaseResponse.ResponseUri.AbsolutePath -match '/auth/login.php$') $r.BaseResponse.ResponseUri.AbsolutePath
    # Account-level rate limiting must survive a new browser session.
    for($i=0;$i -lt 6;$i++) {
        $attempt=Session;$null=Request 'auth/login.php' $attempt
        $r=Request 'auth/login.php' $attempt @{email='nobody-rate-limit@example.test';password='WrongDemo!2026';csrf_token=$tokens[$attempt.GetHashCode()]}
    }
    Record 'Login rate limit survives new sessions' ($r.Content -match '15 ph') 'Six failed attempts from independent sessions'
}
try {RunNegative} finally {
    $results | ConvertTo-Json -Depth 4 | Set-Content storage/baseline/security-negative-results.json -Encoding UTF8
}
