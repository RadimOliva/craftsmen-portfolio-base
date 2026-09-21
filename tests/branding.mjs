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
const contactHtml=await (await request('/sprava/kontakt/')).text();
assert(!contactHtml.includes('brand_mode')&&!contactHtml.includes('name="images[]"'));
const company=fields(contactHtml).company;
const edit='/sprava/nastaveni/';html=await (await request(edit)).text();
assert(html.includes('Logo a text v navigaci'));assert(!html.includes('name="brand_mode"')&&!html.includes('name="brand_mark"'));const original=fields(html);
const imageCount=[...html.matchAll(/class="craft-image-row"/g)].length;
const header=async()=>{const r=await fetch(base);assert.equal(r.status,200);return (await r.text()).match(/<a class="brand brand-font-[\s\S]*?<\/a>/)?.[0]||'';};
const baseline=await header();
async function save(changes={},upload=false) {
 const current=fields(await (await request(edit)).text());const body=new FormData();
 for(const [k,v] of Object.entries({...original,...Object.fromEntries(Object.entries(current).filter(([k])=>k.startsWith('TOKEN'))),...changes}))if(v!==null)body.set(k,v);
 if(upload)body.append('images[]',new Blob([fs.readFileSync('site/templates/assets/images/craft.jpg')],{type:'image/jpeg'}),'branding-test.jpg');
 const r=await request(edit,{method:'POST',body});assert.equal(r.status,303,'CMS save redirects successfully');
}
try {
 await save({brand_show_image:null,brand_show_text:'1',brand_text:'Test <brand>',brand_tagline:'Custom & subtitle',brand_font:'serif'});
 let h=await header();assert(h.includes('Test &lt;brand&gt;')&&h.includes('Custom &amp; subtitle')&&!h.includes('brand-mark')&&h.includes('brand-font-serif')&&!h.includes('<img'));
 html=await (await request(edit)).text();assert.equal(fields(html).brand_text,'Test <brand>');
 console.log('PASS text without initials, subtitle, font, escaping and CMS persistence');
 await save({brand_show_image:null,brand_show_text:'1',brand_text:'',brand_tagline:''});h=await header();assert(!h.includes('<small>')&&!h.includes('brand-mark')&&h.includes(company));
 console.log('PASS optional text and company-name fallback');
 if(!imageCount){await save({brand_show_image:'1',brand_show_text:null});assert.equal(await header(),'');console.log('PASS missing image does not enable disabled text');}
 await save({brand_show_image:'1',brand_show_text:null,brand_text:'Hidden brand'},true);h=await header();assert(h.includes('<img')&&!h.includes('brand-copy'));
 console.log('PASS image upload and image-only mode');
 await save({brand_show_image:'1',brand_show_text:'1',brand_text:'Logo with text'});h=await header();assert(h.includes('<img')&&h.includes('Logo with text')&&h.indexOf('<img')<h.indexOf('brand-copy'));
 html=await (await request(edit)).text();assert.equal(fields(html).brand_show_image,'1');assert.equal(fields(html).brand_show_text,'1');
 console.log('PASS image left, text right, both toggles persist');
 await save({brand_show_image:null,brand_show_text:null});assert.equal(await header(),'');
 console.log('PASS both disabled removes branding link');

 await save({brand_show_image:null,brand_show_text:'1'});assert(!(await header()).includes('<img'));console.log('PASS switching back to text retains uploaded image');
} finally {
 await save({[`remove[${imageCount}]`]:'1'});
 assert.equal(await header(),baseline,'Original branding restored');
 console.log('PASS original branding restored and test upload removed');
}
