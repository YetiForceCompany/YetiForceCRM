<?php

namespace Api\Core\Auth;

/**
 * Basic Authorization class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Basic extends AbstractAuth
{
	public function authenticate($realm)
	{
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			$this->requireLogin($realm);
			throw new \Api\Core\Exception('Unauthorized', 401);
		}
		if (!$this->validatePass($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
			$this->requireLogin($realm);
			throw new \Api\Core\Exception('Wrong Credentials', 401);
		}
		return true;
	}

	public function requireLogin($realm)
	{
		$this->api->response->addHeader('WWW-Authenticate', 'Basic realm="' . $realm . '"');
	}

	public function validatePass($name, $password)
	{
		$row = (new \App\Db\Query())->from('w_#__servers')->where(['name' => $name, 'status' => 1])->one();
		if ($row) {
			$status = $password === \App\Encryption::getInstance()->decrypt($row['pass']);
			if ($status) {
				$this->currentServer = $row;
			}

			return $status;
		}
		return false;
	}
}
