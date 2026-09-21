<?php
if(PHP_SAPI!=='cli')exit(1);
chdir(dirname(__DIR__));require 'index.php';require 'site/templates/lib/Worker.php';
echo json_encode(ProcessWire\Worker::run(),JSON_UNESCAPED_UNICODE).PHP_EOL;
