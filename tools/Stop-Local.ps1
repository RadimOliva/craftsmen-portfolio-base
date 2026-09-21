$projectRoot=Split-Path $PSScriptRoot -Parent
foreach($name in @('php','mariadbd')){
 Get-Process -Name $name -ErrorAction SilentlyContinue | Where-Object {$_.Path -and $_.Path.StartsWith((Join-Path $projectRoot '.runtime/').Replace('/','\'),[StringComparison]::OrdinalIgnoreCase)} | Stop-Process
}
