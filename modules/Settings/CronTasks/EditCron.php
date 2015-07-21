<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */

require_once('config/config.php');
require_once('vtlib/Vtiger/Cron.php');
global $mod_strings, $app_strings;
global $theme;
$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";

$smarty = new vtigerCRM_Smarty;
if (isset($_REQUEST['record']) && $_REQUEST['record'] != '') {
	$id = $_REQUEST['record'];
	$cronTask = Vtiger_cron::getInstanceById($id);
	$label = getTranslatedString($cronTask->getName(), $cronTask->getModule());
	$cron_status = $cronTask->getStatus();
	$cron_freq = $cronTask->getFrequency();
	$cron_desc = $cronTask->getDescription();
	$cron = Array();
	$cron['label'] = $label;
	if ($cron_freq / (60 * 60) > 1 && is_int($cron_freq / (60 * 60))) {
		$cron['frequency'] = (int) ($cron_freq / (60 * 60));
		$cron['time'] = 'hour';
	} else {
		$cron['frequency'] = (int) ($cron_freq / 60);
		$cron['time'] = 'min';
	}
	$cron['status'] = $cron_status;
	$cron['description'] = $cron_desc;
	$cron['id'] = $id;

	$current_language = vglobal('current_language');
	$smarty->assign("CRON_DETAILS", $cron);
	$smarty->assign("MOD", return_module_language($current_language, 'CronTasks'));
	$smarty->assign("THEME", $theme);
	$smarty->assign("IMAGE_PATH", $image_path);
	$smarty->assign("APP", $app_strings);
	$smarty->assign("CMOD", $mod_strings);
	$smarty->assign("MIN_CRON_FREQUENCY", getMinimumCronFrequency());
	$smarty->display("modules/CronTasks/EditCron.tpl");
} else {
	header("Location:index.php?module=CronTasks&action=ListCronJobs&directmode=ajax");
}

?>
