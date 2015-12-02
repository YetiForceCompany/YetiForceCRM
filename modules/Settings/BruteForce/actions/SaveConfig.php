<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_BruteForce_SaveConfig_Action extends Settings_Vtiger_Basic_Action
{

	public function process(Vtiger_Request $request)
	{
		$adb = PearDatabase::getInstance();
		$number = $request->get('number');
		$timelock = $request->get('timelock');
		$active = $request->get('active');
		$selectedUsers = $request->get('selectedUsers');
		$updateResult = Settings_BruteForce_Module_Model::updateConfig($number, $timelock, $active);

		if ($selectedUsers != NULL) {
			$updateUsersForNotificationsResult = Settings_BruteForce_Module_Model::updateUsersForNotifications($selectedUsers);
		}
		$moduleName = $request->getModule();

		if ($updateResult == 0) {
			$return = array('success' => false, 'message' => vtranslate('LBL_FAIL', $moduleName));
		} else {
			$return = array('success' => true, 'message' => vtranslate('LBL_SAVE_SUCCESS', $moduleName));
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}
