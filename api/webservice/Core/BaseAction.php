<?php

/**
 * Base action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class BaseAction
{

	public $api = [];
	protected $requestMethod = [];
	public $session = [];
	public $user = [];

	public function checkPermission($function)
	{
		if ($this->api->app['type'] == 'POS') {
			$dbPortal = PearDatabase::getInstance();
			$query = $result = $dbPortal->pquery('SELECT * FROM w_yf_pos_actions WHERE name = ? LIMIT 1', [$function]);
			if ($action = $dbPortal->getRow($result)) {
				$result = $dbPortal->pquery('SELECT * FROM w_yf_pos_users WHERE id = ? LIMIT 1', [$this->session['user_id']]);
				$user = $dbPortal->getRow($result);
				$this->user = $user;
				if (strpos($user['action'], (string) $action['id']) !== false) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	public function checkSession($sessionId)
	{
		$dbPortal = PearDatabase::getInstance();
		$result = $dbPortal->pquery('SELECT * FROM w_yf_portal_sessions WHERE id = ? LIMIT 1', [$sessionId]);
		if ($session = $dbPortal->getRow($result)) {
			$this->session = $session;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Function to get the value for a given key
	 * @param $key
	 * @return Value for the given key
	 */
	public function getRequestMethod()
	{
		return $this->requestMethod;
	}
}
