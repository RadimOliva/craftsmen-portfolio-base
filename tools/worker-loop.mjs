import {spawn} from 'node:child_process';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');let busy=false;
function run(){if(busy)return;busy=true;const p=spawn(path.join(root,'.runtime/php/php.exe'),['tools/worker.php'],{cwd:root,windowsHide:true,stdio:'inherit'});p.on('error',e=>{console.error(e.message);busy=false});p.on('exit',code=>{if(code)console.error('Worker failed:',code);busy=false;});}
run();setInterval(run,60000);
