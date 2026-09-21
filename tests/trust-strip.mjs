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
const edit='/sprava/obsah-webu/?trust=1';
const read=async()=>fields((await (await request(edit)).text()).replace(/<template[\s\S]*?<\/template>/g,''));
const original=await read();assert('trust[0][title]' in original);
async function save(rows){const f=await read();const body=new URLSearchParams(Object.entries(f).filter(([k])=>k.startsWith('TOKEN')));rows.forEach((row,i)=>Object.entries(row).forEach(([k,v])=>body.set(`trust[${i}][${k}]`,v)));return request(edit,{method:'POST',body});}
const initialRows=[];for(let i=0;i<4;i++)if(original[`trust[${i}][title]`]!==undefined)initialRows.push(Object.fromEntries(['title','subtitle','icon'].map(k=>[k,original[`trust[${i}][${k}]`]])));
async function visibility(on){const f=await read();const body=new URLSearchParams(Object.entries(f).filter(([k])=>k.startsWith('TOKEN')));body.set('visibility_autosave','1');if(on)body.set('trust_enabled','1');const r=await request(edit,{method:'POST',body});assert.deepEqual(await r.json(),{saved:true,enabled:on});}
const publicPage=async()=> (await fetch(base)).text();
try{
 await visibility(true);
 const rows=Array.from({length:4},(_,i)=>({title:`Výhoda <${i}>`,subtitle:`Podtitulek & ${i}`,icon:['clock','star','shield','tools'][i]}));
 assert.equal((await save(rows)).status,303);let page=await publicPage();assert(page.includes('Výhoda &lt;3&gt;')&&page.includes('Podtitulek &amp; 3'));assert.equal((await read())['trust[3][icon]'],'tools');console.log('PASS four snippets, icons, escaping, CMS persistence');
 let r=await save([...rows,rows[0]]);assert((await r.text()).includes('Změny nebyly dokončeny'));assert.equal((await read())['trust[3][icon]'],'tools');console.log('PASS server limit preserves stored content');
 r=await save([{...rows[0],icon:'invalid'}]);assert((await r.text()).includes('Změny nebyly dokončeny'));console.log('PASS invalid icon rejected');
 await save(rows.slice(0,1));page=await publicPage();assert(page.includes('Výhoda &lt;0&gt;')&&!page.includes('Výhoda &lt;1&gt;'));console.log('PASS remove snippets');
 await visibility(false);assert(!(await publicPage()).includes('class="trust-strip"'));await visibility(true);assert((await publicPage()).includes('Výhoda &lt;0&gt;'));console.log('PASS visibility autosave retains content');
 await save([]);assert(!(await publicPage()).includes('class="trust-strip"'));console.log('PASS empty strip hidden');
}finally{assert.equal((await save(initialRows)).status,303);await visibility(original.trust_enabled==='1');console.log('PASS original content and visibility restored');}