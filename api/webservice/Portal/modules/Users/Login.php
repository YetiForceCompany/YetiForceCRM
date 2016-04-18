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
		$result = $dbPortal->pquery('SELECT w_yf_portal_users.*,w_yf_servers.acceptable_url  FROM w_yf_portal_users INNER JOIN w_yf_servers ON w_yf_servers.id = w_yf_portal_users.server_id WHERE w_yf_portal_users.user_name = ? AND w_yf_portal_users.status = ? AND w_yf_servers.status = ?', [$userName, 1, 1]);
		if ($dbPortal->getRowCount($result) != 1) {
			throw new APIException('LBL_INVALID_DATA_ACCESS', 401);
		}
		$userDetail = $dbPortal->getRow($result);
		if ($params['fromUrl'] != $userDetail['acceptable_url']) {
			throw new APIException('LBL_INVALID_SERVER_URL', 401);
		}
		if ($password != $userDetail['password_t']) {
			throw new APIException('LBL_INVALID_USER_PASSWORD', 401);
		}
		$sessionData = APISession::init($userDetail, $params);
		self::updateUser($userDetail['id']);
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
