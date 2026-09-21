<?php
if (PHP_SAPI !== 'cli') exit(1);
chdir(dirname(__DIR__));
if (is_file('storage/local.php')) { fwrite(STDERR, "Already configured. Refusing to overwrite.\n"); exit(1); }
$adminPass = bin2hex(random_bytes(12));
$clientPass = bin2hex(random_bytes(12));
$local = [
 'dbHost'=>'127.0.0.1', 'dbPort'=>3307, 'dbName'=>'craftsmen', 'dbUser'=>'craftsmen',
 'dbPass'=>bin2hex(random_bytes(20)), 'salt'=>bin2hex(random_bytes(32)), 'tableSalt'=>bin2hex(random_bytes(20)),
 'hosts'=>['127.0.0.1:8080','localhost:8080'], 'baseUrl'=>'http://127.0.0.1:8080',
 'mode'=>'local', 'mailTransport'=>'capture', 'smsTransport'=>'mock',
 'mailFrom'=>'web@remesla.example', 'smtpHost'=>'mail.webglobe.cz', 'smtpPort'=>587,
 'smtpUser'=>'', 'smtpPassword'=>'', 'smsApiKey'=>'', 'rateSalt'=>bin2hex(random_bytes(32))
];
$installConfig=getenv('CRAFT_INSTALL_CONFIG');
if($installConfig){
 $override=json_decode(file_get_contents($installConfig),true,512,JSON_THROW_ON_ERROR);
 foreach(['dbHost','dbPort','dbName','dbUser','dbPass','hosts','baseUrl'] as $key)if(array_key_exists($key,$override))$local[$key]=$override[$key];
}else{
 $name=getenv('CRAFT_DB_NAME')?:'craftsmen';if(!preg_match('/^[a-z0-9_]+$/',$name))throw new RuntimeException('Invalid database name');
 $local['dbName']=$name;$local['dbUser']=$name;
 $root = new PDO('mysql:host=127.0.0.1;port=3307', 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 $root->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
 $root->exec("CREATE USER '$name'@'127.0.0.1' IDENTIFIED BY " . $root->quote($local['dbPass']));
 $root->exec("GRANT ALL ON `$name`.* TO '$name'@'127.0.0.1'");
}
$dsn='mysql:host='.$local['dbHost'].';port='.$local['dbPort'].';dbname='.$local['dbName'];
$db = new PDO($dsn, $local['dbUser'], $local['dbPass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if($db->query('SHOW TABLES')->fetchColumn())throw new RuntimeException('Database is not empty. Refusing to overwrite.');
require 'wire/core/WireDatabaseBackup.php';
$backup = new ProcessWire\WireDatabaseBackup();
$backup->setDatabase($db);
$replace = ['ENGINE=MyISAM'=>'ENGINE=InnoDB','CHARSET=utf8;'=>'CHARSET=utf8mb4;','(255)'=>'(191)','(250)'=>'(191)'];
if (!$backup->restoreMerge('wire/core/install.sql','site/install/install.sql',['findReplaceCreateTable'=>$replace])) throw new RuntimeException(implode('; ', $backup->errors()));
file_put_contents('storage/local.php', "<?php\nreturn " . var_export($local,true) . ";\n");
foreach (['cache','logs','sessions','files'] as $dir) if (!is_dir("site/assets/$dir")) mkdir("site/assets/$dir",0750,true);
require 'index.php';
$wire->users->setCurrentUser($wire->users->get(41));
$admin=$wire->users->get(41); $admin->name='developer'; $admin->pass=$adminPass; $admin->email='developer@example.test'; $admin->save();
$wire->pages->get(2)->setAndSave('name','sprava');
require 'tools/seed.php';
file_put_contents('storage/LOCAL_ACCESS.md', "# Local access only\n\nWebsite: http://127.0.0.1:8080/\nCMS: http://127.0.0.1:8080/sprava/\n\nDeveloper: developer\nPassword: $adminPass\n\nClient: klient\nPassword: $clientPass\n\nNever package this file for a client.\n");
file_put_contents('site/assets/installed.php', '<?php // Installed via CLI');
echo "Installed. Unique local credentials: storage/LOCAL_ACCESS.md\n";
