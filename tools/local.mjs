import {spawn} from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import net from 'node:net';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const php=path.join(root,'.runtime/php/php.exe');
const running=port=>new Promise(resolve=>{const s=net.connect(port,'127.0.0.1');s.once('connect',()=>{s.destroy();resolve(true)});s.once('error',()=>resolve(false));});
const start=(exe,args,name)=>{const out=fs.openSync(path.join(root,`.runtime/${name}-out.log`),'a'),err=fs.openSync(path.join(root,`.runtime/${name}-error.log`),'a');const p=spawn(exe,args,{cwd:root,detached:true,windowsHide:true,stdio:['ignore',out,err]});p.unref();fs.writeFileSync(path.join(root,`.runtime/${name}.pid`),String(p.pid));};
const commands={worker:'tools/worker.php',admin:'tools/configure-admin.php',check:'tests/integration.php'};
if(commands[process.argv[2]]){const p=spawn(php,[commands[process.argv[2]]],{cwd:root,windowsHide:true,stdio:'inherit'});p.on('exit',code=>process.exit(code));}
else {
 if(!await running(3307))start(path.join(root,'.runtime/mariadb-11.4.10-winx64/bin/mariadbd.exe'),[`--defaults-file=${root}/.runtime/db/my.ini`,'--bind-address=127.0.0.1','--port=3307','--console'],'db');
 if(!await running(8080))start(php,['-S','127.0.0.1:8080','tools/router.php'],'web');
 let workerAlive=false;try{process.kill(Number(fs.readFileSync(path.join(root,'.runtime/worker.pid'),'utf8')),0);workerAlive=true;}catch{}
 if(!workerAlive)start(process.execPath,['tools/worker-loop.mjs'],'worker');
 console.log('Website: http://127.0.0.1:8080/ | Admin: /sprava/ | Credentials: storage/LOCAL_ACCESS.md');
}
