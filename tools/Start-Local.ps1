$ErrorActionPreference='Stop'
$projectRoot=Split-Path $PSScriptRoot -Parent
$php=Join-Path $projectRoot '.runtime/php/php.exe'
$db=Join-Path $projectRoot '.runtime/mariadb-11.4.10-winx64/bin/mariadbd.exe'
if(!(Test-Path -LiteralPath (Join-Path $projectRoot 'storage/local.php'))){throw 'Run tools/bootstrap.php first.'}
$listener=Get-NetTCPConnection -LocalPort 3307 -State Listen -ErrorAction SilentlyContinue
if(!$listener){Start-Process -FilePath $db -ArgumentList "--defaults-file=$projectRoot/.runtime/db/my.ini",'--bind-address=127.0.0.1','--port=3307','--console' -WorkingDirectory $projectRoot -WindowStyle Hidden -RedirectStandardOutput "$projectRoot/.runtime/db-out.log" -RedirectStandardError "$projectRoot/.runtime/db-error.log"}
if(!(Get-NetTCPConnection -LocalPort 8080 -State Listen -ErrorAction SilentlyContinue)){
 $p=Start-Process -FilePath $php -ArgumentList '-S','127.0.0.1:8080','tools/router.php' -WorkingDirectory $projectRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput "$projectRoot/.runtime/web-out.log" -RedirectStandardError "$projectRoot/.runtime/web-error.log"
 $p.Id | Set-Content -LiteralPath "$projectRoot/.runtime/web.pid"
}
Write-Output 'Local site: http://127.0.0.1:8080/ | Admin: /sprava/ | Mail capture: storage/mail/'
Write-Output 'Run tools/worker.php periodically for queued delivery and retention. Credentials: storage/LOCAL_ACCESS.md'
