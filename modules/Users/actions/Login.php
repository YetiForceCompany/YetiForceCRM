<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
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

	public function process(Vtiger_Request $request)
	{
		$username = $request->get('username');
		$password = $request->getRaw('password');

		$checkBlocked = Settings_BruteForce_Module_Model::checkBlocked();
		$bruteForceSettings = Settings_BruteForce_Module_Model::getBruteForceSettings();
		if ($checkBlocked && $bruteForceSettings['active']) {
			Settings_BruteForce_Module_Model::sendNotificationEmail();
			header('Location: index.php?module=Users&parent=Settings&view=Login&error=2');
			exit;
		}

		$user = CRMEntity::getInstance('Users');
		$user->column_fields['user_name'] = $username;
		$moduleModel = Users_Module_Model::getInstance('Users');

		if ($user->doLogin($password)) {
			if (AppConfig::main('session_regenerate_id'))
				Vtiger_Session::regenerateId(true); // to overcome session id reuse.
			$userid = $user->retrieve_user_id($username);
			Vtiger_Session::set('AUTHUSERID', $userid);

			Vtiger_Session::set('authenticated_user_id', $userid);
			Vtiger_Session::set('app_unique_key', AppConfig::main('application_unique_key'));
			Vtiger_Session::set('authenticated_user_language', AppConfig::main('default_language'));
			Vtiger_Session::set('user_name', $username);
			Vtiger_Session::set('full_user_name', \includes\fields\Owner::getUserLabel($userid, true));

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
				exit();
			} else {
				header('Location: index.php');
				exit();
			}
		} else {
			//Track the login History
			$browser = Settings_BruteForce_Module_Model::browserDetect();
			$moduleModel->saveLoginHistory($username, 'Failed login', $browser);
			header('Location: index.php?module=Users&parent=Settings&view=Login&error=1');
			exit;
		}
	}
}
