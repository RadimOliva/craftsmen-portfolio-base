<?php namespace ProcessWire;
// Called once by the fresh installer. Do not rerun on a populated database.
$field = new Field(); $field->name='craft_data'; $field->type=$wire->modules->get('FieldtypeTextarea'); $field->label='Obsah'; $field->save();
$field = new Field(); $field->name='craft_images'; $field->type=$wire->modules->get('FieldtypeImage'); $field->label='Fotografie'; $field->extensions='jpg jpeg png webp'; $field->maxFiles=0; $field->maxFilesize=15; $field->descriptionRows=1; $field->save();
foreach(['craft_section','craft_settings','craft_service','craft_reference','craft_enquiry','craft_container'] as $name) {
 $group=new Fieldgroup();$group->name=$name;$group->add($wire->fields->get('title'));$group->add($wire->fields->get('craft_data'));$group->add($wire->fields->get('craft_images'));$group->save();
 $t=new Template();$t->name=$name;$t->fieldgroup=$group;$t->noChildren=0;$t->save();
}
$new=function($name,$title,$template,$parent,$data=[])use($wire){$p=new Page();$p->template=$template;$p->parent=$parent;$p->name=$name;$p->title=$title;$p->save();Craft::put($p,$data);return $p;};
$content=$new('obsah','Obsah webu','craft_container',1);
$sections=[
 'hero'=>['Úvod','Řemeslná výroba, renovace a stavitelství.','30 let praxe v oboru pro vaše potřeby.'],
 'o-nas'=>['O nás','Řemesla Václav Čech','Provádíme řemeslné, stavební a rekonstrukční práce pro domácnosti i firmy. Zakládáme si na poctivém provedení, spolehlivé domluvě a praktických řešeních, která dávají smysl technicky i finančně.'],
 'poradenstvi'=>['Poradenství','Poradíme s řešením','Nejste si jistí, jaký postup, materiál nebo rozsah prací zvolit? Projdeme s vámi možnosti, doporučíme vhodné řešení a předem vysvětlíme, co bude realizace obnášet. Ozvěte se nám ještě před zahájením projektu.']
];
foreach($sections as $name=>$v)$new($name,$v[0],'craft_section',$content,['heading'=>$v[1],'text'=>$v[2],'enabled'=>true]);
$services=$new('sluzby','Služby','craft_container',$content);
$serviceData=[['Rekonstrukce','Kompletní i částečné rekonstrukce domů, bytů a dalších prostor.'],['Stavební práce','Zednické, bourací a další práce od drobných oprav po větší realizace.'],['Tesařské práce','Výroba, opravy a rekonstrukce dřevěných konstrukcí.'],['Pokrývačské práce','Realizace a opravy střech včetně souvisejících klempířských prací.'],['Instalace','Instalatérské a další technické práce při stavbě nebo rekonstrukci.'],['Zakázková výroba','Individuální řemeslná řešení podle konkrétního zadání.']];
foreach($serviceData as $i=>$v){$p=$new('sluzba-'.($i+1),$v[0],'craft_service',$services,['text'=>$v[1],'enabled'=>true]);$p->sort=$i;$p->save();}
$refs=$new('reference','Reference','craft_container',$content);
foreach([['Rekonstrukce střechy rodinného domu','Praha 10','Výměna krytiny, oprava krovu a nové klempířské prvky.'],['Dřevo, které dává domu charakter','Střední Čechy','Tesařské práce a dřevěná konstrukce na míru.'],['Nový život starého domu','Praha a okolí','Citlivá rekonstrukce interiéru s důrazem na detail.']] as $i=>$v){$p=$new('realizace-'.($i+1),$v[0],'craft_reference',$refs,['location'=>$v[1],'text'=>$v[2],'enabled'=>true]);$p->sort=$i;$p->save();}
$rules=[];foreach(['simple','quote'] as $mode)foreach(Craft::fields($mode) as $key=>$v)$rules[$mode][$key]=['enabled'=>true,'required'=>$key!=='files'];
$new('nastaveni','Nastavení','craft_settings',$content,[
 'company'=>'Řemesla Václav Čech','phone'=>'+420 777 123 456','email'=>'info@remesla.example','address'=>'Praha a Střední Čechy','billing'=>'Ukázkové firemní údaje – doplňte před zveřejněním.',
 'show_address'=>true,'show_map'=>false,'map_url'=>'','services_enabled'=>true,'references_enabled'=>true,
 'form_mode'=>'quote','rules'=>$rules,'recipients'=>'poptavky@example.test','sms_enabled'=>false,'sms_recipient'=>'',
 'contact_heading'=>'Máte projekt nebo potřebujete opravu?','contact_text'=>'Popište nám stručně, co potřebujete. Ozveme se a domluvíme další postup.',
 'seo_title'=>'Řemesla Václav Čech | Poctivé řemeslo od základu','seo_description'=>'Řemeslné, stavební a rekonstrukční práce pro domácnosti i firmy. 30 let praxe, spolehlivá domluva a poctivé provedení.',
 'privacy'=>'UKÁZKOVÝ TEXT – před spuštěním nahraďte informacemi provozovatele. Údaje z formuláře používáme pro vyřízení poptávky. Ukládáme je do správy webu a upozornění odesíláme e-mailem, případně SMS. Běžné poptávky a přílohy uchováváme 12 měsíců od přijetí; odůvodněné prodloužení se eviduje. Doplňte provozovatele, kontakty, právní základ, příjemce údajů, pravidla záloh a informace o právech subjektů údajů.'
]);
$new('poptavky','Poptávky','craft_container',$content);
$privacy=$new('ochrana-osobnich-udaju','Ochrana osobních údajů','basic-page',1);
$perm=$wire->permissions->add('craft-manage');$perm->title='Správa řemeslného webu';$perm->save();
$role=$wire->roles->add('klient');$role->addPermission('page-view');$role->addPermission('craft-manage');$role->save();
$user=$wire->users->add('klient');$user->pass=$clientPass;$user->email='klient@example.test';$user->addRole($role);$user->save();
$wire->modules->refresh();$wire->modules->install('ProcessCraft');$wire->modules->install('WireMailCraft');$wire->modules->install('LanguageSupport');$wire->modules->install('AdminThemeUikit');
$admin=$wire->pages->get(2);
foreach(['prehled'=>'Přehled','obsah-webu'=>'Obsah webu','sluzby'=>'Služby','reference'=>'Reference','poptavky'=>'Poptávky','kontakt'=>'Kontakt','nastaveni'=>'Nastavení'] as $name=>$title){$p=$new($name,$title,'admin',$admin);$p->process=$wire->modules->get('ProcessCraft');$p->save();}
Craft::schema();
foreach(['hero'=>'craft','o-nas'=>'workshop','poradenstvi'=>'architecture'] as $name=>$image){$p=Craft::page($name);$p->of(false);$p->craft_images->add(__DIR__.'/../site/templates/assets/images/'.$image.'.jpg');$p->save();}
$demoImages=['home','construction','craft','architecture','workshop','craft'];
foreach(Craft::items('craft_service') as $i=>$p){$p->of(false);$p->craft_images->add(__DIR__.'/../site/templates/assets/images/'.$demoImages[$i].'.jpg');$p->save();}
foreach(Craft::items('craft_reference') as $i=>$p){$p->of(false);foreach([$demoImages[$i],$demoImages[($i+1)%6]] as $image)$p->craft_images->add(__DIR__.'/../site/templates/assets/images/'.$image.'.jpg');$p->save();}
echo "Demo schema, content and client role created.\n";
