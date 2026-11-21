import fs from 'node:fs';
import assert from 'node:assert/strict';
const targets=await (await fetch('http://127.0.0.1:9224/json/list')).json();
const socket=new WebSocket(targets.find(t=>t.type==='page').webSocketDebuggerUrl);
await new Promise(resolve=>socket.addEventListener('open',resolve,{once:true}));
let sequence=0, checks=0; const pending=new Map(), errors=[];
socket.addEventListener('message',event=>{
  const data=JSON.parse(event.data);
  if(data.id){const p=pending.get(data.id);pending.delete(data.id);data.error?p.reject(data.error):p.resolve(data.result);}
  if(data.method==='Runtime.exceptionThrown')errors.push(data.params.exceptionDetails.exception?.description || data.params.exceptionDetails.text);
});
function send(method,params={}) {return new Promise((resolve,reject)=>{const id=++sequence;pending.set(id,{resolve,reject});socket.send(JSON.stringify({id,method,params}));});}
const delay=ms=>new Promise(resolve=>setTimeout(resolve,ms));
async function evaluate(expression){const r=await send('Runtime.evaluate',{expression,returnByValue:true}); if(r.exceptionDetails)throw Error(r.exceptionDetails.text);return r.result.value;}
async function waitFor(expression){for(let i=0;i<150;i++){await delay(100);if(await evaluate(expression))return;}throw Error('Timed out: '+expression);}
async function navigate(path){await send('Page.navigate',{url:'http://127.0.0.1:8091/WebsiteQuanLyDeTai/'+path});await delay(150);await waitFor('document.readyState === "complete"');}
function check(condition,label){assert.ok(condition,label);checks++;console.log('PASS: '+label);}
async function screenshot(name){const r=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync('storage/baseline/announcement-'+name+'.png',Buffer.from(r.data,'base64'));}

try {
  await send('Runtime.enable');await send('Page.enable');
  await send('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
  await navigate('index.php');
  check(await evaluate('document.querySelectorAll("a.notification-item-modern").length === 2'),'Homepage has two active clickable announcements');
  await screenshot('homepage-desktop');
  await send('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
  await navigate('chi_tiet_thong_bao.php?id=1');
  check(await evaluate('document.querySelector("article").textContent.includes("Second line") && document.querySelector("article script") === null'),'Mobile detail renders complete escaped content');
  check(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Mobile detail fits viewport');
  await screenshot('detail-mobile');
  await navigate('thong_bao_do_an.php');
  check(await evaluate('document.forms.length === 0 && document.body.textContent.includes("03/02/2031")'),'Public thesis page read-only with configured schedule');
  await send('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
  await navigate('auth/login.php');
  await evaluate(`document.querySelector('[name=email]').value='ld1@example.test';document.querySelector('[name=password]').value='BaselineDemo!2026';document.querySelector('form').requestSubmit()`);
  await waitFor('location.pathname.endsWith("/dashboard.php") && document.readyState === "complete"');
  await navigate('lanh_dao/quan_ly_thong_bao.php?search=PUBLIC_ACTIVE');
  check(await evaluate('document.querySelector("#notice-search").value === "PUBLIC_ACTIVE" && document.querySelector("a[href*=preview]") !== null'),'Management has filters and preview');
  await screenshot('management');
  await evaluate('document.querySelector(".btn-edit-timeline").click()');
  await waitFor('document.querySelector("#editNotificationModal").classList.contains("show")');
  check(await evaluate('document.querySelector("#edit_tieu_de").value === "PUBLIC_ACTIVE"'),'Existing edit modal populated');
  await evaluate(`document.querySelector('#edit_ngay_bat_dau').value='2030-01-02';document.querySelector('#edit_ngay_ket_thuc').value='2030-01-01';document.querySelector('#editNotificationModal form').requestSubmit()`);
  await delay(500);
  await waitFor('document.readyState === "complete" && document.querySelector("#editNotificationModal").classList.contains("show")');
  check(await evaluate('document.querySelector("#edit_tieu_de").value === "PUBLIC_ACTIVE" && document.querySelector("#edit_ngay_bat_dau").value === "2030-01-02"'),'Validation error preserves draft and reopens modal');
  await navigate('lanh_dao/quan_ly_noi_dung_do_an.php');
  check(await evaluate('document.querySelector("[name=notice_trang_thai]") !== null && document.body.textContent.includes("03/02/2031")'),'Thesis management exposes independent notice status and shared schedule');
  await navigate('thong_bao_do_an.php');
  check(await evaluate('Array.from(document.links).some(a=>a.getAttribute("href")==="lanh_dao/quan_ly_noi_dung_do_an.php") && document.forms.length === 0'),'Leadership edit link opens dedicated management');
  if(errors.length) console.log(errors.join('\n'));
  check(errors.length===0,'No browser JavaScript exceptions');
  console.log(`${checks} browser checks passed.`);
} finally {socket.close();}
