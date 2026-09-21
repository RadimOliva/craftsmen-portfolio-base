<?php namespace ProcessWire;
if ($page->name === 'ochrana-osobnich-udaju') require __DIR__ . '/privacy-view.php';
else { http_response_code(404); echo 'Stránka nebyla nalezena.'; }
