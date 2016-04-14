<?php

/**
 * Basic Authorization class
 * @package YetiForce.WebserviceAuth
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class BasicAuth extends AbstractAuth
{

	function authenticate($realm)
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

	function requireLogin($realm)
	{
		$this->api->response->addHeader('WWW-Authenticate', 'Basic realm="' . $realm . '"');
	}

	function validatePass($username, $password)
	{
		$db = $this->api->db;
		$result = $db->pquery('SELECT * FROM w_yf_servers WHERE name = ?', [$username]);
		if ($db->getRowCount($result)) {
			$row = $db->getRow($result);
			$status = $password == $row['xxx'];
			if ($status) {
				$this->currentServer = $row;
			}
			return $status;
		}
		return false;
	}
}
