<?php

/**
 * Switch Users Action Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	public function checkPermission(App\Request $request)
	{
		$userId = $request->getInteger('id');
		$switchUsers = \Users_Module_Model::getSwitchUsers();
		$baseUserId = \App\User::getCurrentUserRealId();
		if ($userId != $baseUserId && (empty($switchUsers) || !\array_key_exists($userId, $switchUsers))) {
			$db = \App\Db::getInstance('log');
			$db->createCommand()->insert('l_#__switch_users', [
				'baseid' => $baseUserId,
				'destid' => $userId,
				'busername' => \App\User::getUserModel($baseUserId)->getName(),
				'dusername' => '',
				'date' => date('Y-m-d H:i:s'),
				'ip' => \App\TextUtils::textTruncate(\App\RequestUtil::getRemoteIP(), 100, false),
				'agent' => \App\TextUtils::textTruncate(\App\Request::_getServer('HTTP_USER_AGENT', '-'), 500, false),
				'status' => 'Failed login - No permission',
			])->execute();
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$db = \App\Db::getInstance('log');
		$currentUser = \App\User::getCurrentUserModel();
		$baseUserId = $currentUser->getId();
		$userId = $request->getInteger('id');

		if ($request->has('visitPurpose') && \App\Config::security('askAdminAboutVisitSwitchUsers', true)) {
			$db->createCommand()->insert('l_#__users_login_purpose', [
				'userid' => $userId,
				'datetime' => date('Y-m-d H:i:s'),
				'purpose' => $request->getByType('visitPurpose', \App\Purifier::TEXT),
				'baseid' => \App\User::getCurrentUserRealId()
			])->execute();
		}

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

		$db->createCommand()->insert('l_#__switch_users', [
			'baseid' => $baseUserId,
			'destid' => $userId,
			'busername' => $currentUser->getName(),
			'dusername' => $name,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \App\TextUtils::textTruncate(\App\RequestUtil::getRemoteIP(), 100, false),
			'agent' => \App\TextUtils::textTruncate(\App\Request::_getServer('HTTP_USER_AGENT', '-'), 500, false),
			'status' => $status,
		])->execute();
		\App\CustomView::resetCurrentView();
		OSSMail_Logout_Model::logoutCurrentUser();
		header('location: index.php');
	}
}
