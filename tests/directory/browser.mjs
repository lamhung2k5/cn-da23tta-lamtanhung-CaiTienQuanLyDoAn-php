import fs from 'node:fs';
import assert from 'node:assert/strict';
const targets=await (await fetch('http://127.0.0.1:9223/json/list')).json();
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
async function navigate(path){await send('Page.navigate',{url:'http://127.0.0.1:8090/WebsiteQuanLyDeTai/'+path});await delay(150);await waitFor('document.readyState === "complete"');}
function check(condition,label){assert.ok(condition,label);checks++;console.log('PASS: '+label);}
async function screenshot(name){const r=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync('storage/baseline/directory-'+name+'.png',Buffer.from(r.data,'base64'));}
try {
  await send('Runtime.enable');await send('Page.enable');
  await send('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
  await navigate('khoa_cntt.php');
  check(await evaluate('document.querySelectorAll(".faculty-item").length === 1 && document.querySelector("#memberCount").textContent === "1"'),'Directory renders database count');
  await evaluate('document.querySelector("#facultyGrid").scrollIntoView()');await screenshot('desktop');
  await evaluate('document.querySelectorAll(".filter-tab")[1].click()');
  check(await evaluate('document.querySelector("#memberCount").textContent === "0" && document.querySelector("#noResults").style.display === "block"'),'Position filter uses database value');
  await evaluate('document.querySelectorAll(".filter-tab")[0].click()');
  check(await evaluate('document.querySelector("#memberCount").textContent === "1"'),'Reset position filter');
  await send('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
  await evaluate('document.querySelector("#facultyGrid").scrollIntoView()');await screenshot('mobile');
  await navigate('giang_vien_chi_tiet_v2.php?id=1');
  check(await evaluate('document.querySelectorAll(".topic-card").length === 3'),'Only approved topics rendered');
  await navigate('giang_vien_chi_tiet_v2.php?id=1&he_dao_tao=chuyen_nganh');
  check(await evaluate('document.querySelectorAll(".topic-card").length === 1'),'Public topic type filter');
  await send('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
  await navigate('auth/login.php');
  await evaluate(`document.querySelector('[name=email]').value='ld1@example.test';document.querySelector('[name=password]').value='BaselineDemo!2026';document.querySelector('form').requestSubmit()`);
  await waitFor('location.pathname.endsWith("/dashboard.php") && document.readyState === "complete"');
  await navigate('lanh_dao/gioi_thieu_giang_vien.php?id=1');
  check(await evaluate('document.querySelector("#public-visible").checked && document.querySelector("[name=so_noi_bo]").value === "456"'),'Leadership edit form reflects saved data');
  await screenshot('leadership');
  if(errors.length) console.log(errors.join('\n'));
  check(errors.length===0,'No browser JavaScript exceptions');
  console.log(`${checks} browser checks passed.`);
} finally {socket.close();}
