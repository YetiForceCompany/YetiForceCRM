<?php

/**
 * Brute force index view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author YetiForce S.A.
 */
class Settings_BruteForce_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * Function gets module settings.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
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
