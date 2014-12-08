<?php

chdir(dirname(__FILE__) . '/../');
ini_set('session.save_path','session');
require_once('includes/main/WebUI.php');
require_once 'libraries/csrf-magic/csrf-magic.php';

require_once('install/views/Index.php');
require_once('install/models/Ajax.php');
require_once('install/models/Utils.php');
require_once('install/models/ConfigFileUtils.php');
require_once('install/models/InitSchema.php');

Vtiger_Session::init();

$request = new Vtiger_Request($_REQUEST);
if($request->get('ajax') == 'true'){
	$install = new Install_Ajax_Model();
	$install->process($request);
}else{
	$install = new Install_Index_view();
	$install->preProcess($request);
	$install->process($request);
	$install->postProcess($request);
}