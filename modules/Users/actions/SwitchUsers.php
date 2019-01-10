<?php

/**
 * Switch Users Action Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_SwitchUsers_Action extends \App\Controller\Action
{
	/**
	 * Function checks permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$userId = $request->getInteger('id');
		require 'user_privileges/switchUsers.php';
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$baseUserId = $currentUserModel->getRealId();
		if (!array_key_exists($baseUserId, $switchUsers) || !array_key_exists($userId, $switchUsers[$baseUserId])) {
			$db = \App\Db::getInstance('log');
			$db->createCommand()->insert('l_#__switch_users', [
				'baseid' => $baseUserId,
				'destid' => $userId,
				'busername' => $currentUserModel->getName(),
				'dusername' => '',
				'date' => date('Y-m-d H:i:s'),
				'ip' => \App\RequestUtil::getRemoteIP(),
				'agent' => $request->getServer('HTTP_USER_AGENT'),
				'status' => 'Failed login - No permission',
			])->execute();
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function proccess.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$currentUser = \App\User::getCurrentUserModel();
		$baseUserId = $currentUser->getId();
		$userId = $request->getInteger('id');
		$newUser = \App\User::getUserModel($userId);
		$name = $newUser->getName();
		App\Session::set('authenticated_user_id', $userId);
		App\Session::set('user_name', $newUser->getDetail('user_name'));
		App\Session::set('full_user_name', $name);

		$status = 'Switched';
		if (empty(App\Session::get('baseUserId'))) {
			App\Session::set('baseUserId', $baseUserId);
			$status = 'Signed in';
		} elseif ($userId === App\Session::get('baseUserId')) {
			$baseUserId = $userId;
			App\Session::set('baseUserId', '');
			$status = 'Signed out';
		} else {
			$baseUserId = App\Session::get('baseUserId');
		}

		$db = \App\Db::getInstance('log');
		$db->createCommand()->insert('l_#__switch_users', [
			'baseid' => $baseUserId,
			'destid' => $userId,
			'busername' => $currentUser->getName(),
			'dusername' => $name,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \App\RequestUtil::getRemoteIP(),
			'agent' => $request->getServer('HTTP_USER_AGENT'),
			'status' => $status,
		])->execute();
		\App\CustomView::resetCurrentView();
		OSSMail_Logout_Model::logoutCurrentUser();
		header('location: index.php');
	}
}
