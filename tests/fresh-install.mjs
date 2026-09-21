import fs from 'node:fs';import path from 'node:path';import {spawnSync} from 'node:child_process';
const root=process.cwd(),source=process.argv[2];if(!source)throw new Error('Pass the clean release directory');
const dest=path.join(root,'.runtime','fresh-check-'+Date.now());fs.cpSync(source,dest,{recursive:true});
const php=path.join(root,'.runtime/php/php.exe');const env={...process.env,CRAFT_DB_NAME:'craft_test_'+Date.now()};
for(const script of ['tools/bootstrap.php','tools/configure-admin.php']){const result=spawnSync(php,[script],{cwd:dest,env,windowsHide:true,encoding:'utf8'});console.log(result.stdout);if(result.status!==0)throw new Error(result.stderr||'Installer failed');}
const verify=`<?php namespace ProcessWire; chdir(__DIR__); require 'index.php'; if(count(Craft::items('craft_enquiry'))!==0)throw new \\RuntimeException('Enquiries leaked'); if(count(Craft::items('craft_reference'))!==3)throw new \\RuntimeException('Missing references'); if(count(Craft::items('craft_service'))!==6)throw new \\RuntimeException('Missing services'); if(!Craft::page('hero')->craft_images->first())throw new \\RuntimeException('Missing images'); if(!$users->get('klient')->hasPermission('craft-manage'))throw new \\RuntimeException('Missing client role'); echo 'Fresh install verified: clean database, demo content, images and client permissions.\\n';`;
fs.writeFileSync(path.join(dest,'verify.php'),verify);
const result=spawnSync(php,['verify.php'],{cwd:dest,env,windowsHide:true,encoding:'utf8'});console.log(result.stdout);if(result.status!==0)throw new Error(result.stderr||'Verification failed');
console.log('Isolated test installation: '+dest);console.log('Original clean release unchanged.');
