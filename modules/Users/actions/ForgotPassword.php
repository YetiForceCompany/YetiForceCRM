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
require_once 'config/config.php';
require_once 'config/debug.php';
require_once 'config/security.php';
require_once 'config/performance.php';
require_once('include/ConfigUtils.php');
include_once "include/utils/VtlibUtils.php";
include_once "include/utils/CommonUtils.php";
include_once "include/Loader.php";
include_once 'include/runtime/BaseModel.php';
include_once 'include/runtime/Viewer.php';
include_once "include/http/Request.php";
include_once "include/Webservices/Custom/ChangePassword.php";
include_once "include/Webservices/Utils.php";
include_once "include/runtime/EntryPoint.php";
include_once 'modules/Vtiger/helpers/ShortURL.php';

class Users_ForgotPassword_Action
{

	public function changePassword($request)
	{
		$request = new Vtiger_Request($request);
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

	public function requestForgotPassword($request)
	{
		$request = new Vtiger_Request($request);
		$adb = PearDatabase::getInstance();
		$username = vtlib_purify($request->get('user_name'));
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
			$trackURL = Vtiger_ShortURL_Helper::generateURL($options);
			$data = [
				'sysname' => 'UsersForgotPassword',
				'to_email' => $email,
				'module' => 'Users',
				'record' => $userId,
				'trackURL' => $trackURL,
			];
			$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
			$status = $recordModel->sendMailFromTemplate($data);
			$site_URL = vglobal('site_URL') . 'index.php?modules=Users&view=Login';
			if ($status === 1)
				header('Location:  ' . $site_URL . '&status=1');
			else
				header('Location:  ' . $site_URL . '&statusError=1');
		} else {
			$site_URL = vglobal('site_URL') . 'index.php?modules=Users&view=Login';
			header('Location:  ' . $site_URL . '&fpError=1');
		}
	}

	public static function run($request)
	{
		$instance = new self();
		if (isset($_REQUEST['user_name']) && isset($_REQUEST['emailId'])) {
			if (SysSecurity::get('RESET_LOGIN_PASSWORD')) {
				$instance->requestForgotPassword($request);
			} else {
				die(vtranslate('LBL_PERMISSION_DENIED'));
			}
		} else {
			$instance->changePassword($request);
		}
	}
}

Users_ForgotPassword_Action::run($_REQUEST);
