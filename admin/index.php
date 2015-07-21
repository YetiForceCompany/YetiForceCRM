<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/

$Start_time = microtime(true);
chdir(__DIR__ . '/../');
define('ADMIN_ROOT', __DIR__ );
include_once 'include/RequirementsValidation.php';
include_once 'admin/include/WebUI.php';

session_save_path(ADMIN_ROOT.'/cache/session');

$webUI = new Admin_WebUI();
$webUI->process(new Vtiger_Request($_REQUEST, $_REQUEST));
