<?php namespace ProcessWire;
final class Worker {
    public static function run(): array {
        $db=Craft::db();$lock=$db->query("SELECT GET_LOCK('craft_worker',0)")->fetchColumn();if(!$lock)return ['busy'=>true];$done=0;
        try{
            // A process that died during delivery may have sent already: never blindly resend it.
            $db->exec("UPDATE craft_queue SET state='uncertain',detail='Výsledek odeslání není známý. Před opakováním ověřte doručení.' WHERE state='sending'");
            $jobs=$db->query("SELECT * FROM craft_queue WHERE state='pending' AND due<=NOW() ORDER BY id LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
            foreach($jobs as $job){
                $q=$db->prepare("UPDATE craft_queue SET state='sending',attempts=attempts+1 WHERE id=?");$q->execute([$job['id']]);
                $p=wire('pages')->get((int)$job['page_id']);if(!$p->id){$db->exec('DELETE FROM craft_queue WHERE id='.(int)$job['id']);continue;}
                $d=Craft::data($p);$c=wire('config')->craft;
                try{
                    if($job['channel']==='email'){
                        $mail=wire('modules')->get('WireMailCraft');$mail->to($job['recipient'])->from($c['mailFrom'])->subject('Nová poptávka z webu – '.($d['fields']['name']??'Zákazník'));
                        $body="Nová poptávka z webu\n\n";foreach($d['fields'] as $key=>$value)$body.=(Craft::fields($d['mode'])[$key][0]??$key).': '.$value."\n";
                        $body.="\nDetail: ".$c['baseUrl'].'/sprava/poptavky/?id='.$p->id;
                        if(!empty($d['fields']['email']))$mail->replyTo($d['fields']['email']);$mail->body($body);
                        if(!$mail->send())throw new \RuntimeException('Email nebyl přijat.',503);$provider='';$detail=$c['mailTransport']==='capture'?'Místní test: zpráva zachycena.':'';
                    }else{[$provider,$detail]=self::sms($job,$c);}
                    $q=$db->prepare("UPDATE craft_queue SET state='accepted',detail=?,provider_id=? WHERE id=?");$q->execute([$detail,$provider,$job['id']]);$done++;
                }catch(\Throwable $e){
                    $attempt=(int)$job['attempts']+1;$code=(int)$e->getCode();$delays=[60,300,900,3600];
                    // Only explicit temporary rejection is retried. Network ambiguity needs review.
                    $temporary=in_array($code,[429,450,451,452,503],true);
                    $state=$temporary&&$attempt<=4?'pending':($code===0?'uncertain':'failed');
                    $detail=$state==='pending'?'Dočasná chyba. Odeslání se automaticky zopakuje.':($state==='uncertain'?'Výsledek není známý. Ověřte doručení před ručním opakováním.':'Odeslání selhalo. Zkontrolujte nastavení nebo kredit.');
                    $q=$db->prepare('UPDATE craft_queue SET state=?,detail=?,due=? WHERE id=?');$q->execute([$state,$detail,date('Y-m-d H:i:s',time()+($delays[$attempt-1]??3600)),$job['id']]);
                }
            }
            $db->exec('DELETE FROM craft_rate WHERE expires<NOW()');
            // Retention runs once a day; UI displays enquiries expiring within 30 days.
            $stamp=Craft::storage().'retention-day';
            if(!is_file($stamp)||trim(file_get_contents($stamp))!==date('Y-m-d')){
                foreach(Craft::items('craft_enquiry') as $p){$d=Craft::data($p);if(($d['expires']??'9999-01-01')<date('Y-m-d'))Craft::deleteEnquiry($p);}
                file_put_contents($stamp,date('Y-m-d'));
                foreach(glob(Craft::storage().'mail/*.json')?:[] as $f)if(filemtime($f)<time()-30*86400)unlink($f);
            }
        }finally{$db->query("SELECT RELEASE_LOCK('craft_worker')");}
        return ['processed'=>$done];
    }
    private static function sms(array $job,array $c):array {
        if($c['smsTransport']==='mock')return ['mock-'.$job['id'],'Místní test: SMS nebyla odeslána.'];
        if(empty($c['smsApiKey']))throw new \RuntimeException('SMS není nastaveno.',401);
        $ch=curl_init('https://api.smsmngr.com/v2/message');
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15,CURLOPT_HTTPHEADER=>['x-api-key: '.$c['smsApiKey'],'Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode(['body'=>'Nová poptávka z webu. Detail najdete ve správě webu.','to'=>[['phone_number'=>ltrim(preg_replace('/\D/','',$job['recipient']),'0')]]])]);
        $raw=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);
        if($raw===false)throw new \RuntimeException('Neznámý výsledek SMS.');
        $result=json_decode($raw,true);
        if($code>=200&&$code<300&&!empty($result['accepted'][0]['message_id']))return [$result['accepted'][0]['message_id'],''];
        throw new \RuntimeException('SMS nepřijata.',in_array($code,[401,403,400,422,429],true)?$code:0);
    }
}
