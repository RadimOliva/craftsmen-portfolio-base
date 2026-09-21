<?php namespace ProcessWire;

final class Craft {
    public static function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    public static function data(Page $p): array { return json_decode((string)$p->getUnformatted('craft_data'), true) ?: []; }
    public static function put(Page $p, array $d): void { $p->of(false); $p->craft_data=json_encode($d,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR); $p->save(); }
    public static function page(string $name): Page { return wire('pages')->get('template=craft_section|craft_settings, name=' . wire('sanitizer')->selectorValue($name)); }
    public static function settings(): array { return self::data(self::page('nastaveni')); }
    public static function sectionLabels(string $name,array $data):array {
        $defaults=[
            'hero'=>['eyebrow'=>$data['eyebrow']??'POCTIVÁ PRÁCE. SPOLEHLIVÁ DOMLUVA.'],
            'o-nas'=>['eyebrow'=>'01 / O NÁS'],
            'poradenstvi'=>['eyebrow'=>'03 / PORADENSTVÍ'],
            'services'=>['eyebrow'=>'02 / NAŠE SLUŽBY','intro'=>"Praktická řešení pro váš domov.\nVždy s důrazem na kvalitu a detail."],
            'references'=>['eyebrow'=>'04 / REALIZOVALI JSME','intro'=>"Za každou realizací je kus naší práce.\nProhlédněte si vybrané projekty."],
            'contact'=>['eyebrow'=>'05 / KONTAKT','intro'=>"Řekněte nám, co potřebujete.\nSpolečně najdeme cestu k řešení."]
        ];
        return ($data['section_labels'][$name]??[])+$defaults[$name]+['eyebrow_enabled'=>true,'intro_enabled'=>true];
    }
    public static function sectionLabel(string $name,array $data,string $kind,string $class=''):string {
        $labels=self::sectionLabels($name,$data);$text=$labels[$kind]??'';
        if(!$labels[$kind.'_enabled']||trim($text)==='')return '';
        return '<p'.($class!==''?' class="'.self::e($class).'"':'').'>'.($name==='hero'?'<span></span> ':'').nl2br(self::e($text),false).'</p>';
    }
    public static function cleanBody(string $html): string {
        return wire('sanitizer')->purify($html,['HTML.Allowed'=>'p,br,strong,b,em,i,ul,ol,li','AutoFormat.AutoParagraph'=>true]);
    }
    public static function body(array $data): string {
        $text=(string)($data['text']??'');
        if(($data['text_format']??'')==='html')return self::cleanBody($text);
        if(trim($text)==='')return '';
        $paragraphs=preg_split('/\R\s*\R/u',trim($text));
        return implode('',array_map(fn($p)=>'<p>'.nl2br(self::e($p),false).'</p>',$paragraphs));
    }
    public static function trust(array $s): array {
        return ['enabled'=>$s['trust_enabled']??true,'items'=>$s['trust_items']??[
            ['icon'=>'clock','title'=>($s['experience']??'30+').' let praxe','subtitle'=>'v poctivém řemesle'],
            ['icon'=>'check','title'=>'Řešení na míru','subtitle'=>'Vašemu domu i vašim potřebám'],
            ['icon'=>'home','title'=>'Pro domácnosti i firmy','subtitle'=>'Od drobných oprav po rekonstrukce']
        ]];
    }
    public static function trustIcons(): array {
        return ['clock'=>'Hodiny','check'=>'Fajfka','home'=>'Dům','star'=>'Hvězda','shield'=>'Štít','tools'=>'Nářadí',
            'building'=>'Budova','building-o'=>'Budova – obrys','industry'=>'Dílna','briefcase'=>'Kufřík','cog'=>'Ozubené kolo','cogs'=>'Mechanismus',
            'handshake-o'=>'Podání ruky',
            'paint-brush'=>'Štětec','tint'=>'Kapka','bolt'=>'Blesk','fire'=>'Oheň','leaf'=>'List','tree'=>'Strom','sun-o'=>'Slunce','cloud'=>'Mrak',
            'umbrella'=>'Deštník','recycle'=>'Recyklace','globe'=>'Zeměkoule','map-marker'=>'Místo','map'=>'Mapa','compass'=>'Kompas',
            'truck'=>'Nákladní vůz','car'=>'Auto','bicycle'=>'Kolo','road'=>'Silnice','users'=>'Lidé','user'=>'Člověk','heart'=>'Srdce',
            'thumbs-up'=>'Palec nahoru','smile-o'=>'Úsměv','trophy'=>'Pohár','certificate'=>'Certifikát','diamond'=>'Diamant','flag'=>'Vlajka',
            'check-circle'=>'Potvrzení','check-square-o'=>'Zaškrtnuté políčko','lock'=>'Zámek','key'=>'Klíč','lightbulb-o'=>'Žárovka',
            'search'=>'Lupa','eye'=>'Oko','camera'=>'Fotoaparát','picture-o'=>'Obrázek','phone'=>'Telefon','envelope'=>'Obálka',
            'comments'=>'Konverzace','calendar'=>'Kalendář','hourglass'=>'Přesýpací hodiny','dashboard'=>'Měřidlo','balance-scale'=>'Váhy',
            'calculator'=>'Kalkulačka','pencil'=>'Tužka','book'=>'Kniha','file-text-o'=>'Dokument','list-alt'=>'Seznam','puzzle-piece'=>'Puzzle'];
    }
    public static function trustIcon(string $key): string {
        if(!array_key_exists($key,self::trustIcons()))$key='check';
        $name=['clock'=>'clock-o','tools'=>'wrench'][$key]??$key;
        return '<i class="fa fa-'.$name.'" aria-hidden="true"></i>';
    }
    public static function branding(array $s, bool $hasImage): array {
        $mode=$s['brand_mode']??($hasImage?'image':'text');
        $b=$s+['brand_show_image'=>in_array($mode,['image','image_text'],true),'brand_show_text'=>in_array($mode,['text','image_text'],true),'brand_text'=>'','brand_tagline'=>'POCTIVÉ ŘEMESLO','brand_font'=>'sans'];
        if(!in_array($b['brand_font'],['sans','serif'],true))$b['brand_font']='sans';
        if(trim($b['brand_text'])==='')$b['brand_text']=$s['company'];
        return $b;
    }
    public static function fields(string $mode): array {
        $f=['name'=>['Jméno','text'],'phone'=>['Telefon','tel'],'email'=>['E-mail','email']];
        if($mode==='quote') $f+=['address'=>['Místo realizace / adresa','text'],'category'=>['Typ práce / kategorie','select']];
        $f+=['subject'=>['Předmět','text'],'message'=>[$mode==='quote'?'Popis zakázky':'Zpráva','textarea']];
        if($mode==='quote') $f+=['files'=>['Fotografie a soubory','file']];
        return $f;
    }
    public static function rules(array $s, string $mode): array {
        $r=[]; foreach(self::fields($mode) as $key=>$field) $r[$key]=$s['rules'][$mode][$key]??['enabled'=>true,'required'=>$key!=='files'];
        return $r;
    }
    public static function validateRules(array $rules): void {
        foreach(['simple','quote'] as $mode) {
            $r=$rules[$mode]??[];
            if(!(($r['phone']['enabled']??false)&&($r['phone']['required']??false)) && !(($r['email']['enabled']??false)&&($r['email']['required']??false)))
                throw new \InvalidArgumentException('V každém formuláři musí být telefon nebo e-mail zobrazený a povinný.');
        }
    }
    public static function items(string $template): PageArray { return wire('pages')->find("template=$template, sort=sort"); }
    public static function categories(): array {
        $a=[]; foreach(self::items('craft_service') as $p) if(self::data($p)['enabled']??true) $a[]=$p->title;
        $a[]='Jiné'; return $a;
    }
    public static function image(Page $p, int $width=1200, int $index=0): array {
        $img=$p->craft_images?->eq($index);
        if(!$img) return ['url'=>'/site/templates/assets/placeholder.svg','alt'=>$p->title,'srcset'=>''];
        $small=$img->width(min(640,$img->width)); $large=$img->width(min($width,$img->width));
        return ['url'=>$large->url,'alt'=>$img->description?:$p->title,'srcset'=>"{$small->url} {$small->width}w, {$large->url} {$large->width}w"];
    }
    public static function storage(): string { return dirname(__DIR__,3).'/storage/'; }
    public static function db(): WireDatabasePDO { return wire('database'); }
    public static function schema(): void {
        $db=self::db();
        $db->exec('CREATE TABLE IF NOT EXISTS craft_receipts (token CHAR(64) PRIMARY KEY, page_id INT UNSIGNED NOT NULL, created DATETIME NOT NULL) ENGINE=InnoDB');
        $db->exec("CREATE TABLE IF NOT EXISTS craft_queue (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, page_id INT UNSIGNED NOT NULL, channel VARCHAR(8) NOT NULL, recipient VARCHAR(191) NOT NULL, state VARCHAR(20) NOT NULL DEFAULT 'pending', attempts INT NOT NULL DEFAULT 0, due DATETIME NOT NULL, detail VARCHAR(255) NOT NULL DEFAULT '', provider_id VARCHAR(191) NOT NULL DEFAULT '', UNIQUE KEY delivery(page_id,channel,recipient)) ENGINE=InnoDB");
        $db->exec('CREATE TABLE IF NOT EXISTS craft_rate (bucket CHAR(64) PRIMARY KEY, hits INT NOT NULL, expires DATETIME NOT NULL) ENGINE=InnoDB');
        $db->exec('CREATE TABLE IF NOT EXISTS craft_views (day DATE PRIMARY KEY, views INT UNSIGNED NOT NULL) ENGINE=InnoDB');
    }
    public static function countView(): void {
        if(wire('user')->isLoggedin() || preg_match('/bot|spider|crawl|headless/i',$_SERVER['HTTP_USER_AGENT']??'')) return;
        self::db()->exec('INSERT INTO craft_views VALUES(CURRENT_DATE,1) ON DUPLICATE KEY UPDATE views=views+1');
    }
    public static function privateDownload(int $id, int $index): never {
        if(!wire('user')->hasPermission('craft-manage')) { http_response_code(403); exit; }
        $p=wire('pages')->get($id);
        if($p->template->name!=='craft_enquiry') {http_response_code(404);exit;}
        $file=self::data($p)['files'][$index]??null;
        if(!$file || !preg_match('/^[a-f0-9]{48}$/',$file['stored'])) {http_response_code(404);exit;}
        $path=self::storage().'uploads/'.$file['stored'];
        if(!is_file($path)) {http_response_code(404);exit;}
        header('Content-Type: '.$file['mime']); header('X-Content-Type-Options: nosniff'); header('Cache-Control: no-store');
        header("Content-Disposition: attachment; filename*=UTF-8''".rawurlencode($file['name']));
        header('Content-Length: '.filesize($path)); readfile($path); exit;
    }
    public static function deleteEnquiry(Page $p): void {
        if($p->template->name!=='craft_enquiry') throw new \RuntimeException('Invalid enquiry');
        foreach(self::data($p)['files']??[] as $f) if(preg_match('/^[a-f0-9]{48}$/',$f['stored'])) {
            $path=self::storage().'uploads/'.$f['stored']; if(is_file($path)&&!unlink($path)) throw new \RuntimeException('Přílohu se nepodařilo odstranit.');
        }
        foreach(['craft_queue','craft_receipts'] as $table) { $q=self::db()->prepare("DELETE FROM $table WHERE page_id=?"); $q->execute([$p->id]); }
        wire('pages')->delete($p,true);
    }
}
