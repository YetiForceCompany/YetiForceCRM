<?php

/**
 * Switch Users Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_SwitchUsers_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$userId = $request->get('id');
		require('user_privileges/switchUsers.php');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$baseUserId = $currentUserModel->getRealId();
		if (!key_exists($baseUserId, $switchUsers) || !key_exists($userId, $switchUsers[$baseUserId])) {
			$dbLog = PearDatabase::getInstance('log');
			$dbLog->insert('l_yf_switch_users', [
				'baseid' => $baseUserId,
				'destid' => $userId,
				'busername' => $currentUserModel->getName(),
				'dusername' => '',
				'date' => date('Y-m-d H:i:s'),
				'ip' => vtlib\Functions::getRemoteIP(),
				'agent' => $_SERVER['HTTP_USER_AGENT'],
				'status' => 'Failed login - No permission',
			]);
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$baseUserId = $currentUserModel->getId();
		$userId = $request->get('id');
		$user = new Users();
		$currentUser = $user->retrieveCurrentUserInfoFromFile($userId);
		$name = $currentUserModel->getName();
		$userName = $currentUser->column_fields['user_name'];
		Vtiger_Session::set('AUTHUSERID', $userId);
		Vtiger_Session::set('authenticated_user_id', $userId);
		Vtiger_Session::set('user_name', $userName);
		Vtiger_Session::set('full_user_name', $name);

		$status = 'Switched';
		if (Vtiger_Session::get('baseUserId') == '') {
			Vtiger_Session::set('baseUserId', $baseUserId);
			$status = 'Signed in';
		} elseif ($userId == Vtiger_Session::get('baseUserId')) {
			$baseUserId = $userId;
			Vtiger_Session::set('baseUserId', '');
			$status = 'Signed out';
		} else {
			$baseUserId = Vtiger_Session::get('baseUserId');
		}

		$dbLog = PearDatabase::getInstance('log');
		$dbLog->insert('l_yf_switch_users', [
			'baseid' => $baseUserId,
			'destid' => $userId,
			'busername' => $currentUserModel->getName(),
			'dusername' => $name,
			'date' => date('Y-m-d H:i:s'),
			'ip' => vtlib\Functions::getRemoteIP(),
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'status' => $status,
		]);

		header('Location: index.php');
	}
}
