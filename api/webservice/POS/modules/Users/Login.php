<?php

/**
 * Users Login action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class API_Users_Login extends BaseAction
{

	protected $requestMethod = ['POST'];

	public function post($userName, $password)
	{
		$dbPortal = PearDatabase::getInstance();
		$result = $dbPortal->pquery('SELECT * FROM w_yf_pos_users WHERE user_name = ? && status = ? ', [$userName, 1]);

		if ($dbPortal->getRowCount($result) != 1) {
			throw new APIException('ERR_INVALID_DATA_ACCESS', 401);
		}
		$userDetail = $dbPortal->getRow($result);

		if ($password != $userDetail['pass']) {
			throw new APIException('ERR_INVALID_USER_PASSWORD', 401);
		}
		if ($this->api->app['id'] != $userDetail['server_id']){
			throw new APIException('ERR_INVALID_APP', 401);
		}
		$sessionData = APISession::init($userDetail);
		self::updateUser($userDetail['id']);
		return [
			'sessionId' => $sessionData['id'],
			'firstName' => $userDetail['first_name'],
			'lastName' => $userDetail['last_name'],
			'company' => 'YetiForce',
			'logged' => true,
		];
	}

	public function updateUser($userId)
	{
		$dbPortal = PearDatabase::getInstance();
		$dbPortal->update('w_yf_pos_users', [
			'login_time' => date('Y-m-d H:i:s'),
			], 'id = ?', [$userId]
		);
	}
}
