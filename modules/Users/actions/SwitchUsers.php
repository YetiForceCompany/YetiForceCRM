<?php

/**
 * Switch Users Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_SwitchUsers_Action extends Vtiger_Action_Controller
{

	/**
	 * Function checks permissions
	 * @param Vtiger_Request $request
	 * @throws \Exception\NoPermitted
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$userId = $request->get('id');
		require('user_privileges/switchUsers.php');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$baseUserId = $currentUserModel->getRealId();
		if (!key_exists($baseUserId, $switchUsers) || !key_exists($userId, $switchUsers[$baseUserId])) {
			$db = \App\Db::getInstance('log');
			$db->createCommand()->insert('l_#__switch_users', [
				'baseid' => $baseUserId,
				'destid' => $userId,
				'busername' => $currentUserModel->getName(),
				'dusername' => '',
				'date' => date('Y-m-d H:i:s'),
				'ip' => \App\RequestUtil::getRemoteIP(),
				'agent' => $_SERVER['HTTP_USER_AGENT'],
				'status' => 'Failed login - No permission',
			])->execute();
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function proccess
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$baseUserId = $currentUserModel->getId();
		$userId = $request->get('id');
		$user = new Users();
		$currentUser = $user->retrieveCurrentUserInfoFromFile($userId);
		$name = $currentUserModel->getName();
		$userName = $currentUser->column_fields['user_name'];
		Vtiger_Session::set('authenticated_user_id', $userId);
		Vtiger_Session::set('user_name', $userName);
		Vtiger_Session::set('full_user_name', $name);

		$status = 'Switched';
		if (empty(Vtiger_Session::get('baseUserId'))) {
			Vtiger_Session::set('baseUserId', $baseUserId);
			$status = 'Signed in';
		} elseif ($userId === Vtiger_Session::get('baseUserId')) {
			$baseUserId = $userId;
			Vtiger_Session::set('baseUserId', '');
			$status = 'Signed out';
		} else {
			$baseUserId = Vtiger_Session::get('baseUserId');
		}

		$db = \App\Db::getInstance('log');
		$db->createCommand()->insert('l_#__switch_users', [
			'baseid' => $baseUserId,
			'destid' => $userId,
			'busername' => $currentUserModel->getName(),
			'dusername' => $name,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \App\RequestUtil::getRemoteIP(),
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'status' => $status,
		])->execute();

		header('Location: index.php');
	}
}
