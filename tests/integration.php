<?php namespace ProcessWire;
if(PHP_SAPI!=='cli')exit(1);
chdir(dirname(__DIR__));require 'index.php';require 'site/templates/lib/Intake.php';require 'site/templates/lib/Worker.php';
if($config->craft['mode']!=='local')throw new \RuntimeException('Local tests only');
$database->exec('DELETE FROM craft_rate'); // Disposable local test rate counters only.
$count=0;$assert=function($v,$message)use(&$count){if(!$v)throw new \RuntimeException($message);$count++;echo "PASS $message\n";};
$settings=Craft::settings();
$assert(ProcessWire::versionMajor===3&&ProcessWire::versionMinor===0&&ProcessWire::versionRevision===259,'Pinned stable ProcessWire');
$client=$users->get('klient');$assert($client->hasPermission('craft-manage'),'Client can access website management');
foreach(['superuser','page-edit','module-admin','template-admin','field-admin','user-admin'] as $permission)$assert(!$client->hasPermission($permission),'Client denied '.$permission);
foreach(['simple','quote'] as $mode){$settings['form_mode']=$mode;[$data,$errors]=Intake::validate([], $settings);$assert(isset($errors['name'],$errors['phone'],$errors['email'],$errors['subject'],$errors['message']),$mode.' requires default fields');$assert(!isset($errors['files']),$mode.' files optional');}
$rules=$settings['rules'];$rules['simple']['phone']['required']=false;$rules['simple']['email']['enabled']=false;$blocked=false;try{Craft::validateRules($rules);}catch(\InvalidArgumentException){$blocked=true;}$assert($blocked,'Cannot disable required reply methods');
$settings['form_mode']='quote';$valid=['name'=>'Test','phone'=>'+420 777 111 222','email'=>'test@example.test','subject'=>'Test','message'=>'Zpráva','address'=>'Praha','category'=>'Jiné'];
[$data,$errors]=Intake::validate($valid,$settings);$assert(!$errors,'Valid enquiry accepted');$valid['email']='not-email';$valid['category']='injected';[, $errors]=Intake::validate($valid,$settings);$assert(isset($errors['email'],$errors['category']),'Invalid email/category rejected');
$engine=$database->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND ENGINE IS NOT NULL AND ENGINE<>'InnoDB'")->fetchColumn();$assert((int)$engine===0,'All persistence tables transactional');
$users->setCurrentUser($users->get(41));$test=new Page();$test->template='craft_enquiry';$test->parent=$pages->get('template=craft_container,name=poptavky');$test->name='test-'.bin2hex(random_bytes(8));$test->title='Automated disposable test';$test->save();Craft::put($test,['fields'=>['name'=>'Test'],'files'=>[],'mode'=>'simple','status'=>'new','expires'=>date('Y-m-d',strtotime('+12 months')),'retention_reason'=>'']);
$original=$config->craft;
try{
 $enqueue=$database->prepare('INSERT INTO craft_queue(page_id,channel,recipient,due) VALUES(?,?,?,NOW())');$enqueue->execute([$test->id,'email','test@example.test']);
 $config->craft=array_merge($original,['mailTransport'=>'smtp','smtpUser'=>'','smtpPassword'=>'']);Worker::run();
 $q=$database->prepare('SELECT state FROM craft_queue WHERE page_id=?');$q->execute([$test->id]);$assert($q->fetchColumn()==='failed','Missing SMTP credentials produce actionable failure');$assert($pages->get($test->id)->id>0,'Enquiry survives notification failure');
 $config->craft=$original;$database->exec("UPDATE craft_queue SET state='pending',attempts=0,due=NOW() WHERE page_id=".$test->id);Worker::run();$q->execute([$test->id]);$assert($q->fetchColumn()==='accepted','Capture transport accepts queued email');
 $duplicate=false;try{$enqueue->execute([$test->id,'email','test@example.test']);}catch(\PDOException){$duplicate=true;}$assert($duplicate,'Duplicate notification recipient/job blocked');
 $database->exec("UPDATE craft_queue SET state='sending' WHERE page_id=".$test->id);Worker::run();$q->execute([$test->id]);$assert($q->fetchColumn()==='uncertain','Interrupted delivery is not blindly resent');
}finally{$config->craft=$original;Craft::deleteEnquiry($test);}
$expired=new Page();$expired->template='craft_enquiry';$expired->parent=$pages->get('template=craft_container,name=poptavky');$expired->name='expiry-test-'.bin2hex(random_bytes(8));$expired->title='Disposable retention test';$expired->save();
$stored=bin2hex(random_bytes(24));$path=Craft::storage().'uploads/'.$stored;file_put_contents($path,'Disposable attachment');
Craft::put($expired,['fields'=>['name'=>'Retention test'],'files'=>[['stored'=>$stored,'name'=>'test.pdf','mime'=>'application/pdf']],'mode'=>'quote','status'=>'archived','expires'=>date('Y-m-d',strtotime('-1 day')),'retention_reason'=>'']);
$expiredId=$expired->id;$stamp=Craft::storage().'retention-day';$oldStamp=is_file($stamp)?file_get_contents($stamp):null;
try{
 file_put_contents($stamp,'2000-01-01');Worker::run();
 $q=$database->prepare('SELECT COUNT(*) FROM pages WHERE id=?');$q->execute([$expiredId]);$assert((int)$q->fetchColumn()===0,'Expired archived enquiry is deleted');
 $assert(!is_file($path),'Retention removes private attachment');
}finally{if($oldStamp!==null)file_put_contents($stamp,$oldStamp);if(is_file($path))unlink($path);$remaining=$pages->get($expiredId);if($remaining->id&&$database->query('SELECT COUNT(*) FROM pages WHERE id='.(int)$expiredId)->fetchColumn())Craft::deleteEnquiry($remaining);}
echo "$count checks passed.\n";
