<?php

/**
 * Switch Users Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_SwitchUsers_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		$record = $request->get('id');
		require('user_privileges/switchUsers.php');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$baseUserId = $currentUserModel->getId();
		if (Vtiger_Session::has('baseUserId') && Vtiger_Session::get('baseUserId') != '') {
			$baseUserId = Vtiger_Session::get('baseUserId');
		}
		if (!key_exists($baseUserId, $switchUsers) || !key_exists($record, $switchUsers[$baseUserId]) ) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	function process(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$baseUserId = $currentUserModel->getId();
		$userId = $request->get('id');
		$user = new Users();
		$currentUser = $user->retrieveCurrentUserInfoFromFile($userId);
		$name = $currentUser->column_fields['first_name'] . ' ' . $currentUser->column_fields['last_name'];
		$userName = $currentUser->column_fields['user_name'];
		Vtiger_Session::set('AUTHUSERID', $userId);
		Vtiger_Session::set('authenticated_user_id', $userId);
		Vtiger_Session::set('user_name', $userName);
		Vtiger_Session::set('full_user_name', $name);

		if (Vtiger_Session::get('baseUserId') == '') {
			Vtiger_Session::set('baseUserId', $baseUserId);
		} elseif ($userId == Vtiger_Session::get('baseUserId')) {
			Vtiger_Session::set('baseUserId', '');
		}
		header('Location: index.php');
	}
}
