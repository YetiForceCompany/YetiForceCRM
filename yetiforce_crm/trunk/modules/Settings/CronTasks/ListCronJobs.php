<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

require_once('vtlib/Vtiger/Cron.php');
require_once ('include/utils/utils.php');

global $theme,$app_strings,$mod_strings,$current_language;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
$smarty = new vtigerCRM_Smarty;
$cronTasks = Vtiger_Cron::listAllActiveInstances(1);
$output = Array();

foreach ($cronTasks as $cronTask) {
	$out = Array();
	$cron_id = $cronTask->getId();
	$cron_mod = $cronTask->getName();
	$cron_freq = $cronTask->getFrequency();
	$cron_st = $cronTask->getStatus();
	if($cronTask->getLastStart() != 0) {
		$start_ts = $cronTask->getLastStart();
		$end_ts = time();
 	    $cron_started = dateDiffAsString($start_ts, $end_ts);
	}
	else {
		$cron_started = '';
	}
	if($cronTask->getLastEnd() != 0) {
		$start_ts = $cronTask->getLastEnd();
		$end_ts = time();
 	    $cron_end = dateDiffAsString($start_ts, $end_ts);
	}
	else {
		$cron_end = '';
	}
	$out ['cronname'] = getTranslatedString($cron_mod,$cronTask->getModule());

	$out['hours'] = str_pad((int)(($cron_freq/(60*60))),2,0,STR_PAD_LEFT);
	$out['mins'] =str_pad((int)(($cron_freq%(60*60))/60),2,0,STR_PAD_LEFT);
	$out ['id'] = $cron_id;
	$out ['status'] = $cron_st;
	$out['laststart']= $cron_started;
	$out['lastend'] =$cron_end;
	if($out['status'] == Vtiger_Cron::$STATUS_DISABLED )
		$out['status'] = $mod_strings['LBL_INACTIVE'];
	elseif($out['status'] == Vtiger_Cron::$STATUS_ENABLED)
		$out['status'] = $mod_strings['LBL_ACTIVE'];
	else
		$out['status'] = $mod_strings['LBL_RUNNING'];

	$output [] = $out;
}

$smarty->assign("CRON",$output);
$smarty->assign("MOD", return_module_language($current_language,'CronTasks'));
$smarty->assign("MIN_CRON_FREQUENCY",getMinimumCronFrequency());
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("APP", $app_strings);
$smarty->assign("CMOD", $mod_strings);

if($_REQUEST['directmode'] != '')
	$smarty->display("modules/CronTasks/CronContents.tpl");
else {
	$smarty->display("modules/CronTasks/Cron.tpl");
}
?>
