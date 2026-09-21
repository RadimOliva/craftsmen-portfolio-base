<?php namespace ProcessWire;
// Keep the normal ProcessWire authentication/admin shell, with client-safe landing pages.
if($user->isLoggedin()&&!$user->isSuperuser()&&$user->hasRole('klient')) {
    if($page->id===2)$session->redirect('/sprava/prehled/',302);
    $allowed=['prehled','obsah-webu','sluzby','reference','poptavky','kontakt','nastaveni','login','profile'];
    $requestedPath=rawurldecode(parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH));
    $requestedSection=explode('/',trim($requestedPath,'/'))[1]??'';
    if(str_starts_with($requestedPath,'/sprava/')&&$requestedSection!==''&&!in_array($requestedSection,$allowed,true)){
        http_response_code(403);header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="cs"><meta charset="utf-8"><title>Přístup odepřen</title><h1>K této části nemáte přístup.</h1><a href="/sprava/prehled/">Zpět na přehled</a></html>';exit;
    }
}
