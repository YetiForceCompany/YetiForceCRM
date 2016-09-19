<?php
/* * *******************************************************************************

 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ****************************************************************************** */
require_once('include/database/PearDatabase.php');
require_once('include/utils/utils.php');
global $mod_strings, $list_max_entries_per_page, $currentModule, $theme;

$smarty = new vtigerCRM_Smarty();

$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";


$smarty->assign("SESSION_WHERE", $_SESSION['export_where']);
$smarty->assign('MOD', $mod_strings);
$smarty->assign("THEME", $theme_path);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("MODULE", $currentModule);
$smarty->assign("MODULELABEL", \includes\Language::translate($currentModule));
$smarty->assign("IDSTRING", AppRequest::get('idstring'));
$smarty->assign("EXCLUDED_RECORDS", AppRequest::get('excludedRecords'));
$smarty->assign("PERPAGE", $list_max_entries_per_page);

$current_user = Users_Privileges_Model::getCurrentUserModel();
if (!$current_user->isAdminUser() && (!\includes\Privileges::isPermitted($currentModule, 'Export'))) {
	$smarty->display(vtlib_getModuleTemplate('Vtiger', 'OperationNotPermitted.tpl'));
} else {
	$smarty->display('ExportRecords.tpl');
}
