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
for(const route of ['sluzby','reference']){
 const create=`/sprava/${route}/?new=1`,title='Rich text test '+Date.now();
 const f=fields(await (await request(create)).text());
 const text='<p>First <strong>bold</strong> paragraph.</p><p>Second<br>line.</p><ul><li>One</li><li>Two</li></ul><ol><li>Numbered</li></ol><script>alert(1)</script><img src=x onerror=alert(1)><p style="color:red" onclick="alert(1)">Safe</p>';
 const r=await request(create,{method:'POST',body:new URLSearchParams({...f,title,text,text_format:'html',location:'Test',sort:'99',enabled:'1'})});assert.equal(r.status,303);const edit=r.headers.get('location');
 try{
  html=await (await request(edit)).text();const saved=fields(html).text;
  assert(saved.includes('<ul><li>One</li><li>Two</li></ul>'));assert(saved.includes('<strong>bold</strong>'));assert(saved.includes('<br'));assert(!/script|onerror|onclick|style=|<img/.test(saved));
  const page=await (await fetch(base)).text();
  if(route==='reference'){const data=JSON.parse(page.match(/id="gallery-data">([\s\S]*?)<\/script>/)[1]);assert(Object.values(data).some(d=>d.title===title&&d.description===saved));}
  else assert(page.includes(saved));
  console.log(`PASS ${route}: paragraphs, breaks, lists, emphasis, sanitization, public rendering`);
 }finally{html=await (await request(edit)).text();await request(edit,{method:'POST',body:new URLSearchParams({...fields(html),delete:'1'})});}
}
console.log('PASS temporary items removed');