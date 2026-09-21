import fs from 'node:fs';import path from 'node:path';import {fileURLToPath} from 'node:url';import {execFileSync} from 'node:child_process';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
for(const name of ['worker','web','db']){
 const file=path.join(root,'.runtime',name+'.pid');if(!fs.existsSync(file))continue;
 const pid=Number(fs.readFileSync(file,'utf8'));if(!Number.isInteger(pid)||pid<1)continue;
 try{
  const command=execFileSync('powershell',['-NoProfile','-Command',`(Get-CimInstance Win32_Process -Filter 'ProcessId = ${pid}').CommandLine`],{encoding:'utf8',windowsHide:true}).trim();
  const expected=name==='worker'?'tools/worker-loop.mjs':name==='web'?'tools/router.php':root.replaceAll('\\','/');
  if(!command.replaceAll('\\','/').includes(expected))throw new Error('PID no longer belongs to the expected local process');
  // PID files belong to this launcher; validate command line before stopping.
  process.kill(pid);fs.unlinkSync(file);console.log('Stopped '+name);
 }catch(e){console.log('Could not stop '+name+': '+e.message);}
}
