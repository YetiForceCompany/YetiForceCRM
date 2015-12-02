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

class Settings_BruteForce_Show_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$settings = Settings_BruteForce_Module_Model::getBruteForceSettings();
		$blockedIP = Settings_BruteForce_Module_Model::getBlockedIP();

		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$adminUsers = Settings_BruteForce_Module_Model::getAdminUsers();
		$usersForNotifications = Settings_BruteForce_Module_Model::getUsersForNotifications();

		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('ATTEMPS_NUMBER', $settings['attempsnumber']);
		$viewer->assign('BRUTEFORCEACTIVE', $settings['active']);
		$viewer->assign('BLOCK_TIME', $settings['timelock']);
		$viewer->assign('BLOCKED', $blockedIP);
		$viewer->assign('ADMINUSERS', $adminUsers);
		$viewer->assign('USERFORNOTIFICATIONS', $usersForNotifications);
		$viewer->view('Show.tpl', $qualifiedModuleName);
	}
}
