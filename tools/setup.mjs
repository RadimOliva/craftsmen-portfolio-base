import fs from 'node:fs';import path from 'node:path';import {spawn} from 'node:child_process';import {createHash} from 'node:crypto';import {fileURLToPath} from 'node:url';import net from 'node:net';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');if(process.platform!=='win32')throw new Error('This portable setup is for Windows. Use the PHP CLI installer on other systems.');
if(fs.existsSync(path.join(root,'storage/local.php')))throw new Error('Already configured. Setup will not overwrite this installation.');
const runtime=path.join(root,'.runtime');fs.mkdirSync(runtime,{recursive:true});
const run=(exe,args)=>new Promise((resolve,reject)=>{const p=spawn(exe,args,{cwd:root,windowsHide:true,stdio:'inherit'});p.on('error',reject);p.on('exit',c=>c===0?resolve():reject(new Error(exe+' exited '+c)));});
const manifest=JSON.parse(fs.readFileSync(path.join(root,'resources/runtime.json')));
for(const [name,item] of Object.entries(manifest)){const file=path.join(runtime,name+'.zip');if(!fs.existsSync(file)){console.log('Downloading official '+name);const r=await fetch(item.url);if(!r.ok)throw new Error('Download failed: '+r.status);fs.writeFileSync(file,Buffer.from(await r.arrayBuffer()));}if(createHash('sha256').update(fs.readFileSync(file)).digest('hex')!==item.sha256)throw new Error('Checksum mismatch: '+name);const dest=name==='php'?path.join(runtime,'php'):runtime;const q=x=>"'"+x.replaceAll("'","''")+"'";await run('powershell',['-NoProfile','-Command',`Expand-Archive -LiteralPath ${q(file)} -DestinationPath ${q(dest)} -Force`]);}
fs.copyFileSync(path.join(root,'resources/php.ini'),path.join(runtime,'php/php.ini'));
const php=path.join(runtime,'php/php.exe');await run(php,['-r','foreach(["pdo_mysql","gd","mbstring","fileinfo","curl","openssl"] as $e)if(!extension_loaded($e))throw new Exception("Missing extension: ".$e);']);
const dbdir=path.join(runtime,'db'),dbbin=path.join(runtime,'mariadb-11.4.10-winx64/bin');
if(!fs.existsSync(path.join(dbdir,'my.ini')))await run(path.join(dbbin,'mariadb-install-db.exe'),['--datadir='+dbdir,'--port=3307']);
const running=()=>new Promise(resolve=>{const s=net.connect(3307,'127.0.0.1');s.on('connect',()=>{s.destroy();resolve(true)});s.on('error',()=>resolve(false));});
if(!await running()){const log=fs.openSync(path.join(runtime,'db-setup.log'),'a');const p=spawn(path.join(dbbin,'mariadbd.exe'),['--defaults-file='+path.join(dbdir,'my.ini'),'--bind-address=127.0.0.1','--port=3307'],{cwd:root,windowsHide:true,detached:true,stdio:['ignore',log,log]});p.unref();}
for(let i=0;i<30&&!await running();i++)await new Promise(r=>setTimeout(r,1000));
await run(php,['tools/bootstrap.php']);await run(php,['tools/configure-admin.php']);
console.log('Ready. Run: node tools/local.mjs');
