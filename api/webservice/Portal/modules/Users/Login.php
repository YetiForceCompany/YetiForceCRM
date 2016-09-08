<?php

/**
 * Users Login action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Users_Login extends BaseAction
{

	protected $requestMethod = ['post'];

	public function post($userName, $password, $params)
	{
		$dbPortal = PearDatabase::getInstance('portal');
		$result = $dbPortal->pquery('SELECT * FROM w_yf_portal_users WHERE user_name = ? && status = ?', [$userName, 1]);
		if ($dbPortal->getRowCount($result) != 1) {
			throw new APIException('LBL_INVALID_DATA_ACCESS', 401);
		}
		$userDetail = $dbPortal->getRow($result);
		if (rtrim($this->api->app['acceptable_url'], '/') != rtrim($params['fromUrl'], '/')) {
			throw new APIException('LBL_INVALID_SERVER_URL', 401);
		}
		if ($password != $userDetail['password_t']) {
			throw new APIException('LBL_INVALID_USER_PASSWORD', 401);
		}
		$sessionData = APISession::init($userDetail, $params);
		$this->updateUser($userDetail['id']);
		return [
			'sessionId' => $sessionData['id'],
			'firstName' => $userDetail['first_name'],
			'lastName' => $userDetail['last_name'],
			'company' => 'YetiForce',
			'lastLoginTime' => $userDetail['login_time'],
			'lastLogoutTime' => $userDetail['logout_time'],
			'language' => $sessionData['language'],
			'logged' => true,
		];
	}

	public function updateUser($userId)
	{
		$dbPortal = PearDatabase::getInstance('portal');
		$dbPortal->update('w_yf_portal_users', [
			'login_time' => date('Y-m-d H:i:s'),
			], 'id = ?', [$userId]
		);
	}
}
