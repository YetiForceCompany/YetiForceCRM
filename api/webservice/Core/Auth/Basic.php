<?php
/**
 * Basic authorization file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core\Auth;

/**
 * Basic authorization class.
 */
class Basic extends AbstractAuth
{
	/** {@inheritdoc}  */
	public function authenticate(string $realm): bool
	{
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			$this->api->response->addHeader('WWW-Authenticate', 'Basic realm="' . $realm . '"');
			throw new \Api\Core\Exception('Unauthorized', 401);
		}
		if (!$this->validatePass($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
			$this->api->response->addHeader('WWW-Authenticate', 'Basic realm="' . $realm . '"');
			throw new \Api\Core\Exception('Wrong Credentials', 401);
		}
		return true;
	}

	/**
	 * Validate pass.
	 *
	 * @param string $userName
	 * @param string $password
	 *
	 * @return bool
	 */
	public function validatePass(string $userName, string $password): bool
	{
		$row = (new \App\Db\Query())->from('w_#__servers')->where(['name' => $userName, 'status' => 1])->one();
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
