<?php namespace ProcessWire;
/** Client screens inside ProcessWire's authenticated admin, using its session/CSRF/roles. */
class ProcessCraft extends Process {
    public static function getModuleInfo(){return ['title'=>'Správa řemeslného webu','version'=>100,'permission'=>'craft-manage','icon'=>'home','summary'=>'Česká správa obsahu a poptávek.'];}
    private function esc($v){return Craft::e($v);}
    private function form():string{return '<form method="post" enctype="multipart/form-data" class="craft-admin-form">'.$this->session->CSRF->renderInput();}
    private function button(string $text='Uložit změny'):string{return '<button class="ui-button" type="submit">'.$this->esc($text).'</button>';}
    private function field(string $key,string $label,$value='',string $type='text'):string {
        $id='craft-'.preg_replace('/[^a-z0-9_-]/i','-',$key);$base='<label for="'.$id.'">'.$this->esc($label).'</label>';
        if($type==='checkbox')return '<label class="craft-check"><input type="checkbox" name="'.$key.'" value="1" '.($value?'checked':'').'> '.$this->esc($label).'</label>';
        if($type==='textarea')return '<div>'.$base.'<textarea id="'.$id.'" name="'.$key.'" rows="5">'.$this->esc($value).'</textarea></div>';
        return '<div>'.$base.'<input id="'.$id.'" type="'.$type.'" name="'.$key.'" value="'.$this->esc($value).'"></div>';
    }
    private function text(string $key,int $max=10000):string {return mb_substr(is_string($_POST[$key]??null)?trim($_POST[$key]):'',0,$max);}
    private function richField(string $label,array $data):string {
        $this->config->scripts->add('/wire/modules/Inputfield/InputfieldTinyMCE/tinymce-6.8.2/tinymce.min.js');
        return '<div><label for="craft-text">'.$this->esc($label).'</label><p class="craft-muted">Enter: nový odstavec. Shift+Enter: nový řádek. Pro seznam použijte tlačítko odrážek.</p><input type="hidden" name="text_format" value="html"><textarea class="craft-rich-text" id="craft-text" name="text" rows="8">'.$this->esc(Craft::body($data)).'</textarea></div>';
    }
    private function saveBody(array &$data):void {
        $text=$this->text('text',30000);
        if($this->text('text_format')==='html'){$data['text']=Craft::cleanBody($text);$data['text_format']='html';}
        else{$data['text']=$text;unset($data['text_format']);}
    }
    private function select(string $key,string $label,string $value,array $options):string {
        $out='<div><label for="craft-'.$key.'">'.$this->esc($label).'</label><select id="craft-'.$key.'" name="'.$key.'">';
        foreach($options as $v=>$text)$out.='<option value="'.$v.'" '.($value===$v?'selected':'').'>'.$this->esc($text).'</option>';
        return $out.'</select></div>';
    }
    public function ___execute(){
        $this->config->styles->add('/site/modules/ProcessCraft/admin.css');
        $this->config->scripts->add('/site/modules/ProcessCraft/admin.js');
        $this->config->js('craftAdminTitle', trim((string)(Craft::settings()['company']??'')) ?: 'Správa webu');
        $section=$this->page->name;
        if(isset($_GET['download']))Craft::privateDownload((int)($_GET['id']??0),(int)$_GET['download']);
        $out='';
        try{
            if($_SERVER['REQUEST_METHOD']==='POST'&&!$this->session->CSRF->hasValidToken())throw new \InvalidArgumentException('Platnost formuláře vypršela. Obnovte stránku.');
            $out.=match($section){'obsah-webu'=>$this->content(),'sluzby'=>$this->collection('craft_service'),'reference'=>$this->collection('craft_reference'),'poptavky'=>$this->enquiries(),'kontakt'=>$this->settings('contact'),'nastaveni'=>$this->settings('settings'),default=>$this->dashboard()};
        }catch(\Throwable $e){$this->error($e instanceof \InvalidArgumentException?$e->getMessage():'Změnu se nepodařilo uložit. Zkontrolujte zadání a zkuste to znovu.');$out.='<p>Změny nebyly dokončeny. Vraťte se zpět a zkontrolujte zadání.</p>'; $this->wire('log')->save('craft-errors','Admin operation: '.$e->getCode());}
        return $out;
    }
    private function saved(?int $id=null):never{$this->session->message('Změny byly uloženy a jsou zveřejněné.');$id=$id??(int)($_GET['id']??0);$this->session->redirect($this->page->url.($id?'?id='.$id:''),303);}
    private function dashboard():string {
        $all=$this->pages->find('template=craft_enquiry,sort=-created');$new=0;$month=0;$last=0;$expiring=[];
        foreach($all as $p){$d=Craft::data($p);if(($d['status']??'new')==='new')$new++;$ym=date('Y-m',$p->created);if($ym===date('Y-m'))$month++;if($ym===date('Y-m',strtotime('first day of last month')))$last++;if(($d['expires']??'9999')<=date('Y-m-d',strtotime('+30 days')))$expiring[]=$p;}
        $fails=(int)Craft::db()->query("SELECT COUNT(*) FROM craft_queue WHERE state IN ('failed','uncertain')")->fetchColumn();
        $views=(int)Craft::db()->query('SELECT COALESCE(SUM(views),0) FROM craft_views WHERE day>=DATE_FORMAT(CURRENT_DATE,\'%Y-%m-01\')')->fetchColumn();
        $out='<div class="craft-stats">';foreach(['Nové poptávky'=>$new,'Tento měsíc'=>$month,'Minulý měsíc'=>$last,'Chyby upozornění'=>$fails,'Zobrazení webu / měsíc'=>$views] as $label=>$value)$out.='<article><strong>'.$value.'</strong><span>'.$label.'</span></article>';$out.='</div><p class="craft-muted">Zobrazení jsou přibližná, bez sledování identity návštěvníků. Nejde o počet unikátních lidí.</p>';
        if($this->config->craft['mode']==='local')$out.='<p class="craft-notice">Místní demo: e-maily se ukládají do místní schránky, SMS se neposílají. Použijte ukázkové údaje.</p>';
        if($fails)$out.='<p class="craft-notice">Některá upozornění potřebují kontrolu. Poptávky jsou bezpečně uložené. <a href="/sprava/poptavky/?failures=1">Zobrazit poptávky s chybou</a></p>';
        if($expiring){$out.='<h3>Blíží se automatické smazání</h3><ul>';foreach($expiring as $p)$out.='<li><a href="/sprava/poptavky/?id='.$p->id.'">'.$this->esc($p->title).'</a> – '.$this->esc(Craft::data($p)['expires']).'</li>';$out.='</ul>';}
        $out.='<h2>Poslední poptávky</h2>'.$this->enquiryTable(array_slice(iterator_to_array($all),0,5));
        return $out;
    }
    private function backLink(string $label):string {
        return '<p class="craft-back"><a class="ui-button" href="'.$this->esc($this->page->url).'">← '.$this->esc($label).'</a></p>';
    }
    private function content():string {
        if(isset($_GET['trust']))return $this->trustEditor();
        $id=(int)($_GET['id']??0);$p=$id?$this->pages->get($id):new NullPage();
        if(!$id){
            $trust=Craft::trust(Craft::settings());
            $badge=fn(bool $enabled,string $text='')=>'<span class="craft-section-status '.($enabled?'is-enabled':'is-hidden').'">'.($text?:($enabled?'Zobrazeno':'Skryto')).'</span>';
            $out='<div class="craft-edit-list craft-section-list">';
            $trustLink='<a href="?trust=1"><strong>Pruh výhod</strong>'.$badge((bool)$trust['enabled'] && (bool)$trust['items'], !$trust['items']?'Skryto – bez výhod':'').'<span>Ikony, texty a viditelnost →</span></a>';
            foreach($this->pages->find('template=craft_section,sort=id') as $item){
                $enabled=$item->name==='hero'||(bool)(Craft::data($item)['enabled']??false);
                $out.='<a href="?id='.$item->id.'"><strong>'.$this->esc($item->title).'</strong>'.$badge($enabled,$item->name==='hero'?'Vždy zobrazeno':'').'<span>Texty a fotografie →</span></a>';
                if($item->name==='hero')$out.=$trustLink;
            }
            return $out.'</div>';
        }
        if($p->template->name!=='craft_section')throw new \InvalidArgumentException('Obsah nebyl nalezen.');
        $d=Craft::data($p);
        if($_SERVER['REQUEST_METHOD']==='POST'){$this->saveLabels($p->name,$d);$d['heading']=$this->text('heading',250);$this->saveBody($d);$d['enabled']=$p->name==='hero'||isset($_POST['enabled']);$this->images($p);Craft::put($p,$d);$this->saved();}
        return $this->backLink('Zpět na obsah webu').'<h2>'.$this->esc($p->title).'</h2>'.$this->form().$this->labelFields($p->name,$d).$this->field('heading','Nadpis',$d['heading']).$this->richField('Text',$d).($p->name==='hero'?'<p>Úvodní sekce je vždy zapnutá.</p>':$this->field('enabled','Zobrazit tuto sekci',$d['enabled'],'checkbox')).$this->imageFields($p).$this->button().'</form>';
    }
    private function trustCard(string $index,array $item):string {
        return '<fieldset class="craft-trust-card"><legend>Výhoda</legend>'.$this->field("trust[$index][title]",'Nadpis (max. 80 znaků)',$item['title']??'').$this->field("trust[$index][subtitle]",'Podtitulek (max. 150 znaků)',$item['subtitle']??'').$this->trustIconPicker($index,$item['icon']??'check').'<button type="button" class="craft-trust-remove">Odebrat výhodu</button></fieldset>';
    }
    private function labelFields(string $name,array $data):string {
        $labels=Craft::sectionLabels($name,$data);$out='<fieldset class="craft-label-fields"><legend>Doplňkové texty sekce</legend>';
        foreach(['eyebrow'=>'Text nad nadpisem','intro'=>'Úvodní doprovodný text'] as $key=>$label){
            if(!array_key_exists($key,$labels))continue;
            $out.=$this->field('label_'.$key.'_enabled','Zobrazit: '.$label,$labels[$key.'_enabled'],'checkbox');
            $out.=$this->field('label_'.$key,$label,$labels[$key],$key==='intro'?'textarea':'text');
        }
        return $out.'<p class="craft-muted">Vypnutí zachová uložený text. Prázdný text se nezobrazuje. Změny potvrďte uložením.</p></fieldset>';
    }
    private function saveLabels(string $name,array &$data):void {
        $labels=Craft::sectionLabels($name,$data);
        foreach(['eyebrow'=>150,'intro'=>1000] as $key=>$limit){
            if(!array_key_exists($key,$labels))continue;
            $labels[$key]=$this->text('label_'.$key,$limit);
            $labels[$key.'_enabled']=isset($_POST['label_'.$key.'_enabled']);
        }
        $data['section_labels'][$name]=$labels;
    }
    private function trustIconPicker(string $index,string $selected):string {
        $icons=Craft::trustIcons();if(!isset($icons[$selected]))$selected='check';
        $out='<div class="craft-icon-picker"><span>Ikona</span><input type="hidden" name="trust['.$index.'][icon]" value="'.$selected.'"><button type="button" class="craft-icon-trigger" aria-expanded="false" aria-controls="craft-icons-'.$index.'" aria-label="Vybrat ikonu: '.$this->esc($icons[$selected]).'">'.Craft::trustIcon($selected).'</button><div class="craft-icon-grid" id="craft-icons-'.$index.'" role="group" aria-label="Výběr ikony" hidden>';
        foreach($icons as $key=>$label)$out.='<button type="button" class="craft-icon-choice" data-icon="'.$key.'" aria-label="'.$this->esc($label).'" aria-pressed="'.($selected===$key?'true':'false').'">'.Craft::trustIcon($key).'</button>';
        return $out.'</div></div>';
    }
    private function trustEditor():string {
        $p=Craft::page('nastaveni');$s=Craft::data($p);$trust=Craft::trust($s);
        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(($_POST['visibility_autosave']??'')==='1'){
                $s['trust_enabled']=isset($_POST['trust_enabled']);Craft::put($p,$s);
                header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
                echo json_encode(['saved'=>true,'enabled'=>$s['trust_enabled']]);exit;
            }
            $rows=$_POST['trust']??[];
            if(!is_array($rows)||count($rows)>4)throw new \InvalidArgumentException('Pruh může obsahovat nejvýše čtyři výhody.');
            $items=[];foreach($rows as $row){
                if(!is_array($row))throw new \InvalidArgumentException('Neplatná výhoda.');
                foreach(['title','subtitle','icon'] as $key)if(!is_string($row[$key]??null))throw new \InvalidArgumentException('Vyplňte text a vyberte ikonu.');
                $title=trim($row['title']);$subtitle=trim($row['subtitle']);
                if($title===''||mb_strlen($title)>80||mb_strlen($subtitle)>150)throw new \InvalidArgumentException('Vyplňte nadpis do 80 znaků a podtitulek do 150 znaků.');
                if(!array_key_exists($row['icon'],Craft::trustIcons()))throw new \InvalidArgumentException('Vyberte ikonu ze seznamu.');
                $items[]=['title'=>$title,'subtitle'=>$subtitle,'icon'=>$row['icon']];
            }
            $s['trust_items']=$items;Craft::put($p,$s);$this->message('Výhody byly uloženy a jsou zveřejněné.');$this->session->redirect($this->page->url.'?trust=1',303);
        }
        $out=$this->backLink('Zpět na obsah webu').'<h2>Pruh výhod</h2><p>Až čtyři výhody pod úvodní sekcí. Prázdný pruh se nezobrazuje. Viditelnost se ukládá ihned; texty a odebrání potvrďte tlačítkem Uložit změny.</p>';
        $out.=str_replace('class="craft-admin-form"','class="craft-admin-form craft-visibility-form"',$this->form()).$this->field('trust_enabled','Zobrazit pruh výhod',$trust['enabled'],'checkbox').'<span class="craft-save-status" role="status" aria-live="polite"></span></form>';
        $out.=str_replace('class="craft-admin-form"','class="craft-admin-form craft-trust-form"',$this->form()).'<div class="craft-trust-items">';
        foreach($trust['items'] as $i=>$item)$out.=$this->trustCard((string)$i,$item);
        return $out.'</div><template class="craft-trust-template">'.$this->trustCard('__INDEX__',[]).'</template><button type="button" class="craft-trust-add">Přidat výhodu</button><span class="craft-trust-count" role="status"></span>'.$this->button().'</form>';
    }
    private function imageFields(Page $p):string {
        $single=$p->template->name==='craft_section';
        $out='<fieldset><legend>Fotografie</legend><p>JPG, PNG nebo WebP, nejvýše 15 MB na obrázek. '.($single?'Vyberte jednu fotografii pro zobrazení a uložte změny. Nové fotografie lze vybrat po nahrání. Při odstranění vybrané fotografie se použije první zbývající.':'Nižší pořadí je první; první obrázek je titulní.').'</p>';
        $i=0;foreach($p->craft_images as $img){$out.='<div class="craft-image-row"><img src="'.$this->esc($img->width(160)->url).'" alt="">'.$this->field('alt['.$i.']','Popis obrázku',$img->description).($single?'<label class="craft-check"><input type="radio" name="selected_image" value="'.$i.'" '.($i===0?'checked':'').'> Zobrazit tuto fotografii</label>':$this->field('order['.$i.']','Pořadí',$i,'number')).$this->field('remove['.$i.']','Odstranit',false,'checkbox').'</div>';$i++;}
        return $out.'<label>Nové fotografie <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"></label></fieldset>';
    }
    private function images(Page $p):void {
        $p->of(false);$validated=[];
        foreach($_FILES['images']['name']??[] as $i=>$name){$err=$_FILES['images']['error'][$i];if($err===UPLOAD_ERR_NO_FILE)continue;if($err!==UPLOAD_ERR_OK)throw new \InvalidArgumentException('Nahrání obrázku selhalo.');$tmp=$_FILES['images']['tmp_name'][$i];$info=@getimagesize($tmp);$ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
            $types=['image/jpeg'=>['jpg','jpeg'],'image/png'=>['png'],'image/webp'=>['webp']];
            if(!is_uploaded_file($tmp)||filesize($tmp)>15*1024*1024||!$info||$info[0]*$info[1]>40000000||!in_array($ext,$types[$info['mime']]??[],true))throw new \InvalidArgumentException('Povoleny jsou JPG, PNG a WebP do 15 MB a 40 megapixelů.');
            $validated[]=[$tmp,$ext];
        }
        $ordered=[];$i=0;foreach($p->craft_images as $img){$index=$i++;if(isset($_POST['remove'][$index])){$p->craft_images->delete($img);continue;}$img->description=mb_substr((string)($_POST['alt'][$index]??''),0,250);$ordered[]=['img'=>$img,'order'=>$p->template->name==='craft_section'?($index===(int)($_POST['selected_image']??0)?-1:$index):(int)($_POST['order'][$index]??$index)];}
        // Pagefiles::remove queues the physical image for deletion; sort in place instead.
        foreach($ordered as $row)$row['img']->set('_craftOrder',$row['order']);
        $p->craft_images->sort('_craftOrder');
        foreach($validated as [$tmp,$ext]){$dest=Craft::storage().'image-'.bin2hex(random_bytes(10)).'.'.$ext;move_uploaded_file($tmp,$dest);try{$p->craft_images->add($dest);}finally{if(is_file($dest))unlink($dest);}}
        $p->save('craft_images');
    }
    private function collection(string $template):string {
        $isRef=$template==='craft_reference';$id=(int)($_GET['id']??0);$create=isset($_GET['new']);
        if(!$id&&!$create){
            $settingsPage=Craft::page('nastaveni');$settings=Craft::data($settingsPage);
            $key=$isRef?'references_enabled':'services_enabled';$label=$isRef?'Zobrazit sekci Realizace':'Zobrazit sekci Naše služby';
            if($_SERVER['REQUEST_METHOD']==='POST'){
                if(($_POST['save_labels']??'')==='1'){$this->saveLabels($isRef?'references':'services',$settings);Craft::put($settingsPage,$settings);$this->saved();}
                $settings[$key]=isset($_POST[$key]);Craft::put($settingsPage,$settings);
                if(($_POST['visibility_autosave']??'')==='1'){
                    header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
                    echo json_encode(['saved'=>true,'enabled'=>$settings[$key]]);exit;
                }
                $this->saved();
            }
            $out=str_replace('class="craft-admin-form"','class="craft-admin-form craft-visibility-form"',$this->form()).$this->field($key,$label,$settings[$key]??true,'checkbox').'<span class="craft-save-status" role="status" aria-live="polite"></span><noscript>'.$this->button('Uložit viditelnost sekce').'</noscript></form>';
            $out.='<details class="craft-section-labels"><summary>Doplňkové texty sekce</summary>'.$this->form().'<input type="hidden" name="save_labels" value="1">'.$this->labelFields($isRef?'references':'services',$settings).$this->button().'</form></details>';
            $out.='<p><a class="ui-button" href="?new=1">Přidat '.($isRef?'referenci':'službu').'</a></p><div class="craft-edit-list">';foreach(Craft::items($template) as $p){$d=Craft::data($p);$out.='<a href="?id='.$p->id.'"><strong>'.$this->esc($p->title).'</strong><span>'.(($d['enabled']??true)?'Zobrazeno':'Skryto').' · pořadí '.$p->sort.' →</span></a>';}return $out.'</div>';
        }
        $p=$id?$this->pages->get($id):new Page();if($id&&$p->template->name!==$template)throw new \InvalidArgumentException('Položka nebyla nalezena.');
        $d=$id?Craft::data($p):['enabled'=>true,'text'=>'','location'=>''];
        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(isset($_POST['delete'])&&$id){$this->pages->delete($p,true);$this->session->redirect($this->page->url,303);}
            $title=$this->text('title',200);if($title==='')throw new \InvalidArgumentException('Vyplňte název.');
            if(!$id){$p->template=$template;$p->parent=$this->pages->get('template=craft_container,name='.($isRef?'reference':'sluzby'));$p->name=$this->sanitizer->pageName($title,true).'-'.bin2hex(random_bytes(3));}
            $p->of(false);$p->title=$title;$p->sort=max(0,(int)($_POST['sort']??0));$p->save();$this->saveBody($d);$d['location']=$this->text('location',200);$d['enabled']=isset($_POST['enabled']);$this->images($p);Craft::put($p,$d);$this->saved((int)$p->id);
        }
        return $this->backLink($isRef?'Zpět na reference':'Zpět na služby').$this->form().$this->field('title','Název',$id?$p->title:'').$this->richField('Popis',$d).($isRef?$this->field('location','Místo realizace (nepovinné)',$d['location']):'').$this->field('sort','Pořadí',$id?$p->sort:0,'number').$this->field('enabled','Zobrazit položku',$d['enabled'],'checkbox').($id?$this->imageFields($p):'<p>Fotografie přidáte po prvním uložení.</p>').$this->button().($id?'<hr><label><input type="checkbox" name="delete" value="1"> Smazat tuto položku při uložení</label>':'').'</form>';
    }
    private function settings(string $group):string {
        $p=Craft::page('nastaveni');$d=Craft::data($p);$d+=['experience'=>'30+'];
        $contact=['company'=>'Název firmy','phone'=>'Telefon','email'=>'E-mail','address'=>'Adresa','billing'=>'Firemní a fakturační údaje','contact_heading'=>'Nadpis formuláře','contact_text'=>'Text u formuláře','map_url'=>'Adresa vložené mapy (URL iframe z Mapy.com)'];
        $settings=['seo_title'=>'SEO titulek','seo_description'=>'Meta popis','recipients'=>'Příjemci e-mailu (oddělte čárkou)','sms_recipient'=>'Telefon pro SMS (+420…)','privacy'=>'Informace o ochraně osobních údajů'];
        $fields=$group==='contact'?$contact:$settings;
        $checks=$group==='contact'?['show_address'=>'Zobrazit adresu','show_map'=>'Zobrazit mapu']:['sms_enabled'=>'Zapnout SMS upozornění'];
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $next=$d;foreach($fields as $key=>$label)$next[$key]=$this->text($key);foreach($checks as $key=>$label)$next[$key]=isset($_POST[$key]);
            if($group==='contact'){
                $this->saveLabels('contact',$next);
                if(!filter_var($next['email'],FILTER_VALIDATE_EMAIL))throw new \InvalidArgumentException('Zadejte platný kontaktní e-mail.');
                if($next['show_map']&&!preg_match('~^https://(?:frame\.)?(?:mapy\.com|mapy\.cz)/[^\s"<>]*$~',$next['map_url']))throw new \InvalidArgumentException('Vložte HTTPS adresu mapy z Mapy.com nebo Mapy.cz.');
            }else{
                foreach(['brand_font'=>['sans','serif']] as $key=>$allowed){$value=$this->text($key);if(!in_array($value,$allowed,true))throw new \InvalidArgumentException('Vyberte platné písmo.');$next[$key]=$value;}
                foreach(['brand_show_image','brand_show_text'] as $key)$next[$key]=isset($_POST[$key]);
                foreach(['brand_text'=>80,'brand_tagline'=>100] as $key=>$limit)$next[$key]=$this->text($key,$limit);
                unset($next['brand_mark'],$next['brand_mode']);
                $next['form_mode']=$this->text('form_mode')==='simple'?'simple':'quote';$rules=[];foreach(['simple','quote'] as $mode)foreach(Craft::fields($mode) as $key=>$v)$rules[$mode][$key]=['enabled'=>isset($_POST['rules'][$mode][$key]['enabled']),'required'=>isset($_POST['rules'][$mode][$key]['required'])];Craft::validateRules($rules);$next['rules']=$rules;
                $emails=array_filter(preg_split('/[,;\s]+/',$next['recipients']));if(!$emails)throw new \InvalidArgumentException('Zadejte alespoň jednoho příjemce e-mailu.');foreach($emails as $email)if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new \InvalidArgumentException('Zkontrolujte adresy příjemců.');
                if($next['sms_enabled']&&!preg_match('/^\+[1-9]\d{8,14}$/',$next['sms_recipient']))throw new \InvalidArgumentException('Zadejte SMS číslo v mezinárodním formátu, např. +420777123456.');
                $this->images($p);
            }
            Craft::put($p,$next);$this->saved();
        }
        $out=$this->form();foreach($fields as $key=>$label)$out.=$this->field($key,$label,$d[$key]??'',in_array($key,['billing','privacy','contact_text','seo_description'])?'textarea':'text');foreach($checks as $key=>$label)$out.=$this->field($key,$label,$d[$key]??false,'checkbox');
        if($group==='settings'){
            $b=Craft::branding($d,(bool)$p->craft_images->count());
            $out.='<fieldset class="craft-branding"><legend>Logo a text v navigaci</legend><p>Obrázek a text lze zapnout samostatně. Pokud zapnete obojí, obrázek bude vlevo a text vpravo. Vypnutím obou skryjete značku v navigaci. Pro zobrazení obrázku jej nejprve nahrajte níže.</p>';
            $out.=$this->field('brand_show_image','Zobrazit obrázek',$b['brand_show_image'],'checkbox');
            $out.=$this->field('brand_show_text','Zobrazit text',$b['brand_show_text'],'checkbox');
            $out.=$this->field('brand_text','Text loga (prázdné = název firmy, max. 80 znaků)',$d['brand_text']??'');
            $out.=$this->field('brand_tagline','Podtitulek (prázdné = skrýt, max. 100 znaků)',$b['brand_tagline']);
            $out.=$this->select('brand_font','Písmo textu loga',$b['brand_font'],['sans'=>'Moderní – bezpatkové','serif'=>'Klasické – patkové']);
            $out.='<p>Pro obrázkové logo se použije první fotografie níže. Vypnutí obrázku nebo textu zachová uložený obsah. Změny platí pro horní navigaci.</p>'.$this->imageFields($p).'</fieldset>';
        }
        if($group==='settings'){
            $out.='<label>Typ formuláře <select name="form_mode"><option value="simple" '.($d['form_mode']==='simple'?'selected':'').'>Jednoduchý kontakt</option><option value="quote" '.($d['form_mode']==='quote'?'selected':'').'>Poptávka zakázky</option></select></label>';
            foreach(['simple'=>'Jednoduchý kontakt','quote'=>'Poptávka zakázky'] as $mode=>$label){$rules=Craft::rules($d,$mode);$out.='<fieldset><legend>'.$label.'</legend><p>Telefon nebo e-mail musí být zobrazený a povinný.</p>';foreach(Craft::fields($mode) as $key=>[$label,$type])$out.='<div class="craft-rule"><strong>'.$label.'</strong>'.$this->field("rules[$mode][$key][enabled]",'Zobrazit',$rules[$key]['enabled'],'checkbox').$this->field("rules[$mode][$key][required]",'Povinné',$rules[$key]['required'],'checkbox').'</div>';$out.='</fieldset>';}
        }
        if($group==='contact')$out.=$this->labelFields('contact',$d);
        return $out.$this->button().'</form>';
    }
    private function enquiryStatuses():array {
        return Craft::settings()['enquiry_statuses']??['new'=>['label'=>'Nová','color'=>'#39e675'],'done'=>['label'=>'Vyřízená','color'=>'#d9dee3'],'archived'=>['label'=>'Archivovaná','color'=>'#cbd5e1']];
    }
    private function statusChip(string $key):string {
        $s=$this->enquiryStatuses()[$key]??['label'=>'Neznámý stav','color'=>'#d9dee3'];
        $color=preg_match('/^#[0-9a-f]{6}$/i',$s['color'])?$s['color']:'#d9dee3';
        $rgb=sscanf($color,'#%02x%02x%02x');
        $l=array_map(fn($v)=>$v/255<=0.04045?$v/255/12.92:(($v/255+0.055)/1.055)**2.4,$rgb);
        $ink=(0.2126*$l[0]+0.7152*$l[1]+0.0722*$l[2])>0.179?'#000':'#fff';
        return '<span class="craft-enquiry-status" style="background:'.$color.';color:'.$ink.'">'.$this->esc($s['label']).'</span>';
    }
    private function statusSettings():string {
        $statuses=$this->enquiryStatuses();
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $action=$this->text('status_action');if(($_GET['status_action']??'')==='delete')$action='delete';
            if(!in_array($action,['save','add','delete'],true))throw new \InvalidArgumentException('Neplatná akce.');
            if($action==='save')foreach($statuses as $key=>&$status){
                $label=$this->text('status_label_'.$key,60);$color=$this->text('status_color_'.$key);
                if($label===''||!preg_match('/^#[0-9a-f]{6}$/i',$color))throw new \InvalidArgumentException('Vyplňte název a platnou barvu každého stavu.');
                $status=['label'=>$label,'color'=>$color];
            }unset($status);
            $label=$this->text('new_status_label',60);
            if($label!==''){
                $color=$this->text('new_status_color');
                if(!preg_match('/^#[0-9a-f]{6}$/i',$color))throw new \InvalidArgumentException('Vyberte platnou barvu.');
                if(count($statuses)>=30)throw new \InvalidArgumentException('Lze vytvořit nejvýše 30 stavů.');
                $statuses['custom_'.bin2hex(random_bytes(6))]=['label'=>$label,'color'=>$color];
            }
            $replacements=[];
            foreach(array_keys($statuses) as $key){
                if($action!=='delete'||$this->text('delete_status')!==$key)continue;
                if($key==='new')throw new \InvalidArgumentException('Výchozí stav nelze odstranit.');
                $replacement=$this->text('replace_status_'.$key);
                if($replacement===$key||!isset($statuses[$replacement]))throw new \InvalidArgumentException('Zvolte náhradní stav, který neodstraňujete.');
                $replacements[$key]=$replacement;
            }
            if($action==='delete'&&!$replacements)throw new \InvalidArgumentException('Stav nebyl nalezen.');
            $db=Craft::db();$db->beginTransaction();
            try{
                if($replacements)foreach($this->pages->find('template=craft_enquiry') as $enquiry){
                    $entry=Craft::data($enquiry);$replacement=$replacements[$entry['status']??'new']??null;
                    if($replacement!==null){$entry['status']=$replacement;Craft::put($enquiry,$entry);}
                }
                foreach($replacements as $key=>$replacement)unset($statuses[$key]);
                $p=Craft::page('nastaveni');$data=Craft::data($p);$data['enquiry_statuses']=$statuses;Craft::put($p,$data);
                $db->commit();
            }catch(\Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
            $this->session->message('Stavy byly uloženy.');$this->session->redirect($this->page->url.'?statuses=1',303);
        }
        $out=$this->backLink('Zpět na poptávky').'<h2>Stavy poptávek</h2><p>Upravte názvy a barvy nebo přidejte vlastní stav. První stav se přiřazuje novým poptávkám.</p>'.$this->form();
        foreach($statuses as $key=>$status){
            $out.='<fieldset>'.$this->statusChip($key).$this->field('status_label_'.$key,'Název stavu',$status['label']).$this->field('status_color_'.$key,'Barva',$status['color'],'color');
            if($key==='new')$out.='<p class="craft-muted">Výchozí stav pro nové poptávky nelze odstranit. Název a barvu lze upravit.</p>';
            else{
                $options=array_map(fn($s)=>$s['label'],$statuses);unset($options[$key]);
                $out.=$this->select('replace_status_'.$key,'Poptávky převést do stavu','new',$options).'<button class="ui-button" type="submit" name="delete_status" value="'.$this->esc($key).'" formaction="?statuses=1&amp;status_action=delete">Odstranit stav</button>';
            }
            $out.='</fieldset>';
        }
        return $out.'<button class="ui-button" type="submit" name="status_action" value="save">Uložit názvy a barvy</button><fieldset><legend>Přidat stav</legend>'.$this->field('new_status_label','Název nového stavu').$this->field('new_status_color','Barva nového stavu','#38bdf8','color').'<button class="ui-button" type="submit" name="status_action" value="add">+ Přidat stav</button></fieldset></form>';
    }
    private function enquiryTable(array $items):string {
        if(!$items)return '<p>Zatím zde nejsou žádné poptávky.</p>';
        $out='<div class="craft-table"><table><thead><tr><th>Přijato</th><th>Jméno</th><th>Telefon</th><th>Typ</th><th>Stav</th></tr></thead><tbody>';
        foreach($items as $p){$d=Craft::data($p);$out.='<tr><td>'.date('d. m. Y H:i',$p->created).'</td><td><a href="/sprava/poptavky/?id='.$p->id.'">'.$this->esc($p->title).'</a></td><td>'.$this->esc($d['fields']['phone']??'—').'</td><td>'.($d['mode']==='quote'?'Poptávka':'Kontakt').'</td><td>'.$this->statusChip($d['status']??'new').'</td></tr>';}
        return $out.'</tbody></table></div>';
    }
    private function enquiries():string {
        if(isset($_GET['statuses']))return $this->statusSettings();
        $id=(int)($_GET['id']??0);if(!$id){$items=iterator_to_array($this->pages->find('template=craft_enquiry,sort=-created'));if(isset($_GET['failures'])){$ids=Craft::db()->query("SELECT DISTINCT page_id FROM craft_queue WHERE state IN ('failed','uncertain')")->fetchAll(\PDO::FETCH_COLUMN);$items=array_filter($items,fn($p)=>in_array($p->id,$ids));}return '<p><a class="ui-button" href="?statuses=1">Spravovat stavy</a></p>'.$this->enquiryTable($items);}
        $p=$this->pages->get($id);if($p->template->name!=='craft_enquiry')throw new \InvalidArgumentException('Poptávka nebyla nalezena.');$d=Craft::data($p);
        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(isset($_POST['delete'])){Craft::deleteEnquiry($p);$this->session->redirect($this->page->url,303);}
            if(isset($_POST['retry'])){if(!isset($_POST['verified']))throw new \InvalidArgumentException('Před opakováním ověřte, zda upozornění již nepřišlo.');$q=Craft::db()->prepare("UPDATE craft_queue SET state='pending',attempts=0,due=NOW(),detail='' WHERE id=? AND page_id=? AND state IN ('failed','uncertain')");$q->execute([(int)$_POST['retry'],$id]);$this->saved();}
            $status=$this->text('status');if(!isset($this->enquiryStatuses()[$status]))throw new \InvalidArgumentException('Neplatný stav.');
            $expires=$this->text('expires');$format=isset($_POST['expires_european'])?'d.m.Y':'Y-m-d';$date=\DateTimeImmutable::createFromFormat('!'.$format,$expires);if(!$date||$date->format($format)!==$expires)throw new \InvalidArgumentException('Zadejte platné datum ve formátu DD.MM.RRRR.');$expires=$date->format('Y-m-d');
            $reason=$this->text('retention_reason',1000);if($expires>$d['expires']&&$reason==='')throw new \InvalidArgumentException('Pro prodloužení uveďte odůvodnění.');
            $d['status']=$status;$d['expires']=$expires;$d['retention_reason']=$reason;Craft::put($p,$d);$this->saved();
        }
        $out=$this->backLink('Zpět na poptávky').'<div class="craft-enquiry-layout"><section class="craft-enquiry-content" aria-label="Údaje poptávky"><p>Přijato '.date('d. m. Y H:i',$p->created).'</p><dl class="craft-detail">';foreach($d['fields'] as $key=>$value)$out.='<div'.(in_array($key,['message','subject'],true)?' class="craft-detail-wide"':'').'><dt>'.$this->esc(Craft::fields($d['mode'])[$key][0]??$key).'</dt><dd>'.nl2br($this->esc($value)).'</dd></div>';$out.='</dl><h3>Přílohy</h3><ul>';foreach($d['files'] as $i=>$f)$out.='<li><a href="?id='.$id.'&download='.$i.'">'.$this->esc($f['name']).'</a></li>';$out.='</ul><h3>Upozornění</h3>';
        $q=Craft::db()->prepare('SELECT * FROM craft_queue WHERE page_id=?');$q->execute([$id]);foreach($q->fetchAll(\PDO::FETCH_ASSOC) as $job){$label=match($job['state']){'accepted'=>'Předáno poskytovateli','failed','uncertain'=>'Chyba odeslání',default=>'Čeká na odeslání'};$out.='<div class="craft-notice"><strong>'.$this->esc(strtoupper($job['channel']).' · '.$job['recipient']).'</strong><p>'.$label.' · '.$this->esc($job['detail']).'</p>';if(in_array($job['state'],['failed','uncertain']))$out.=$this->form().'<input type="hidden" name="retry" value='.$job['id'].'>'.$this->field('verified','Ověřil/a jsem, že je bezpečné upozornění zopakovat.',false,'checkbox').$this->button('Opakovat odeslání').'</form>';$out.='</div>';}
        $out.='</section><aside class="craft-enquiry-controls" aria-label="Správa poptávky">'.$this->statusChip($d['status']??'new').$this->form().'<label>Stav <select name="status">';foreach(array_map(fn($s)=>$s['label'],$this->enquiryStatuses()) as $v=>$label)$out.='<option value="'.$v.'" '.($d['status']===$v?'selected':'').'>'.$this->esc($label).'</option>';
        return $out.'</select></label>'.'<input type="hidden" name="expires_european" value="1">'.$this->field('expires','Automaticky smazat po datu (DD.MM.RRRR)',(new \DateTimeImmutable($d['expires']))->format('d.m.Y')).$this->field('retention_reason','Odůvodnění prodloužení',$d['retention_reason'],'textarea').$this->button().'</form><hr>'.str_replace('class="craft-admin-form"','class="craft-admin-form craft-delete-enquiry"',$this->form()).'<input type="hidden" name="delete" value="1"><button type="submit" class="craft-danger-button" disabled>Trvale smazat poptávku</button><noscript>Pro potvrzení smazání povolte JavaScript.</noscript></form></aside></div>';
    }
}

