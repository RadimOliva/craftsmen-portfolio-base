import fs from 'node:fs';import path from 'node:path';import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const dest=path.join(root,'.runtime','releases','craftsmen-'+new Date().toISOString().replace(/[:.]/g,'-'));fs.mkdirSync(dest,{recursive:true});
const paths=['wire','vendor','resources','tools','tests','site/templates','site/modules','site/config.php','site/init.php','site/ready.php','site/install','index.php','.htaccess','composer.json','composer.lock','README.md','LICENSE-ProcessWire.txt','CraftsmenPortfolioBase_ProjectOverview_Updated.md','Deployment_Checklist.md','Hosting_Compatibility_Shortlist.md'];
for(const relative of paths){const target=path.join(dest,relative);fs.mkdirSync(path.dirname(target),{recursive:true});fs.cpSync(path.join(root,relative),target,{recursive:true});}
fs.mkdirSync(path.join(dest,'storage'));fs.copyFileSync(path.join(root,'storage/.htaccess'),path.join(dest,'storage/.htaccess'));
fs.writeFileSync(path.join(dest,'RELEASE.json'),JSON.stringify({created:new Date().toISOString(),processwire:'3.0.259',php:'8.4.25',mariadb:'11.4.10',privateDataIncluded:false},null,2));
for(const forbidden of ['storage/local.php','storage/LOCAL_ACCESS.md','storage/uploads','storage/mail','site/assets'])if(fs.existsSync(path.join(dest,forbidden)))throw new Error('Unsafe package: '+forbidden);
console.log(dest);
