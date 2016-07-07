<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
chdir(dirname(__FILE__) . '/../');
require 'include/main/WebUI.php';
require('install/models/InitSchema.php');

$db = PearDatabase::getInstance();
$initSchema = new Install_InitSchema_Model($db);
$initSchema->initialize();
