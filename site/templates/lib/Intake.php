<?php namespace ProcessWire;

final class Intake {
    public static function validate(array $input, array $settings): array {
        $mode=$settings['form_mode']==='simple'?'simple':'quote'; $rules=Craft::rules($settings,$mode); $data=[]; $errors=[];
        foreach(Craft::fields($mode) as $key=>[$label,$type]) {
            if(!$rules[$key]['enabled'] || $key==='files') continue;
            $value=is_string($input[$key]??null)?trim($input[$key]):'';
            if($rules[$key]['required'] && $value==='') $errors[$key]="Vyplňte pole $label.";
            $limit=$key==='message'?10000:($key==='address'?500:200);
            if(mb_strlen($value)>$limit) $errors[$key]="Pole $label je příliš dlouhé (nejvýše $limit znaků).";
            if($key==='email' && $value!=='' && !filter_var($value,FILTER_VALIDATE_EMAIL)) $errors[$key]='Zadejte platnou e-mailovou adresu.';
            if($key==='phone' && $value!=='' && !preg_match('/^\+?[0-9 ()-]{9,25}$/',$value)) $errors[$key]='Zadejte platné telefonní číslo.';
            if($key==='category' && $value!=='' && !in_array($value,Craft::categories(),true)) $errors[$key]='Vyberte platný typ práce.';
            $data[$key]=$value;
        }
        return [$data,$errors];
    }
    public static function files(array $upload, bool $required): array {
        $out=[]; $total=0; $names=$upload['name']??[];
        if(!is_array($names)) throw new \InvalidArgumentException('Neplatný formát příloh.');
        foreach($names as $i=>$name) {
            $err=$upload['error'][$i]??UPLOAD_ERR_NO_FILE;
            if($err===UPLOAD_ERR_NO_FILE) continue;
            if($err!==UPLOAD_ERR_OK) throw new \InvalidArgumentException('Přílohu se nepodařilo nahrát. Limit je 10 MB na soubor.');
            $tmp=$upload['tmp_name'][$i]; $size=filesize($tmp); $total+=$size;
            if(count($out)>=5 || $size>10*1024*1024 || $total>25*1024*1024) throw new \InvalidArgumentException('Nejvýše 5 souborů, 10 MB na soubor a 25 MB celkem.');
            if(!is_uploaded_file($tmp)) throw new \InvalidArgumentException('Neplatná příloha.');
            $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($tmp); $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
            $allowed=['image/jpeg'=>['jpg','jpeg'],'image/png'=>['png'],'image/webp'=>['webp'],'application/pdf'=>['pdf']];
            if(!isset($allowed[$mime]) || !in_array($ext,$allowed[$mime],true)) throw new \InvalidArgumentException('Povoleny jsou pouze JPG, PNG, WebP a PDF. HEIC zatím nepodporujeme.');
            if(str_starts_with($mime,'image/')) { $info=@getimagesize($tmp); if(!$info || $info[0]*$info[1]>40000000) throw new \InvalidArgumentException('Obrázek nelze zpracovat nebo je větší než 40 megapixelů.'); }
            $out[]=['tmp'=>$tmp,'stored'=>bin2hex(random_bytes(24)),'name'=>mb_substr(basename(str_replace('\\','/',$name)),0,160),'mime'=>$mime,'size'=>$size];
        }
        if($required&&!$out) throw new \InvalidArgumentException('Přiložte alespoň jeden soubor.');
        return $out;
    }
    public static function submit(array $settings): array {
        $session=wire('session'); $db=Craft::db(); $token=$_POST['submission_token']??'';
        if(!is_string($token)||!preg_match('/^[a-f0-9]{64}$/',$token)||(!hash_equals((string)$session->craftToken,$token)&&!hash_equals((string)$session->craftLastToken,$token))||!$session->CSRF->hasValidToken()) return ['_form'=>'Platnost formuláře vypršela. Obnovte stránku a zkuste to znovu.'];
        $q=$db->prepare('SELECT page_id FROM craft_receipts WHERE token=?');$q->execute([$token]); if($q->fetchColumn()) return [];
        if(!empty($_POST['website']) || time()-(int)$session->craftStarted<2) return ['_form'=>'Odeslání se nezdařilo. Vyčkejte chvíli a zkuste to znovu.'];
        $ip=$_SERVER['REMOTE_ADDR']??'local'; $bucket=hash_hmac('sha256',$ip.'|'.date('YmdH'),wire('config')->craft['rateSalt']);
        $q=$db->prepare('INSERT INTO craft_rate VALUES(?,1,DATE_ADD(NOW(), INTERVAL 2 HOUR)) ON DUPLICATE KEY UPDATE hits=hits+1');$q->execute([$bucket]);
        $q=$db->prepare('SELECT hits FROM craft_rate WHERE bucket=?');$q->execute([$bucket]);if((int)$q->fetchColumn()>10)return ['_form'=>'Bylo odesláno příliš mnoho požadavků. Zkuste to prosím později nebo nám zavolejte.'];
        [$data,$errors]=self::validate($_POST,$settings); $files=[];
        $r=Craft::rules($settings,$settings['form_mode']);
        if($settings['form_mode']==='quote'&&$r['files']['enabled'])try{$files=self::files($_FILES['files']??[],(bool)$r['files']['required']);}catch(\InvalidArgumentException $e){$errors['files']=$e->getMessage();}
        if($errors)return $errors;
        $moved=[];
        try {
            $db->beginTransaction();
            // Claim the token inside the same transaction as the ProcessWire enquiry and queue.
            $q=$db->prepare('INSERT INTO craft_receipts VALUES(?,0,NOW())');$q->execute([$token]);
            $dir=Craft::storage().'uploads/';if(!is_dir($dir))mkdir($dir,0700,true);
            foreach($files as &$file){if(!move_uploaded_file($file['tmp'],$dir.$file['stored']))throw new \RuntimeException('Upload move failed');$moved[]=$dir.$file['stored'];unset($file['tmp']);}unset($file);
            $p=new Page();$p->template='craft_enquiry';$p->parent=wire('pages')->get('template=craft_container,name=poptavky');$p->name='enquiry-'.bin2hex(random_bytes(10));$p->title=$data['name']??'Poptávka';$p->save();
            Craft::put($p,['fields'=>$data,'files'=>$files,'mode'=>$settings['form_mode'],'status'=>'new','received'=>date('c'),'expires'=>date('Y-m-d',strtotime('+12 months')),'retention_reason'=>'']);
            $q=$db->prepare('UPDATE craft_receipts SET page_id=? WHERE token=?');$q->execute([$p->id,$token]);
            $recipients=array_unique(array_filter(array_map('trim',preg_split('/[,;\s]+/',$settings['recipients']??''))));
            $enqueue=$db->prepare("INSERT INTO craft_queue(page_id,channel,recipient,due) VALUES(?,?,?,NOW())");
            foreach($recipients as $recipient) if(filter_var($recipient,FILTER_VALIDATE_EMAIL))$enqueue->execute([$p->id,'email',$recipient]);
            if($settings['sms_enabled']??false)$enqueue->execute([$p->id,'sms',$settings['sms_recipient']]);
            $db->commit();return [];
        } catch(\Throwable $e) {
            if($db->inTransaction())$db->rollBack();foreach($moved as $path)if(is_file($path))unlink($path);
            $q=$db->prepare('SELECT page_id FROM craft_receipts WHERE token=?');$q->execute([$token]);if($q->fetchColumn())return [];
            wire('log')->save('craft-errors','Submission failed: '.$e->getCode());
            return ['_form'=>'Poptávku se nepodařilo uložit. Zkuste to znovu nebo nám zavolejte.'];
        }
    }
}
