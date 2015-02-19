<?php
chdir(dirname(__FILE__) . '/../');
ini_set('session.save_path','cache/session');
// Adjust error_reporting favourable to deployment.
ini_set('display_errors','off');version_compare(PHP_VERSION, '5.4.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT); // PRODUCTION
//ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.4.0') <= 0 ? error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);   // DEBUGGING

require_once('include/main/WebUI.php');
require_once 'libraries/csrf-magic/csrf-magic.php';
require_once('install/views/Index.php');
require_once('install/models/Utils.php');
require_once('install/models/ConfigFileUtils.php');
require_once('install/models/InitSchema.php');

Vtiger_Session::init();

$request = new Vtiger_Request($_REQUEST);
$install = new Install_Index_view();
$install->preProcess($request);
$install->process($request);
$install->postProcess($request);