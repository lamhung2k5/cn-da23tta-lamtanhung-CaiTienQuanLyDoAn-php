import fs from 'node:fs';
const targets=await(await fetch('http://127.0.0.1:9222/json/list')).json();
const socket=new WebSocket(targets.find(target=>target.type==='page').webSocketDebuggerUrl);
await new Promise(resolve=>socket.addEventListener('open',resolve,{once:true}));
let sequence=0;
const pending=new Map(),exceptions=[],requests=[],results=[];
socket.addEventListener('message',event=>{
 const data=JSON.parse(event.data);
 if(data.id){const item=pending.get(data.id);pending.delete(data.id);data.error?item.reject(data.error):item.resolve(data.result);}
 if(data.method==='Runtime.exceptionThrown')exceptions.push(data.params.exceptionDetails.exception?.description||data.params.exceptionDetails.text);
 if(data.method==='Network.requestWillBeSent')requests.push({url:data.params.request.url,method:data.params.request.method});
});
function send(method,params={}){return new Promise((resolve,reject)=>{const id=++sequence;pending.set(id,{resolve,reject});socket.send(JSON.stringify({id,method,params}));});}
const delay=ms=>new Promise(resolve=>setTimeout(resolve,ms));
async function evaluate(expression){const r=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.exception?.description||r.exceptionDetails.text);return r.result.value;}
async function waitFor(expression){for(let i=0;i<80;i++){await delay(100);try{if(await evaluate(expression))return;}catch{}}throw new Error('Condition timed out: '+expression);}
const base='http://127.0.0.1:8089/WebsiteQuanLyDeTai/';
async function navigate(path){await send('Page.navigate',{url:base+path});await delay(100);await waitFor('document.readyState === "complete"');}
async function login(email){await send('Network.clearBrowserCookies');await navigate('auth/login.php');await evaluate(`document.querySelector('[name=email]').value=${JSON.stringify(email)};document.querySelector('[name=password]').value='BaselineDemo!2026';document.querySelector('form').requestSubmit()`);await waitFor('location.pathname.endsWith("/dashboard.php")');}
async function configureSchedule(start,end){
 await navigate('lanh_dao/cai_dat_thong_so.php');
 await evaluate(`document.querySelector('#tab-dang-ky').click()`);
 await waitFor('document.querySelector("#content-dang-ky").classList.contains("active")');
 await evaluate(`for(const type of ['csn','cn']){document.querySelector('[name=dang_ky_'+type+'_bat_dau]').value=${JSON.stringify(start)};document.querySelector('[name=dang_ky_'+type+'_ket_thuc]').value=${JSON.stringify(end)};}document.querySelector('#gioi-han-quan-tam').value='6';document.querySelector('#cho-phep-dang-ky').value='1';document.querySelector('#content-dang-ky form').requestSubmit()`);
 await waitFor('location.hash === "#tab-dang-ky" && document.readyState === "complete"');
 await delay(200);
}
function check(name,passed,detail=''){results.push({Check:name,Passed:!!passed,Detail:detail});console.log(name+': '+passed+(detail?' '+detail:''));}
await send('Page.enable');await send('Runtime.enable');await send('Network.enable');
await send('Emulation.setDeviceMetricsOverride',{width:1280,height:900,deviceScaleFactor:1,mobile:false});
try{
 await login('ld1@example.test');
 await configureSchedule('2100-01-01T00:00:00','2100-12-31T23:59:59');
 check('Leadership registration tab opens and saves',await evaluate('document.querySelector("#content-dang-ky").classList.contains("active")'));
 await login('sv1@example.test');
 await navigate('sinh_vien/danh_sach_de_tai.php');
 check('Sidebar shows followed count and configured limit',await evaluate('document.querySelector("[data-quan-tam-count]").textContent.trim()==="0/6"'));
 await evaluate(`document.querySelector('form.quan-tam-form input[name=de_tai_id][value="5"]').closest('form').querySelector('button').click()`);
 await waitFor(`document.querySelector('form.quan-tam-form input[name=de_tai_id][value="5"]')?.closest('form').querySelector('button').getAttribute('aria-pressed')==='true'`);
 check('Topic card toggles to saved via POST',requests.some(r=>r.url.endsWith('/sinh_vien/xu_ly_quan_tam.php')&&r.method==='POST'));
 await navigate('sinh_vien/de_tai_quan_tam.php');
 check('Saved page shows topic and blocks pre-opening registration',await evaluate(`document.querySelectorAll('[data-saved-topic]').length===1 && document.body.textContent.includes('Chưa mở đăng ký') && !document.querySelector('[data-register-topic]')`));
 await send('Page.reload');await delay(150);await waitFor('document.readyState === "complete"');
 check('Saved state survives page reload',await evaluate('document.querySelectorAll("[data-saved-topic]").length===1'));
 const screenshot=await send('Page.captureScreenshot',{format:'png'});
 fs.writeFileSync('storage/baseline/followed-desktop.png',Buffer.from(screenshot.data,'base64'));
 await send('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
 await navigate('sinh_vien/de_tai_quan_tam.php');
 check('Saved page fits mobile viewport',await evaluate('document.documentElement.scrollWidth<=window.innerWidth+1'));
 const mobile=await send('Page.captureScreenshot',{format:'png'});
 fs.writeFileSync('storage/baseline/followed-mobile.png',Buffer.from(mobile.data,'base64'));
 await send('Emulation.setDeviceMetricsOverride',{width:1280,height:900,deviceScaleFactor:1,mobile:false});
 await login('ld1@example.test');await configureSchedule('2000-01-01T00:00:00','2100-01-01T00:00:00');
 await login('sv1@example.test');await navigate('sinh_vien/de_tai_quan_tam.php');
 check('Quick registration appears when schedule opens',await evaluate(`!!document.querySelector('[data-register-topic="5"]')`));
 await evaluate(`document.querySelector('[data-register-topic="5"]').click()`);
 await waitFor('location.pathname.endsWith("/de_tai_cua_toi.php")');
 check('Quick registration submits POST successfully',requests.some(r=>r.url.endsWith('/sinh_vien/dang_ky_de_tai.php')&&r.method==='POST'));
 await navigate('sinh_vien/de_tai_quan_tam.php');
 check('Registered saved item updates its state',await evaluate('document.body.textContent.includes("Đã đăng ký") && !document.querySelector("[data-register-topic]")'));
 await evaluate(`Array.from(document.forms).find(form=>form.querySelector('[name=action][value=remove]')).querySelector('button').click()`);
 await waitFor('document.body.textContent.includes("Bạn chưa quan tâm đề tài nào")');
 check('Removing updates empty state and sidebar',await evaluate('document.querySelector("[data-quan-tam-count]").textContent.trim()==="0/6"'));
 await navigate('sinh_vien/chi_tiet_de_tai.php?id=5');
 check('Registered topic follow button is disabled with type tooltip',await evaluate(`document.querySelector('form.quan-tam-form button').disabled && document.querySelector('form.quan-tam-form button').title==='Bạn đã đăng ký đề tài Cơ sở ngành.' && !document.body.textContent.includes('không cần thêm vào danh sách quan tâm')`));
 await navigate('sinh_vien/danh_sach_de_tai.php');
 check('Catalog removes the registered one-student topic',await evaluate(`!document.querySelector('a[href="chi_tiet_de_tai.php?id=5"]') && !document.body.textContent.includes('Đề tài đã được xác nhận cho bạn')`));
 check('Other topics of registered type have disabled follow buttons',await evaluate(`document.querySelector('form.quan-tam-form input[name=de_tai_id][value="7"]').closest('form').querySelector('button').disabled`));
 await navigate('sinh_vien/danh_sach_de_tai.php?loai=chuyen_nganh');
 check('Unregistered type still permits following',await evaluate(`!document.querySelector('form.quan-tam-form input[name=de_tai_id][value="2"]').closest('form').querySelector('button').disabled`));
 check('No JavaScript exceptions in followed flow',exceptions.length===0,exceptions.join(' | '));
}finally{
 fs.writeFileSync('storage/baseline/followed-browser-results.json',JSON.stringify(results,null,2));socket.close();
}
