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
chdir(dirname(__FILE__) . "/../../../");
require_once 'include/RequirementsValidation.php';
require_once 'include/main/WebUI.php';
include_once "include/utils/VtlibUtils.php";
include_once "include/Webservices/Custom/ChangePassword.php";
include_once "include/Webservices/Utils.php";
include_once 'modules/Vtiger/helpers/ShortURL.php';

class Users_ForgotPassword_Action
{

	public function changePassword(Vtiger_Request $request)
	{
		$viewer = Vtiger_Viewer::getInstance();
		$userName = $request->get('username');
		$newPassword = $request->get('password');
		$confirmPassword = $request->get('confirmPassword');
		$shortURLID = $request->get('shorturl_id');
		$secretHash = $request->get('secret_hash');
		$shortURLModel = Vtiger_ShortURL_Helper::getInstance($shortURLID);
		$secretToken = $shortURLModel->handler_data['secret_token'];

		$validateData = array('username' => $userName,
			'secret_token' => $secretToken,
			'secret_hash' => $secretHash
		);
		$valid = $shortURLModel->compareEquals($validateData);
		if ($valid) {
			$userId = getUserId_Ol($userName);
			$user = Users::getActiveAdminUser();
			$wsUserId = vtws_getWebserviceEntityId('Users', $userId);
			vtws_changePassword($wsUserId, '', $newPassword, $confirmPassword, $user);
		} else {
			$viewer->assign('ERROR', true);
		}
		$shortURLModel->delete();
		$viewer->assign('USERNAME', $userName);
		$viewer->assign('PASSWORD', $newPassword);
		$viewer->view('FPLogin.tpl', 'Users');
	}

	public function requestForgotPassword(Vtiger_Request $request)
	{
		$adb = PearDatabase::getInstance();
		$username = App\Purifier::purify($request->get('user_name'));
		$result = $adb->pquery('select id,email1 from vtiger_users where user_name = ? ', array($username));
		if ($adb->num_rows($result) > 0) {
			$email = $adb->query_result($result, 0, 'email1');
		}
		if (strcasecmp($request->get('emailId'), $email) === 0) {
			$userId = $adb->query_result($result, 0, 'id');
			$time = time();
			$options = array(
				'handler_path' => 'modules/Users/handlers/ForgotPassword.php',
				'handler_class' => 'Users_ForgotPassword_Handler',
				'handler_function' => 'changePassword',
				'handler_data' => array(
					'username' => $username,
					'email' => $email,
					'time' => $time,
					'hash' => md5($username . $time)
				)
			);
			$status = \App\Mailer::sendFromTemplate([
					'template' => 'UsersForgotPassword',
					'moduleName' => 'Users',
					'recordId' => $userId,
					'to' => $email,
					'priority' => 9,
					'trackURL' => Vtiger_ShortURL_Helper::generateURL($options)
			]);
			$site_URL = vglobal('site_URL') . 'index.php?modules=Users&view=Login';
			if ($status)
				header('Location:  ' . $site_URL . '&status=1');
			else
				header('Location:  ' . $site_URL . '&statusError=1');
		} else {
			$site_URL = vglobal('site_URL') . 'index.php?modules=Users&view=Login';
			header('Location:  ' . $site_URL . '&fpError=1');
		}
	}

	public static function run(Vtiger_Request $request)
	{
		$instance = new self();
		if ($request->has('user_name') && $request->has('emailId')) {
			if (AppConfig::security('RESET_LOGIN_PASSWORD')) {
				$instance->requestForgotPassword($request);
			} else {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}
		} else {
			$instance->changePassword($request);
		}
	}
}

Users_ForgotPassword_Action::run(AppRequest::init());
