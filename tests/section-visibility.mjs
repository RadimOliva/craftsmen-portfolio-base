import fs from 'node:fs';
import assert from 'node:assert/strict';
// Local-only form round trip; restore original visible settings and remove our upload.
const base='http://127.0.0.1:8080', cookies=new Map();
async function request(url,options={}) {
 const r=await fetch(base+url,{...options,headers:{Cookie:[...cookies].map(([k,v])=>`${k}=${v}`).join('; ')},redirect:'manual'});
 for(const c of r.headers.getSetCookie()){const pair=c.split(';')[0],i=pair.indexOf('=');cookies.set(pair.slice(0,i),pair.slice(i+1));}return r;
}
const decode=s=>s.replace(/&quot;/g,'"').replace(/&#0?39;/g,"'").replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&amp;/g,'&');
function fields(html) {
 const result={};
 for(const [tag] of html.matchAll(/<input\b[^>]*>/g)) {
  const name=tag.match(/name=["']([^"']+)["']/)?.[1];if(!name||/type="file"/.test(tag)||(/type="checkbox"/.test(tag)&&!tag.includes('checked')))continue;
  result[name]=decode(tag.match(/value=["']([^"']*)["']/)?.[1]||'');
 }
 for(const [,name,value] of html.matchAll(/<textarea[^>]*name="([^"]+)"[^>]*>([\s\S]*?)<\/textarea>/g))result[name]=decode(value);
 for(const [,name,options] of html.matchAll(/<select[^>]*name="([^"]+)"[^>]*>([\s\S]*?)<\/select>/g))result[name]=options.match(/<option value="([^"]*)" selected/)?.[1]||'';
 return result;
}
let html=await (await request('/sprava/')).text();
const password=fs.readFileSync('storage/LOCAL_ACCESS.md','utf8').match(/Client: klient\r?\nPassword: (\S+)/)[1];
await request('/sprava/',{method:'POST',body:new URLSearchParams({...fields(html),login_name:'klient',login_pass:password,login_submit:'Přihlásit'})});
const settingsUrl='/sprava/nastaveni/';
html=await (await request(settingsUrl)).text();
assert(!html.includes('name="services_enabled"')&&!html.includes('name="references_enabled"'));
const sections=[['sluzby','services_enabled','sluzby'],['reference','references_enabled','realizace']];
const originals={};
for(const [route,key] of sections){const f=fields(await (await request(`/sprava/${route}/`)).text());originals[key]=f[key]==='1';}
async function save(route,key,on){
 const f=fields(await (await request(`/sprava/${route}/`)).text());delete f.save_labels;delete f[key];if(on)f[key]='1';f.visibility_autosave='1';
 const r=await request(`/sprava/${route}/`,{method:'POST',body:new URLSearchParams(f)});assert.equal(r.status,200);assert.deepEqual(await r.json(),{saved:true,enabled:on});
}
async function check(key,id,on){
 const page=await (await fetch(base)).text();assert.equal(page.includes(`id="${id}"`),on);assert.equal(page.includes(`href="/#${id}"`),on);
 const route=sections.find(s=>s[1]===key)[0];assert.equal(fields(await (await request(`/sprava/${route}/`)).text())[key]==='1',on);
}
try{
 for(const [route,key,id] of sections){
  for(const on of [false,true]){await save(route,key,on);await check(key,id,on);}
  console.log(`PASS ${route}: toggle persistence and public section/navigation visibility`);
 }
 for(const [route,key] of sections)await save(route,key,false);
 html=await (await request(settingsUrl)).text();
 assert.equal((await request(settingsUrl,{method:'POST',body:new URLSearchParams(fields(html))})).status,303);
 for(const [,key,id] of sections)await check(key,id,false);
 console.log('PASS saving Nastavení preserves visibility controlled by collection screens');
}finally{
 for(const [route,key,id] of sections){await save(route,key,originals[key]);await check(key,id,originals[key]);}
 console.log('PASS original visibility restored');
}