<?php namespace ProcessWire;
class WireMailCraft extends WireMail implements Module {
    public static function getModuleInfo(){return ['title'=>'Craft SMTP / local capture','version'=>100,'summary'=>'Webglobe SMTP with a local capture transport.','singular'=>false];}
    public function ___send(){
        $c=$this->wire('config')->craft;
        if($c['mailTransport']==='capture') {
            $dir=Craft::storage().'mail/';if(!is_dir($dir))mkdir($dir,0700,true);
            $file=$dir.date('Ymd-His').'-'.bin2hex(random_bytes(6)).'.json';
            if(file_put_contents($file,json_encode($this->mail,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))===false)throw new \RuntimeException('Cannot capture email');
            return count($this->mail['to']);
        }
        if(empty($c['smtpUser'])||empty($c['smtpPassword']))throw new \RuntimeException('SMTP není nastaveno.',401);
        require_once dirname(__DIR__,3).'/vendor/autoload.php';
        $mail=new \PHPMailer\PHPMailer\PHPMailer(true);$mail->isSMTP();$mail->Host=$c['smtpHost'];$mail->Port=(int)$c['smtpPort'];$mail->SMTPAuth=true;
        $mail->Username=$c['smtpUser'];$mail->Password=$c['smtpPassword'];$mail->SMTPSecure=$mail->Port===465?'ssl':'tls';$mail->CharSet='UTF-8';$mail->Timeout=15;
        $mail->setFrom($this->mail['from']?:$c['mailFrom'],$this->mail['fromName']??'');
        foreach($this->mail['to'] as $to)$mail->addAddress($to);
        if(!empty($this->mail['replyTo']))$mail->addReplyTo($this->mail['replyTo']);
        $mail->Subject=$this->mail['subject'];$mail->Body=$this->mail['body'];
        try{$mail->send();}catch(\PHPMailer\PHPMailer\Exception $e){
            $smtpError=$mail->getSMTPInstance()->getError();$code=(int)($smtpError['smtp_code']??0);
            // An explicit SMTP refusal is distinguishable from an ambiguous connection failure.
            throw new \RuntimeException('SMTP odeslání selhalo.',in_array($code,[450,451,452,535,550,551,552,553,554],true)?$code:0);
        }
        return count($this->mail['to']);
    }
}
