<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Calendar_Feed_Action extends Vtiger_BasicAjax_Action
{

	public function process(\App\Request $request)
	{
		try {
			$result = [];

			$start = $request->get('start');
			$end = $request->get('end');
			$type = $request->get('type');
			$userid = $request->get('userid');
			$color = $request->get('color');
			$textColor = $request->get('textColor');

			$actionName = 'Calendar_' . $type . '_ActivityTypes';
			Vtiger_Loader::includeOnce('~modules/Calendar/activityTypes/' . $type . '.php');
			$pullInstance = new $actionName;
			$pullInstance->process($this, $request, $start, $end, $result, $userid, $color, $textColor);
			echo json_encode($result);
		} catch (Exception $ex) {
			echo $ex->getMessage();
		}
	}

	public function getGroupsIdsForUsers($userId)
	{
		Vtiger_Loader::includeOnce('~include/utils/GetUserGroups.php');

		$userGroupInstance = new GetUserGroups();
		$userGroupInstance->getAllUserGroups($userId);
		return $userGroupInstance->user_groups;
	}
}
