import assert from 'node:assert/strict';
const base = 'http://127.0.0.1:8090/WebsiteQuanLyDeTai/';
let count = 0;
function check(value, label) { assert.ok(value, label); ++count; console.log(`PASS: ${label}`); }
function session() { return {cookie:'', token:''}; }
async function request(path, s = session(), data, csrf = true) {
  let url = new URL(path, base); let method = data ? 'POST' : 'GET';
  for (let i=0; i<8; ++i) {
    const headers = {Cookie:s.cookie};
    if (data) { headers['Content-Type']='application/x-www-form-urlencoded'; if (csrf) headers['X-CSRF-Token']=s.token; }
    const r = await fetch(url, {method, headers, body:data ? new URLSearchParams(data) : undefined, redirect:'manual'});
    const cookies = r.headers.getSetCookie();
    for (const cookie of cookies) { if (cookie.startsWith('PHPSESSID=')) s.cookie=cookie.split(';')[0]; }
    if ([301,302,303].includes(r.status)) { url=new URL(r.headers.get('location'), url); method='GET'; data=undefined; continue; }
    const text=await r.text(); const token=text.match(/name="csrf-token" content="([^"]+)"/);
    if (token) s.token=token[1];
    return {status:r.status, text, url:String(url)};
  }
  throw Error('Redirect loop');
}
async function login(email) {
  const s=session(); await request('auth/login.php',s);
  const r=await request('auth/login.php',s,{email,password:'BaselineDemo!2026',csrf_token:s.token});
  assert.match(r.url,/dashboard\.php$/); return s;
}
check((await request('tests/directory/environment.php')).text === 'qldt_directory_test', 'Isolated database identity');
let r=await request('khoa_cntt.php');
check(r.status === 200 && r.text.includes('giang_vien_chi_tiet_v2.php?id=1') && !r.text.includes('giang_vien_chi_tiet_v2.php?id=2'), 'Public directory uses published database profiles');
check(r.text.includes('Faculty &lt;script&gt;') && !r.text.includes('Faculty <script>'), 'Public name escaped');
check(!r.text.includes('lamnn@tvu.edu.vn'), 'Hardcoded faculty list removed');
r=await request('giang_vien_chi_tiet_v2.php?id=1');
check(r.status === 200 && !r.text.includes('PRIVATE_DRAFT_SENTINEL'), 'Detail hides unapproved topics');
check((await request('giang_vien_chi_tiet_v2.php?id=2')).status === 404, 'Hidden profile returns 404');
check((await request('giang_vien_chi_tiet_v2.php?id=999999')).status === 404, 'Unknown profile returns 404');
check((await request('giang_vien_chi_tiet_v2.php?email=gv1%40example.test')).status === 200, 'Legacy email link resolves published database profile');
check((await request('giang_vien_chi_tiet_v2.php?email=gv2%40example.test')).status === 404, 'Legacy email link cannot bypass visibility');
check((await request('giang_vien_chi_tiet_v2.php?id[]=1')).status === 404, 'Malformed id rejected');
const anonymous=await request('lanh_dao/gioi_thieu_giang_vien.php');
check(anonymous.url.includes('auth/login.php'), 'Anonymous management requires login');
const sv=await login('sv1@example.test'); const gv=await login('gv1@example.test'); const ld=await login('ld1@example.test');
check((await request('lanh_dao/gioi_thieu_giang_vien.php',sv)).url.endsWith('/sinh_vien/dashboard.php'), 'Student redirected away from management');
check((await request('lanh_dao/gioi_thieu_giang_vien.php',gv)).url.endsWith('/giang_vien/dashboard.php'), 'Lecturer redirected away from management');
r=await request('lanh_dao/gioi_thieu_giang_vien.php?id=1',ld);
check(r.status === 200 && r.text.includes('public-visible'), 'Leadership edit form accessible');
const profile={giang_vien_id:'1',chuc_vu_cong_khai:'Giảng viên',so_noi_bo:'456',hoc_ham_hoc_vi:'TS.',khoa:'Demo khoa',chuyen_mon:'Web',so_dien_thoai:'0123456789'};
check((await request('lanh_dao/xu_ly_gioi_thieu_giang_vien.php',ld,profile,false)).status === 403, 'POST requires CSRF');
check((await request('lanh_dao/xu_ly_gioi_thieu_giang_vien.php',sv,profile)).url.endsWith('/sinh_vien/dashboard.php'), 'Student cannot POST profile changes');
check((await request('lanh_dao/xu_ly_gioi_thieu_giang_vien.php',ld)).status === 405, 'GET cannot mutate profile');
await request('lanh_dao/xu_ly_gioi_thieu_giang_vien.php',ld,profile);
check((await request('giang_vien_chi_tiet_v2.php?id=1')).status === 404, 'Unpublish immediately blocks direct detail');
await request('lanh_dao/xu_ly_gioi_thieu_giang_vien.php',ld,{...profile,hien_thi_cong_khai:'1'});
r=await request('giang_vien_chi_tiet_v2.php?id=1');
check(r.status === 200 && r.text.includes('456'), 'Republish and profile edit visible');
await request('lanh_dao/xu_ly_gioi_thieu_giang_vien.php',ld,{...profile,hien_thi_cong_khai:'1',chuc_vu_cong_khai:'Admin'});
check((await request('giang_vien_chi_tiet_v2.php?id=1')).status === 200, 'Invalid edit leaves published profile intact');
console.log(`${count} HTTP checks passed.`);
