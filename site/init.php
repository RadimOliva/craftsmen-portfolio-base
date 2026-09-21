<?php namespace ProcessWire;
$wire->addHookAfter('Page::viewable',function($event){
    $u=wire('user');$p=$event->object;
    if($u->hasRole('klient')&&!$u->isSuperuser()&&$p->template->name==='admin'&&$p->id!==2&&!in_array($p->name,['prehled','obsah-webu','sluzby','reference','poptavky','kontakt','nastaveni','login','profile'],true))$event->return=false;
});
$wire->addHook('/robots.txt',function($event){header('Content-Type: text/plain; charset=utf-8');$c=wire('config')->craft;if($c['mode']==='local')return "User-agent: *\nDisallow: /\n";return "User-agent: *\nDisallow: /sprava/\nDisallow: /obsah/\nSitemap: ".$c['baseUrl']."/sitemap.xml\n";});
$wire->addHook('/sitemap.xml',function($event){header('Content-Type: application/xml; charset=utf-8');$base=Craft::e(rtrim(wire('config')->craft['baseUrl'],'/'));return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>'.$base.'/</loc></url><url><loc>'.$base.'/ochrana-osobnich-udaju/</loc></url></urlset>';});
