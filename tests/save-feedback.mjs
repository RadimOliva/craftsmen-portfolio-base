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
 const create=`/sprava/${route}/?new=1`;
 let r=await request(create), f=fields(await r.text());
 const title='Save timing test '+Date.now();
 r=await request(create,{method:'POST',body:new URLSearchParams({...f,title,text:'Initial',location:'Test',sort:'99',enabled:'1'})});assert.equal(r.status,303);const edit=r.headers.get('location');
 try{
  html=await (await request(edit)).text();assert(html.replaceAll('&eacute;','é').includes('Změny byly uloženy a jsou zveřejněné.'));
  for(let i=0;i<3;i++){
   const marker=`Immediate update ${route} ${Date.now()} ${i}`;const start=Date.now();
   r=await request(edit,{method:'POST',body:new URLSearchParams({...fields(html),title:marker,text:marker,location:'Test',sort:'99',enabled:'1'})});assert.equal(r.status,303);
   // Read public content first: publication must not depend on viewing the admin redirect.
   const page=await fetch(base);assert(page.headers.get('cache-control').includes('no-store'));assert((await page.text()).includes(marker));
   html=await (await request(edit)).text();assert(html.replaceAll('&eacute;','é').includes('Změny byly uloženy a jsou zveřejněné.'));assert.equal(fields(html).text.replace(/<[^>]+>/g,'').trim(),marker);
   console.log(`PASS ${route}: save ${i+1}, immediate frontend/readback/confirmation (${Date.now()-start}ms)`);
  }
 }finally{html=await (await request(edit)).text();assert.equal((await request(edit,{method:'POST',body:new URLSearchParams({...fields(html),delete:'1'})})).status,303);}
}
console.log('PASS temporary items removed');