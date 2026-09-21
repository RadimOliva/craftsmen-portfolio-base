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
let list=await (await request('/sprava/obsah-webu/')).text();
const contentLinks=[...list.matchAll(/href="(\?id=\d+)"><strong>([^<]+)<\/strong>/g)].map(m=>['/sprava/obsah-webu/'+m[1],m[2]]);
const targets=[['/sprava/sluzby/','services'],['/sprava/reference/','references'],['/sprava/kontakt/','contact'],...contentLinks];
for(const [edit,name] of targets){
 const read=async()=>fields(await (await request(edit)).text());const original=await read();assert('label_eyebrow' in original,name);
 const post=async changes=>{const current=await read();const values={...original,...Object.fromEntries(Object.entries(current).filter(([k])=>k.startsWith('TOKEN'))),...changes};const body=new URLSearchParams(Object.entries(values).filter(([,v])=>v!==null));return request(edit,{method:'POST',body});};
 const marker='Label <'+name+'> '+Date.now();
 try{
  let r=await post({label_eyebrow:marker,label_eyebrow_enabled:'1',...('label_intro' in original?{label_intro:marker+'\nSecond line',label_intro_enabled:'1'}:{})});assert.equal(r.status,303);
  const saved=await read();assert.equal(saved.label_eyebrow,marker);
  // Poradenství can be intentionally hidden; do not change its visibility for this test.
  const page=await (await fetch(base)).text();if(name!=='Poradenství')assert(page.includes(marker.replace('<','&lt;').replace('>','&gt;')));
  r=await post({label_eyebrow:marker,label_eyebrow_enabled:null,...('label_intro' in original?{label_intro:marker+'\nSecond line',label_intro_enabled:null}:{})});assert.equal(r.status,303);
  assert.equal((await read()).label_eyebrow,marker);assert(!(await (await fetch(base)).text()).includes(marker.replace('<','&lt;').replace('>','&gt;')));
  console.log('PASS '+name+': editing, escaping, disabling and retaining text');
 }finally{assert.equal((await post({})).status,303);}
}
console.log('PASS original labels restored');