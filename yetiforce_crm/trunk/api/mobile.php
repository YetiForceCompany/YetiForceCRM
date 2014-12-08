<?php
require_once 'libraries/restler/restler.php';
require_once 'config/config.php';
ini_set('error_log',$root_directory.'logs/mobile.log');

spl_autoload_register('spl_autoload');
$r = new Restler();
$r->addAPIClass('CallHistory');
$r->handle();