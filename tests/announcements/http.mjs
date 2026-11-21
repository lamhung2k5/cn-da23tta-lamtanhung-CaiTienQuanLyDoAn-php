import assert from 'node:assert/strict';
import fs from 'node:fs';
const base = 'http://127.0.0.1:8091/WebsiteQuanLyDeTai/';
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

const ids=JSON.parse(fs.readFileSync('storage/baseline/announcement-fixture.json','utf8'));
check((await request('tests/announcements/environment.php')).text === 'qldt_announcement_test','Isolated test database');
let r=await request('index.php');
check(r.status === 200 && r.text.includes('PUBLIC_ACTIVE') && r.text.includes('PUBLIC_TODAY'),'Homepage shows active and same-day announcements');
check(!['PUBLIC_HIDDEN','PUBLIC_SCHEDULED','PUBLIC_EXPIRED','PRIVATE_SENTINEL'].some(s=>r.text.includes(s)),'Homepage excludes hidden future expired and private content');
check(r.text.includes('chi_tiet_thong_bao.php?id=1'),'Homepage links to full public detail');
for(const key of ['hidden','scheduled','expired','private']) {
  check((await request(`chi_tiet_thong_bao.php?id=${ids[key]}`)).status===404,`${key} detail blocked`);
}
r=await request('chi_tiet_thong_bao.php?id=1');
check(r.status===200 && r.text.includes('&lt;script&gt;') && !r.text.includes('<script>alert(1)</script>') && r.text.includes('Second line'),'Full content escaped and readable without login');
check((await request(`chi_tiet_thong_bao.php?id=${ids.hidden}&preview=1`)).status===404,'Anonymous cannot preview hidden announcement');
const sv=await login('sv1@example.test'), gv=await login('gv1@example.test'), ld=await login('ld1@example.test');
check((await request(`chi_tiet_thong_bao.php?id=${ids.hidden}&preview=1`,ld)).status===200,'Leadership can preview hidden public announcement');
check((await request(`chi_tiet_thong_bao.php?id=${ids.private}&preview=1`,ld)).status===404,'Preview cannot read personal notification');
check((await request(`chi_tiet_thong_bao.php?id=${ids.hidden}&preview=1`,sv)).status===404,'Student cannot preview hidden content');
check((await request('lanh_dao/quan_ly_thong_bao.php',gv)).url.endsWith('/giang_vien/dashboard.php'),'Lecturer cannot manage public announcements');
await request('lanh_dao/quan_ly_thong_bao.php',ld);
const createdTitle='CREATED_HTTP_'+Date.now();
const draft={action:'add',tieu_de:createdTitle,noi_dung:'HTTP content',loai:'info',trang_thai:'khoa'};
check((await request('lanh_dao/quan_ly_thong_bao.php',ld,draft,false)).status===403,'Announcement write requires CSRF');
await request('lanh_dao/quan_ly_thong_bao.php',ld,draft);
r=await request('lanh_dao/quan_ly_thong_bao.php?search='+createdTitle+'&state=hidden',ld);
check(r.text.includes(createdTitle) && r.text.includes('Tìm thấy 1 thông báo'),'Create and management filters work');
const createdId=r.text.match(/data-id="(\d+)"/)?.[1];
assert.ok(createdId);
await request('lanh_dao/quan_ly_thong_bao.php',ld,{action:'delete',id:createdId});
check((await request('lanh_dao/quan_ly_thong_bao.php?search='+createdTitle,ld)).text.includes('Tìm thấy 0 thông báo'),'Delete public announcement');
r=await request('lanh_dao/quan_ly_thong_bao.php?search=PUBLIC_ACTIVE&state=hidden',ld);
check(r.text.includes('Tìm thấy 0 thông báo'),'Combined search and state filter');
r=await request('lanh_dao/quan_ly_thong_bao.php?page=2',ld);
check(r.status===200 && r.text.includes('Phân trang thông báo'),'Announcements beyond first page remain accessible');
const active={...draft,action:'update',id:'1',tieu_de:'PUBLIC_ACTIVE',noi_dung:'Updated full public content',trang_thai:'mo'};
await request('lanh_dao/quan_ly_thong_bao.php',ld,{...active,ngay_bat_dau:'2030-01-02',ngay_ket_thuc:'2030-01-01'});
check((await request('chi_tiet_thong_bao.php?id=1')).status===200,'Reversed dates leave announcement unchanged');
await request('lanh_dao/quan_ly_thong_bao.php',ld,{...active,id:String(ids.private)});
check((await request(`chi_tiet_thong_bao.php?id=${ids.private}&preview=1`,ld)).status===404,'Forged update cannot turn personal notification public');
await request('lanh_dao/quan_ly_thong_bao.php',ld,{action:'toggle_status',id:'1'});
check((await request('chi_tiet_thong_bao.php?id=1')).status===404 && !(await request('index.php')).text.includes('PUBLIC_ACTIVE'),'Hide affects homepage and direct detail');
await request('lanh_dao/quan_ly_thong_bao.php',ld,{action:'toggle_status',id:'1'});
check((await request('chi_tiet_thong_bao.php?id=1')).status===200,'Publish restores detail');
r=await request('thong_bao_do_an.php');
check(r.status===200 && !r.text.includes('<form') && r.text.includes('Lịch đăng ký đề tài của hệ thống'),'Thesis public page is read-only with authoritative registration schedule');
const page={action:'update_page',page_title:'THESIS_ACTIVE',subtitle:'Thesis notice',date_badge:'2026-2027',trang_thai:'mo'};
await request('lanh_dao/quan_ly_noi_dung_do_an.php',ld,{...page,page_title:'SHOULD_ROLL_BACK',notice_tieu_de:'',notice_noi_dung:'Body requires title'});
r=await request('thong_bao_do_an.php');
check(r.text.includes('THESIS_ACTIVE') && !r.text.includes('SHOULD_ROLL_BACK'),'Page and notice edits roll back atomically on validation failure');
await request('lanh_dao/quan_ly_noi_dung_do_an.php',ld,{...page,notice_tieu_de:'NOTICE_LATEST',notice_noi_dung:'Latest content',notice_trang_thai:'khoa'});
check(!(await request('thong_bao_do_an.php')).text.includes('NOTICE_LATEST'),'Notice can be hidden independently');
await request('lanh_dao/quan_ly_noi_dung_do_an.php',ld,page);
check(!(await request('thong_bao_do_an.php')).text.includes('NOTICE_LATEST'),'Saving page does not reopen hidden notice');
await request('lanh_dao/quan_ly_noi_dung_do_an.php',ld,{...page,trang_thai:'khoa'});
r=await request('thong_bao_do_an.php');
check(!r.text.includes('THESIS_ACTIVE') && !r.text.includes('THESIS_MILESTONE'),'Hidden thesis page does not expose content');
await request('lanh_dao/quan_ly_noi_dung_do_an.php',ld,page);
check((await request('thong_bao_do_an.php',ld,{action:'save_page'})).status===405,'Public page rejects inline writes');
check((await request('lanh_dao/sua_ngay_thong_bao.php',ld,{action:'set_today',id:'1'})).status===405,'Old date editor cannot mutate announcements');
const schedule={gioi_han_de_tai_quan_tam:'6',cho_phep_dang_ky:'0',dang_ky_csn_bat_dau:'2031-02-03T08:00:00',dang_ky_csn_ket_thuc:'2031-02-04T17:00:00',dang_ky_cn_bat_dau:'2031-03-03T08:00:00',dang_ky_cn_ket_thuc:'2031-03-04T17:00:00'};
await request('lanh_dao/xu_ly_cai_dat_dang_ky.php',ld,schedule);
r=await request('thong_bao_do_an.php');
check(r.text.includes('03/02/2031') && r.text.includes('Đăng ký đang tạm đóng'),'Public schedule follows configured dates and manual pause');
console.log(`${count} HTTP checks passed.`);
