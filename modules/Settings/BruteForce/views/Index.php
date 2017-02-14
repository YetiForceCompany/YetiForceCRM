<?php

/**
 * Brute force index view class
 * @package YetiForce.Settings.View
 * @license licenses/License.html
 * @author YetiForce.com
 */
class Settings_BruteForce_Index_View extends Settings_Vtiger_Index_View
{

	/**
	 * Function gets module settings
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		$viewer = $this->getViewer($request);
		$adminUsers = Settings_BruteForce_Module_Model::getAdminUsers();
		$usersForNotifications = Settings_BruteForce_Module_Model::getUsersForNotifications();

		$viewer->assign('MODULE_MODEL', $bfInstance);
		$viewer->assign('CONFIG', $bfInstance->getData());
		$viewer->assign('BLOCKED', $bfInstance->getBlockedIp());
		$viewer->assign('ADMIN_USERS', $adminUsers);
		$viewer->assign('USERS_FOR_NOTIFICATIONS', $usersForNotifications);
		$viewer->view('Index.tpl', $request->getModule(false));
	}
}
