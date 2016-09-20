<?php

/**
 * Basic Authorization class
 * @package YetiForce.WebserviceAuth
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class BasicAuth extends AbstractAuth
{

	public function authenticate($realm)
	{
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			$this->requireLogin($realm);
			throw new APIException('Unauthorized', 401);
		}

		if (!$this->validatePass($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
			$this->requireLogin($realm);
			throw new APIException('Wrong Credentials', 401);
		}
		return true;
	}

	public function requireLogin($realm)
	{
		$this->api->response->addHeader('WWW-Authenticate', 'Basic realm="' . $realm . '"');
	}

	public function validatePass($username, $password)
	{
		$db = $this->api->db;
		$result = $db->pquery('SELECT * FROM w_yf_servers WHERE name = ? && status = ?', [$username, 1]);
		if ($db->getRowCount($result)) {
			$row = $db->getRow($result);
			$status = $password == $row['pass'];
			if ($status) {
				$this->currentServer = $row;
			}
			return $status;
		}
		return false;
	}
}
