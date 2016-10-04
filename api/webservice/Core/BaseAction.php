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
	public $user = [];

	public function checkPermission($function, $userId)
	{
		if ($this->api->app['type'] == 'POS') {
			$db = PearDatabase::getInstance();
			$query = $result = $db->pquery('SELECT * FROM w_yf_pos_actions WHERE name = ? LIMIT 1', [$function]);
			if ($action = $db->getRow($result)) {
				$result = $db->pquery('SELECT * FROM w_yf_pos_users WHERE id = ? LIMIT 1', [$userId]);
				$user = $db->getRow($result);
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
