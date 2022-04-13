<?php
/**
 * Basic authorization file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
			throw new \Api\Core\Exception('Web service - Applications: Unauthorized', 401);
		}
		if (!$this->api->app || !$this->validatePwd($_SERVER['PHP_AUTH_PW'])) {
			$this->api->response->addHeader('WWW-Authenticate', 'Basic realm="' . $realm . '"');
			throw new \Api\Core\Exception('Web service - Applications: Wrong Credentials', 401);
		}
		$apiKey = $this->api->request->getHeaders()['x-api-key'] ?? null;
		if (!$apiKey || $apiKey !== \App\Encryption::getInstance()->decrypt($this->api->app['api_key'])) {
			throw new \Api\Core\Exception('Invalid api key', 401);
		}

		return true;
	}

	/** {@inheritdoc} */
	public function setServer(): self
	{
		$this->api->app = [];
		$userName = $_SERVER['PHP_AUTH_USER'] ?? '';
		$type = $this->api->request->getByType('_container', \App\Purifier::STANDARD);
		$query = (new \App\Db\Query())->from('w_#__servers')->where(['type' => $type, 'name' => $userName, 'status' => 1]);
		if ($userName && $row = $query->one()) {
			$row['id'] = (int) $row['id'];
			$this->api->app = $row;
		}

		return $this;
	}

	/**
	 * Validate pwd.
	 *
	 * @param string $password
	 *
	 * @return bool
	 */
	public function validatePwd(string $password): bool
	{
		return $this->api->app && $password === \App\Encryption::getInstance()->decrypt($this->api->app['pass']);
	}
}
