<?php
$rootDirectory = dirname(__FILE__) . '/../';
chdir($rootDirectory);

// Adjust error_reporting favourable to deployment.
include_once 'include/RequirementsValidation.php';
require_once('include/main/WebUI.php');
session_save_path($rootDirectory . 'cache/session/');
require_once('libraries/csrf-magic/csrf-magic.php');
require_once('config/csrf_config.php');
require_once('install/views/Index.php');
require_once('install/models/Utils.php');
require_once('install/models/ConfigFileUtils.php');
require_once('install/models/InitSchema.php');

$log = LoggerManager::getLogger('INSTALL');
vglobal('log', $log);
Vtiger_Session::init();

$request = new Vtiger_Request($_REQUEST);
$install = new Install_Index_view();
$install->preProcess($request);
$install->process($request);
$install->postProcess($request);
