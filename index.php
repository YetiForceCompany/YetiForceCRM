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

//Overrides GetRelatedList : used to get related query
//TODO : Eliminate below hacking solution

$Start_time = microtime(true);
define('YF_ROOT', __DIR__);
include_once 'include/RequirementsValidation.php';
include_once 'include/Webservices/Relation.php';
include_once 'include/main/WebUI.php';
include_once 'vtlib/Vtiger/Module.php';

session_save_path(YF_ROOT.'/cache/session');

$webUI = new Vtiger_WebUI();
$webUI->process(new Vtiger_Request($_REQUEST, $_REQUEST));
