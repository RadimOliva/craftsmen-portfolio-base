import fs from 'node:fs';
import assert from 'node:assert/strict';
const base='http://127.0.0.1:8080';let count=0;const testName='HTTP attachment test '+Date.now();
const check=(condition,label)=>{assert(condition,label);count++;console.log('PASS',label);};
function client(){const cookies=new Map();return async (url,options={})=>{const r=await fetch(base+url,{...options,headers:{Cookie:[...cookies].map(([k,v])=>`${k}=${v}`).join('; '),...(options.headers||{})},redirect:'manual'});for(const cookie of r.headers.getSetCookie()){const pair=cookie.split(';')[0],i=pair.indexOf('=');cookies.set(pair.slice(0,i),pair.slice(i+1));}return r;};}
const hidden=html=>Object.fromEntries([...html.matchAll(/<input\b[^>]*type=["']hidden["'][^>]*>/gi)].map(([tag])=>[tag.match(/\bname=["']([^"']+)/)?.[1],tag.match(/\bvalue=["']([^"']*)/)?.[1]||'']).filter(([name])=>name));
const web=client();let r=await web('/');let html=await r.text();check(r.status===200&&html.includes('Řemeslná výroba'),'Homepage renders');
const fields=hidden(html);check(fields.submission_token?.length===64,'Submission token present');check(Object.keys(fields).some(k=>k.startsWith('TOKEN')),'CSRF token present');
for(const url of ['/storage/local.php','/storage/LOCAL_ACCESS.md','/.runtime/php/php.ini','/tools/bootstrap.php','/composer.json','/site/config.php']){r=await web(url);check(r.status===404,'Private path denied: '+url);}
r=await web('/ochrana-osobnich-udaju/');check((await r.text()).includes('UKÁZKOVÝ TEXT'),'Privacy placeholder page');
r=await web('/robots.txt');check((await r.text()).includes('Disallow: /'),'Local robots prevents indexing');
r=await web('/sitemap.xml');html=await r.text();if(!html.includes('<urlset'))console.log('Sitemap response',r.status,r.headers.get('location'),html.slice(0,500));check(html.includes('<urlset'),'Sitemap available');
await new Promise(resolve=>setTimeout(resolve,2100));
const bad=new URLSearchParams({...fields,name:'HTTP test',phone:'abc',email:'invalid',address:'Praha',category:'Jiné',subject:'Test',message:'Keep this text'});
r=await web('/',{method:'POST',body:bad});html=await r.text();check(html.includes('Zadejte platnou e-mailovou adresu')&&html.includes('Keep this text'),'Validation retains valid text');
const noCsrf=new URLSearchParams({name:'Rejected'});r=await web('/',{method:'POST',body:noCsrf});check((await r.text()).includes('Platnost formuláře vypršela'),'Missing CSRF rejected');
const files=new FormData();for(const [k,v] of Object.entries({...fields,name:'HTTP test',phone:'+420777123999',email:'test@example.test',address:'Praha',category:'Jiné',subject:'HTTP upload test',message:'Disposable integration test'}))files.set(k,v);
files.append('files[]',new Blob(['<?php echo "invalid"; ?>'],{type:'image/jpeg'}),'fake.jpg');r=await web('/',{method:'POST',body:files});check((await r.text()).includes('Povoleny jsou pouze'),'Spoofed image rejected');
const validFiles=new FormData();for(const [k,v] of Object.entries({...fields,name:testName,phone:'+420777123999',email:'test@example.test',address:'Praha',category:'Jiné',subject:'HTTP upload test',message:'Disposable integration test'}))validFiles.set(k,v);
validFiles.append('files[]',new Blob([fs.readFileSync('site/templates/assets/images/craft.jpg')],{type:'image/jpeg'}),'sample.jpg');
r=await web('/',{method:'POST',body:validFiles});if(![302,303].includes(r.status)){html=await r.text();console.log('Upload response',r.status,r.headers.get('location'),html.match(/<div class="form-errors"[\s\S]*?<\/ul>/)?.[0]||html.slice(0,200));}check(r.status===302||r.status===303,'Valid enquiry with attachment saved');
r=await web('/',{method:'POST',body:validFiles});check(r.status===302||r.status===303,'Duplicate submission safely acknowledged');
const admin=client();r=await admin('/sprava/');html=await r.text();const credentials=fs.readFileSync('storage/LOCAL_ACCESS.md','utf8');const password=credentials.match(/Client: klient\r?\nPassword: (\S+)/)[1];
const login=new URLSearchParams({...hidden(html),login_name:'klient',login_pass:password,login_submit:'Přihlásit'});r=await admin('/sprava/',{method:'POST',body:login});
check([301,302,303].includes(r.status),'Client authentication succeeds');r=await admin('/sprava/prehled/');html=await r.text();check(html.includes('Nové poptávky')&&html.includes('Zobrazení webu'),'Client dashboard available');
for(const url of ['/sprava/setup/','/sprava/module/','/sprava/access/']){r=await admin(url);html=await r.text();const denied=r.status>=400||/nemáte|permission|přístup|oprávnění|not have/i.test(html)||([301,302,303].includes(r.status)&&r.headers.get('location')!==url);if(!denied)console.log('Restricted response',r.status,r.headers.get('location'),html.replace(/<[^>]*>/g,'').slice(-1200));check(!html.includes('ProcessModuleEdit')&&denied,'Client denied privileged route '+url);}
r=await admin('/sprava/poptavky/');html=await r.text();const links=[...html.matchAll(new RegExp('href="/sprava/poptavky/\\?id=(\\d+)">'+testName,'g'))];check(links.length===1,'Exactly one saved enquiry for repeated token');const id=links[0][1];
r=await admin(`/sprava/poptavky/?id=${id}`);html=await r.text();check(html.includes('sample.jpg'),'Private attachment visible to client');
r=await admin(`/sprava/poptavky/?id=${id}&download=0`);check(r.status===200&&r.headers.get('content-type')==='image/jpeg','Authenticated attachment download');
r=await web(`/sprava/poptavky/?id=${id}&download=0`);check(r.headers.get('content-type')!=='image/jpeg','Anonymous attachment download denied');
r=await admin(`/sprava/poptavky/?id=${id}`);html=await r.text();
r=await admin(`/sprava/poptavky/?id=${id}`,{method:'POST',body:new URLSearchParams({...hidden(html),status:'archived',expires:'2099-01-01',retention_reason:''})});
html=await r.text();check(html.includes('Pro prodlou')&&r.status===200,'Retention extension requires a reason');
r=await admin(`/sprava/poptavky/?id=${id}`);html=await r.text();
r=await admin(`/sprava/poptavky/?id=${id}`,{method:'POST',body:new URLSearchParams({...hidden(html),delete:'1'})});check(r.status===303,'Client can delete test enquiry');
r=await admin(`/sprava/poptavky/?id=${id}&download=0`);check(r.status===404,'Deleted attachment no longer downloadable');
// Edit a temporary reference through the actual client form, including multipart uploads and ordering.
r=await admin('/sprava/reference/?new=1');html=await r.text();
r=await admin('/sprava/reference/?new=1',{method:'POST',body:new URLSearchParams({...hidden(html),title:'Disposable gallery test',text:'Test',location:'Test',sort:'99',enabled:'1'})});
check(r.status===303,'Client can create a reference');const edit=r.headers.get('location');
try{
 r=await admin(edit);html=await r.text();const upload=new FormData();
 for(const [k,v] of Object.entries({...hidden(html),title:'Disposable gallery test',text:'Test',location:'Test',sort:'99',enabled:'1'}))upload.set(k,v);
 for(const name of ['craft','home'])upload.append('images[]',new Blob([fs.readFileSync(`site/templates/assets/images/${name}.jpg`)],{type:'image/jpeg'}),name+'.jpg');
 r=await admin(edit,{method:'POST',body:upload});check(r.status===303,'Client multi-image upload succeeds');
 r=await admin(edit);html=await r.text();const imageUrls=[...html.matchAll(/class="craft-image-row"><img src="([^"]+)"/g)].map(m=>m[1]);check(imageUrls.length===2,'Both gallery images persisted');
 r=await admin(edit,{method:'POST',body:new URLSearchParams({...hidden(html),title:'Disposable gallery test',text:'Test',location:'Test',sort:'99',enabled:'1','order[0]':'1','order[1]':'0','alt[0]':'First test image','alt[1]':'Second test image'})});check(r.status===303,'Image order can be saved');
 r=await admin(edit);html=await r.text();const reordered=[...html.matchAll(/class="craft-image-row"><img src="([^"]+)"/g)].map(m=>m[1]);check(reordered[0]===imageUrls[1]&&reordered[1]===imageUrls[0],'Image order persisted without deleting files');
 for(const url of reordered){r=await web(url);check(r.status===200&&r.headers.get('content-type')?.startsWith('image/'),'Reordered image remains available');}
}finally{
 r=await admin(edit);html=await r.text();await admin(edit,{method:'POST',body:new URLSearchParams({...hidden(html),delete:'1'})});
}
console.log(`${count} HTTP checks passed; temporary enquiries and gallery removed.`);
