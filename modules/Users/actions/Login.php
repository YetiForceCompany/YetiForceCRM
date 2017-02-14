<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Users_Login_Action extends Vtiger_Action_Controller
{

	public function loginRequired()
	{
		return false;
	}

	public function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	/**
	 * Function verifies application access
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$username = $request->get('username');
		$password = $request->getRaw('password');
		$moduleModel = Users_Module_Model::getInstance('Users');
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		if ($bfInstance->isActive() && $bfInstance->isBlockedIp()) {
			$bfInstance->incAttempts();
			$moduleModel->saveLoginHistory($username, 'Blocked IP');
			header('Location: index.php?module=Users&view=Login&error=2');
			return false;
		}
		$user = CRMEntity::getInstance('Users');
		$user->column_fields['user_name'] = $username;
		if (!empty($password) && $user->doLogin($password)) {
			if (AppConfig::main('session_regenerate_id')) {
				Vtiger_Session::regenerateId(true); // to overcome session id reuse.
			}
			$userId = $user->column_fields['id'];
			Vtiger_Session::set('authenticated_user_id', $userId);
			Vtiger_Session::set('app_unique_key', AppConfig::main('application_unique_key'));
			Vtiger_Session::set('user_name', $username);
			Vtiger_Session::set('full_user_name', \App\Fields\Owner::getUserLabel($userId, true));

			if ($request->has('language') && AppConfig::main('langInLoginView')) {
				Vtiger_Session::set('language', $request->get('language'));
			}
			if ($request->has('layout')) {
				Vtiger_Session::set('layout', $request->get('layout'));
			}
			//Track the login History
			$moduleModel->saveLoginHistory($user->column_fields['user_name']);
			//End
			if (isset($_SESSION['return_params'])) {
				$return_params = urldecode($_SESSION['return_params']);
				header("Location: index.php?$return_params");
			} else {
				if (AppConfig::performance('SHOW_ADMIN_PANEL') && App\User::getUserModel($userId)->isAdmin()) {
					header('Location: index.php?module=Vtiger&parent=Settings&view=Index');
				} else {
					header('Location: index.php');
				}
			}
		} else {
			$bfInstance->updateBlockedIp();
			$error = 1;
			if ($bfInstance->isBlockedIp()) {
				$bfInstance->sendNotificationEmail();
				$error = 2;
			}
			//Track the login History
			$moduleModel->saveLoginHistory($username, 'Failed login');
			header("Location: index.php?module=Users&view=Login&error=$error");
		}
	}
}
