<?php

/**
 * Users Login action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Users_Login extends BaseAction
{

	protected $requestMethod = 'POST';

	public function login($userName, $password, $params)
	{
		$dbPortal = PearDatabase::getInstance('portal');
		$result = $dbPortal->pquery('SELECT p_yf_users.*,p_yf_servers.acceptable_url  FROM p_yf_users INNER JOIN p_yf_servers ON p_yf_servers.id = p_yf_users.server_id WHERE p_yf_users.user_name = ? AND p_yf_users.status = ? AND p_yf_servers.status = ?', [$userName, 1, 1]);
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
		$dbPortal->update('p_yf_users', [
			'login_time' => date('Y-m-d H:i:s'),
			], 'id = ?', [$userId]
		);
	}
}
