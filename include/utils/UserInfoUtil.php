<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/utils.php';
require_once 'include/runtime/Globals.php';
require_once 'include/runtime/Cache.php';

/**
 * Function to get the permitted module id Array with presence as 0
 * @global Users $current_user
 * @return Array Array of accessible tabids.
 */
function getPermittedModuleIdList()
{
	$current_user = vglobal('current_user');
	$permittedModules = [];
	require('user_privileges/user_privileges_' . $current_user->id . '.php');
	include('user_privileges/tabdata.php');

	if ($is_admin === false && $profileGlobalPermission[1] == 1 &&
		$profileGlobalPermission[2] == 1) {
		foreach ($tab_seq_array as $tabid => $seq_value) {
			if ($seq_value === 0 && $profileTabsPermission[$tabid] === 0) {
				$permittedModules[] = ($tabid);
			}
		}
	} else {
		foreach ($tab_seq_array as $tabid => $seq_value) {
			if ($seq_value === 0) {
				$permittedModules[] = ($tabid);
			}
		}
	}
	$homeTabid = \App\Module::getModuleId('Home');
	if (!in_array($homeTabid, $permittedModules)) {
		$permittedModules[] = $homeTabid;
	}
	return $permittedModules;
}
