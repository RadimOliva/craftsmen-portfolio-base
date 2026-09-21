<?php
if(PHP_SAPI!=='cli')exit(1);
chdir(dirname(__DIR__));require 'index.php';
$wire->users->setCurrentUser($wire->users->get(41));
$languageSupport=$wire->modules->get('LanguageSupport');
if(!$wire->languages)$languageSupport->init();
$lang=$wire->languages->getDefault();$lang->of(false);$lang->title='Čeština';
foreach(glob('resources/czech/*.json') as $file){if(!$lang->language_files->get(basename($file)))$lang->language_files->add(realpath($file));}
$lang->save();
$wire->modules->install('AdminThemeUikit');
foreach($wire->users->find('include=all') as $user){$user->of(false);$user->language=$lang;$user->admin_theme='AdminThemeUikit';$user->save();}
echo "Czech admin configured.\n";
