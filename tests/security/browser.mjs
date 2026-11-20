import fs from 'node:fs';
const targets = await (await fetch('http://127.0.0.1:9222/json/list')).json();
const target = targets.find(item => item.type === 'page');
const socket = new WebSocket(target.webSocketDebuggerUrl);
await new Promise(resolve => socket.addEventListener('open', resolve, {once:true}));
let sequence=0;
const pending=new Map();
const exceptions=[];
const requests=[];
socket.addEventListener('message', event => {
    const data=JSON.parse(event.data);
    if (data.id) { const item=pending.get(data.id); pending.delete(data.id); data.error ? item.reject(data.error) : item.resolve(data.result); }
    if (data.method==='Runtime.exceptionThrown') exceptions.push(JSON.stringify(data.params.exceptionDetails));
    if (data.method==='Network.requestWillBeSent') requests.push({url:data.params.request.url,method:data.params.request.method});
    if (data.method==='Page.javascriptDialogOpening') send('Page.handleJavaScriptDialog',{accept:true});
});
function send(method,params={}) { return new Promise((resolve,reject)=>{const id=++sequence;pending.set(id,{resolve,reject});socket.send(JSON.stringify({id,method,params}));}); }
const delay=ms=>new Promise(resolve=>setTimeout(resolve,ms));
async function evaluate(expression) {
    const response=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});
    if (response.exceptionDetails) throw new Error(response.exceptionDetails.exception?.description || response.exceptionDetails.text);
    return response.result.value;
}
const base='http://localhost:8088/WebsiteQuanLyDeTai/';
async function navigate(path) { await send('Page.navigate',{url:base+path}); for(let i=0;i<80;i++){await delay(100);if(await evaluate('document.readyState')==='complete')return;} throw new Error('Navigation timeout '+path); }
async function login(email,password) {
    await send('Network.clearBrowserCookies');
    await navigate('auth/login.php');
    await evaluate(`document.querySelector('[name=email]').value=${JSON.stringify(email)};document.querySelector('[name=password]').value=${JSON.stringify(password)};document.querySelector('form').requestSubmit()`);
    for(let i=0;i<60;i++){await delay(100);if((await evaluate('location.pathname')).endsWith('/dashboard.php'))return;}
    throw new Error('Login failed '+email);
}
await send('Page.enable');await send('Runtime.enable');await send('Network.enable');
const results=[];
function check(name,passed,detail=''){results.push({Check:name,Passed:!!passed,Detail:detail});console.log(name+': '+passed+(detail?' '+detail:''));}
try {
    await login('ld1@example.test','BaselineDemo!2026');
    await navigate('lanh_dao/quan_ly_nguoi_dung.php');
    check('Leadership account forms contain CSRF',await evaluate(`Array.from(document.querySelectorAll('form[method=POST],form[method=post]')).every(form=>!!form.querySelector('[name=csrf_token]'))`));
    check('Account security controls visible',await evaluate(`!!document.querySelector('#security-user') && !!document.querySelector('#security-current-password')`));
    for(const path of ['lanh_dao/duyet_de_tai.php','lanh_dao/de_tai_da_duyet.php','lanh_dao/danh_sach_phan_cong.php','lanh_dao/cau_hinh_menu.php']) await navigate(path);
    await login('gv1@example.test','BaselineDemo!2026');
    for(const path of ['giang_vien/dashboard.php','giang_vien/danh_sach_de_tai.php','giang_vien/duyet_sinh_vien.php','giang_vien/tao_de_tai.php?loai=co_so_nganh','edit-profile.php']) {
        await navigate(path);
        check('CSRF forms '+path,await evaluate(`Array.from(document.forms).filter(form=>form.method.toLowerCase()==='post').every(form=>!!form.querySelector('[name=csrf_token]'))`));
    }
    await login('sv2@example.test','BaselineDemo!2026');
    await navigate('sinh_vien/chi_tiet_de_tai.php?id=2');
    const registered=await evaluate(`(()=>{const anchor=document.querySelector('a[href*="dang_ky_de_tai.php?id=2"]');if(!anchor)return false;anchor.click();return true;})()`);
    await delay(1500);
    check('Student registration link submits POST',registered && requests.some(item=>item.url.endsWith('/sinh_vien/dang_ky_de_tai.php')&&item.method==='POST'));
    check('Registration returns to own topics',(await evaluate('location.pathname')).endsWith('/sinh_vien/de_tai_cua_toi.php'));
    await evaluate(`document.querySelector('a[href$="auth/logout.php"]').click()`);
    await delay(800);
    check('Logout link submits POST',requests.some(item=>item.url.endsWith('/auth/logout.php')&&item.method==='POST'));
    check('No JavaScript runtime exceptions',exceptions.length===0,exceptions.join(' | '));
} finally {
    fs.writeFileSync('storage/baseline/security-browser-results.json',JSON.stringify(results,null,2));
    socket.close();
}
