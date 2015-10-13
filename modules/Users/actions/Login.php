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

	function loginRequired()
	{
		return false;
	}

	function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	function process(Vtiger_Request $request)
	{
		$username = $request->get('username');
		$password = $request->get('password');
		if ($request->get('mode') == 'install') {
			$dirPath = 'install';
			Users_Module_Model::deleteLangFiles();
			$configTemplate = "config/config.template.php";
			if (file_exists($configTemplate)) {
				unlink($configTemplate);
			}
			if (file_exists($dirPath))
				foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
					$path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
				}
			rmdir($dirPath);
		}

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
			if (vglobal('session_regenerate_id'))
				session_regenerate_id(true); // to overcome session id reuse.
			$userid = $user->retrieve_user_id($username);
			Vtiger_Session::set('AUTHUSERID', $userid);

			// For Backward compatability
			// TODO Remove when switch-to-old look is not needed
			Vtiger_Session::set('authenticated_user_id', $userid);
			Vtiger_Session::set('app_unique_key', vglobal('application_unique_key'));
			Vtiger_Session::set('authenticated_user_language', vglobal('default_language'));
			Vtiger_Session::set('user_name', $username);
			Vtiger_Session::set('full_user_name', Vtiger_Functions::getUserRecordLabel($userid));

			if ($request->has('language')) {
				Vtiger_Session::set('language', $request->get('language'));
			}

			//Enabled session variable for KCFINDER 
			$_SESSION['KCFINDER'] = array();
			$_SESSION['KCFINDER']['disabled'] = false;
			$_SESSION['KCFINDER']['uploadURL'] = "cache/upload";
			$_SESSION['KCFINDER']['uploadDir'] = "../../cache/upload";
			$deniedExts = implode(" ", vglobal('upload_badext'));
			$_SESSION['KCFINDER']['deniedExts'] = $deniedExts;
			// End
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
