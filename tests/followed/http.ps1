$ErrorActionPreference='Stop'
$base='http://127.0.0.1:8089/WebsiteQuanLyDeTai/'
$tokens=@{}
$results=[Collections.Generic.List[object]]::new()
function Record($name,$ok,$detail=''){$results.Add([pscustomobject]@{Check=$name;Passed=[bool]$ok;Detail=$detail});"$name : $ok : $detail"}
function Sql($query){$value=& E:/xampp/mysql/bin/mysql.exe -h 127.0.0.1 -u root --default-character-set=utf8mb4 --batch --skip-column-names qldt_follow_test -e $query;if($LASTEXITCODE -ne 0){throw 'Followed SQL failed'};return ($value -join "`n")}
function Session{New-Object Microsoft.PowerShell.Commands.WebRequestSession}
function Request($path,$session,$body=$null,$raw=$false){
 $args=@{Uri=($base+$path);UseBasicParsing=$true;WebSession=$session;TimeoutSec=15}
 if($null -ne $body){$args.Method='POST';$args.Body=$body;if(!$raw){$args.Headers=@{'X-CSRF-Token'=$tokens[$session.GetHashCode()]}}}
 try{$r=Invoke-WebRequest @args}catch{if(!$_.Exception.Response){throw};$response=$_.Exception.Response;$reader=New-Object IO.StreamReader($response.GetResponseStream());$r=[pscustomobject]@{StatusCode=[int]$response.StatusCode;Content=$reader.ReadToEnd();BaseResponse=$response};$reader.Close()}
 $token=[regex]::Match($r.Content,'name="csrf-token" content="([^"]+)"').Groups[1].Value
 if($token){$tokens[$session.GetHashCode()]=$token};return $r
}
function Login($email){$s=Session;$page=Request 'auth/login.php' $s;$page=Request 'auth/login.php' $s @{email=$email;password='BaselineDemo!2026';csrf_token=$tokens[$s.GetHashCode()]};if($page.BaseResponse.ResponseUri.AbsolutePath -notmatch '/dashboard.php$'){throw "Login failed $email"};return $s}
function AddTopic($session,$id){Request 'sinh_vien/xu_ly_quan_tam.php' $session @{action='add';de_tai_id=$id;return_to='followed'}}
function Settings($limit,$enabled='1',$start='2000-01-01T00:00:00',$end='2100-01-01T00:00:00'){
 return @{gioi_han_de_tai_quan_tam=$limit;cho_phep_dang_ky=$enabled;dang_ky_csn_bat_dau=$start;dang_ky_csn_ket_thuc=$end;dang_ky_cn_bat_dau=$start;dang_ky_cn_ket_thuc=$end}
}
function RunFollowed{
 $guard=Request 'tests/followed/environment.php' (Session);if($guard.Content.Trim() -ne 'qldt_follow_test'){throw 'Wrong test server identity'}
 $sv=Login 'sv1@example.test';$sv2=Login 'sv2@example.test';$gv=Login 'gv1@example.test';$ld=Login 'ld1@example.test'
 $r=Request 'sinh_vien/de_tai_quan_tam.php' $sv
 Record 'Empty saved list displays guidance' ($r.Content.Contains('Bạn chưa quan tâm đề tài nào')) ('HTTP '+$r.StatusCode)
 $r=Request 'sinh_vien/xu_ly_quan_tam.php' $sv
 Record 'GET cannot change saved topics' ($r.StatusCode -eq 405) ('HTTP '+$r.StatusCode)
 $r=Request 'sinh_vien/xu_ly_quan_tam.php' $sv @{action='add';de_tai_id='1'} $true
 Record 'Missing CSRF cannot save' ($r.StatusCode -eq 403 -and (Sql 'SELECT COUNT(*) FROM de_tai_quan_tam') -eq '0') ('HTTP '+$r.StatusCode)
 $r=Request 'sinh_vien/xu_ly_quan_tam.php' $gv @{action='add';de_tai_id='1'}
 Record 'Lecturer cannot use student followed actions' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam') -eq '0' -and $r.BaseResponse.ResponseUri.AbsolutePath -notmatch '/sinh_vien/') $r.BaseResponse.ResponseUri.AbsolutePath
 $null=Request 'lanh_dao/xu_ly_cai_dat_dang_ky.php' $ld (Settings 6 '1' '2100-01-01T00:00:00' '2100-12-31T23:59:59')
 $null=AddTopic $sv '1'
 Record 'Saving before registration opens is allowed' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=1') -eq '1') 'Saved while schedule starts in 2100'
 $r=Request 'sinh_vien/de_tai_quan_tam.php' $sv
 Record 'Saved page blocks quick registration before opening' ($r.Content.Contains('Chưa mở đăng ký') -and !$r.Content.Contains('data-register-topic=')) 'Server-rendered action unavailable'
 $null=Request 'sinh_vien/dang_ky_de_tai.php' $sv @{id='1';return_to='followed'}
 Record 'Direct POST cannot register before opening' ((Sql 'SELECT COUNT(*) FROM dang_ky_de_tai WHERE sinh_vien_id=1 AND de_tai_id=1') -eq '0') 'Database unchanged'
 $null=AddTopic $sv '1'
 Record 'Repeated save is idempotent' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1') -eq '1') 'One row only'
 foreach($id in @(2,5,6,7,8)){$null=AddTopic $sv $id}
 Record 'Default limit accepts six across both types' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1') -eq '6') 'Combined limit, not six per type'
 $null=AddTopic $sv '9'
 Record 'Seventh topic is rejected' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1') -eq '6') 'No extra row'
 $r=Request 'sinh_vien/de_tai_quan_tam.php?loai=chuyen_nganh' $sv
 Record 'Type filter returns only selected topics' ([regex]::Matches($r.Content,'data-saved-topic=').Count -eq 2) 'Two saved major topics'
 $r=Request 'sinh_vien/de_tai_quan_tam.php?sinh_vien_id=1' $sv2
 Record 'Query cannot expose another student list' (!$r.Content.Contains('data-saved-topic=') -and $r.Content.Contains('Bạn chưa quan tâm đề tài nào')) 'Student identity derives from session'
 $first=Sql 'SELECT id FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=1'
 $null=Request 'sinh_vien/xu_ly_quan_tam.php' $sv2 @{action='remove';quan_tam_id=$first;return_to='followed'}
 Record 'Cross-student removal is rejected' ((Sql "SELECT COUNT(*) FROM de_tai_quan_tam WHERE id=$first") -eq '1') 'Owner row preserved'
 $null=AddTopic $sv2 '4';$null=AddTopic $sv2 '999999'
 Record 'Draft and nonexistent topics cannot be saved' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=2') -eq '0') 'No invalid saved entries'
 $null=Request 'lanh_dao/xu_ly_cai_dat_dang_ky.php' $ld (Settings 5)
 Record 'Leadership can reduce limit without deleting entries' ((Sql "SELECT value FROM cai_dat WHERE key_name='gioi_han_de_tai_quan_tam'") -eq '5' -and (Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1') -eq '6') 'Existing six retained'
 $null=AddTopic $sv '9'
 Record 'Reduced limit blocks additional saves' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1') -eq '6') 'Still six'
 foreach($limit in @('0','51','1.5')){$null=Request 'lanh_dao/xu_ly_cai_dat_dang_ky.php' $ld (Settings $limit)}
 Record 'Invalid limits do not change settings' ((Sql "SELECT value FROM cai_dat WHERE key_name='gioi_han_de_tai_quan_tam'") -eq '5') 'Reject 0, 51 and non-integer'
 $null=Request 'lanh_dao/xu_ly_cai_dat_dang_ky.php' $sv (Settings 20)
 Record 'Student cannot change followed limit' ((Sql "SELECT value FROM cai_dat WHERE key_name='gioi_han_de_tai_quan_tam'") -eq '5') 'Leadership only'
 $invalid=Settings 20 '1' '2100-02-31T00:00:00' '2000-01-01T00:00:00'
 $null=Request 'lanh_dao/xu_ly_cai_dat_dang_ky.php' $ld $invalid
 Record 'Invalid dates roll back all configuration changes' ((Sql "SELECT value FROM cai_dat WHERE key_name='gioi_han_de_tai_quan_tam'") -eq '5') 'Limit unchanged with invalid date range'
 $null=Request 'lanh_dao/xu_ly_cai_dat_dang_ky.php' $ld (Settings 6)
 $null=Sql "UPDATE de_tai SET trang_thai='nhap',tieu_de='PRIVATE DRAFT CONTENT' WHERE id=6"
 $r=Request 'sinh_vien/de_tai_quan_tam.php' $sv
 Record 'Hidden saved topic shows unavailable without draft content' ($r.Content.Contains('Đề tài hiện không khả dụng') -and !$r.Content.Contains('PRIVATE DRAFT CONTENT')) 'Uses title snapshot'
 $null=Sql 'DELETE FROM de_tai WHERE id=6'
 $r=Request 'sinh_vien/de_tai_quan_tam.php' $sv
 Record 'Deleted topic remains removable as unavailable' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id IS NULL') -eq '1' -and [regex]::Matches($r.Content,'data-saved-topic=').Count -eq 6) 'ON DELETE SET NULL'
 $missing=Sql 'SELECT id FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id IS NULL'
 $null=Request 'sinh_vien/xu_ly_quan_tam.php' $sv @{action='remove';quan_tam_id=$missing;return_to='followed'}
 $null=AddTopic $sv '9'
 Record 'Removing unavailable entry frees a followed slot' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=9') -eq '1') 'Can add after removal'
 Record 'Following never reserves registration slots' ((Sql "SELECT COUNT(*) FROM dang_ky_de_tai WHERE trang_thai='da_duyet'") -eq '0' -and (Sql 'SELECT SUM(so_luong_da_dang_ky) FROM de_tai') -eq '0') 'No registrations or counters added'
 # Both students may follow a topic, but only an actual registration wins the seat.
 $null=AddTopic $sv2 '1'
 $null=Request 'sinh_vien/dang_ky_de_tai.php' $sv2 @{id='1'}
 $r=Request 'sinh_vien/de_tai_quan_tam.php' $sv
 Record 'Saved topic reports another student has registered' ($r.Content.Contains('Đã có sinh viên đăng ký')) 'One seat: another successful registration makes the topic unavailable'
 $catalog=Request 'sinh_vien/danh_sach_de_tai.php' $sv
 Record 'Catalog hides a topic filled by another student' (!$catalog.Content.Contains('chi_tiet_de_tai.php?id=1"') -and $r.Content.Contains('Đã có sinh viên đăng ký')) 'Old catalog filtering restored; saved list keeps its status'
 $null=Request 'sinh_vien/dang_ky_de_tai.php' $sv @{id='1';return_to='followed'}
 Record 'Following does not bypass full-topic validation' ((Sql "SELECT COUNT(*) FROM dang_ky_de_tai WHERE de_tai_id=1 AND trang_thai='da_duyet'") -eq '1') 'Only the first student registered'
 $null=Request 'lanh_dao/xu_ly_cai_dat_dang_ky.php' $ld (Settings 6 '1' '2000-01-01T00:00:00' '2001-01-01T00:00:00')
 $null=Request 'sinh_vien/dang_ky_de_tai.php' $sv @{id='2';return_to='followed'}
 Record 'Direct POST cannot register after closing' ((Sql 'SELECT COUNT(*) FROM dang_ky_de_tai WHERE sinh_vien_id=1 AND de_tai_id=2') -eq '0') 'Expired schedule enforced at server'
 $null=Request 'lanh_dao/xu_ly_cai_dat_dang_ky.php' $ld (Settings 6 '0')
 $null=Request 'sinh_vien/dang_ky_de_tai.php' $sv @{id='2'}
 Record 'Global pause blocks registration' ((Sql 'SELECT COUNT(*) FROM dang_ky_de_tai WHERE sinh_vien_id=1 AND de_tai_id=2') -eq '0') 'Pause takes priority'
 $null=AddTopic $sv2 '10'
 Record 'Global registration pause still permits following' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=2 AND de_tai_id=10') -eq '1') 'Independent from registration permission'
 $null=Request 'lanh_dao/xu_ly_cai_dat_dang_ky.php' $ld (Settings 6)
 $null=Request 'sinh_vien/dang_ky_de_tai.php' $sv @{id='2';return_to='followed'}
 Record 'Quick registration approves immediately in valid window' ((Sql "SELECT trang_thai FROM dang_ky_de_tai WHERE sinh_vien_id=1 AND de_tai_id=2") -eq 'da_duyet') 'Confirmed business rule'
 $r=Request 'sinh_vien/de_tai_quan_tam.php' $sv
 Record 'Saved item reflects own successful registration' ($r.Content.Contains('Đã đăng ký')) 'Entry remains saved; no auto-removal'
 $saved=Sql 'SELECT id FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=2'
 $r=Request 'sinh_vien/chi_tiet_de_tai.php?id=2' $sv
 Record 'Previously saved registered topic still allows removal' ($r.Content -match 'Bấm để bỏ quan tâm' -and (Sql "SELECT COUNT(*) FROM de_tai_quan_tam WHERE id=$saved") -eq '1') 'Existing saved entry retained'
 $null=Request 'sinh_vien/xu_ly_quan_tam.php' $sv @{action='remove';quan_tam_id=$saved;return_to='followed'}
 $r=Request 'sinh_vien/chi_tiet_de_tai.php?id=2' $sv
 Record 'Registered unsaved topic disables follow button with type tooltip' ($r.Content -match 'disabled data-registered-follow-disabled="true"' -and $r.Content.Contains('title="Bạn đã đăng ký đề tài Chuyên ngành."')) 'No extra explanation paragraph'
 $r=Request 'sinh_vien/danh_sach_de_tai.php?loai=chuyen_nganh' $sv
 Record 'Catalog hides own registered topic after last seat is taken' (!$r.Content.Contains('chi_tiet_de_tai.php?id=2"') -and $r.Content.Contains('chi_tiet_de_tai.php?id=8"')) 'Matches pre-maintenance behavior for one-student topics'
 Record 'Catalog omits newly introduced explanation paragraphs' (!$r.Content.Contains('Mỗi sinh viên được đăng ký một đề tài được duyệt cho mỗi hệ') -and !$r.Content.Contains('Đề tài đã được xác nhận cho bạn') -and !$r.Content.Contains('không cần thêm vào danh sách quan tâm')) 'Registration controls remain compact'
 $r=AddTopic $sv '2'
 Record 'Direct POST cannot follow an already registered topic' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=2') -eq '0' -and $r.Content.Contains('Bạn đã đăng ký đề tài Chuyên ngành.')) 'Server rechecks registered training type inside transaction'
 $r=AddTopic $sv '10'
 Record 'Direct POST cannot follow another topic of registered type' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=10') -eq '0' -and $r.Content.Contains('Bạn đã đăng ký đề tài Chuyên ngành.')) 'Restriction applies to every topic of the registered type'
 $r=Request 'sinh_vien/chi_tiet_de_tai.php?id=10' $sv
 Record 'Another topic of registered type has disabled follow button' ($r.Content -match 'disabled data-registered-follow-disabled="true"' -and $r.Content.Contains('title="Bạn đã đăng ký đề tài Chuyên ngành."')) 'Detail and catalog share the same component'
 $eight=Sql 'SELECT id FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=8'
 $null=Request 'sinh_vien/dang_ky_de_tai.php' $sv2 @{id='8'}
 $r=Request 'sinh_vien/de_tai_quan_tam.php' $sv
 $article=[regex]::Match($r.Content,('(?s)<article[^>]*data-saved-topic="'+$eight+'".*?</article>')).Value
 Record 'Taken topic status takes precedence over same-type restriction' ($article.Contains('Đã có sinh viên đăng ký') -and !$article.Contains('Bạn đã có đề tài cùng hệ')) 'Other student took the one seat; saved row retained'
 $null=Request 'sinh_vien/xu_ly_quan_tam.php' $sv @{action='remove';quan_tam_id=$eight;return_to='followed'}
 Record 'Previously saved topic of registered type can still be removed' ((Sql "SELECT COUNT(*) FROM de_tai_quan_tam WHERE id=$eight") -eq '0') 'Removal stays enabled'
 $null=AddTopic $sv '8'
 Record 'Removed same-type topic cannot be added again after registration' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=8') -eq '0') 'No re-add bypass'
 $null=Sql 'DELETE FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=9'
 $null=AddTopic $sv '9'
 Record 'Topics of the unregistered type can still be followed' ((Sql 'SELECT COUNT(*) FROM de_tai_quan_tam WHERE sinh_vien_id=1 AND de_tai_id=9') -eq '1') 'CSN still open for following after CN registration'
}
try{RunFollowed}finally{$results|ConvertTo-Json -Depth 5|Set-Content storage/baseline/followed-http-results.json -Encoding UTF8}
