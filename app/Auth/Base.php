<?php

/**
 * Base authorization method class.
 *
 * @package   Auth
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Auth;

/**
 * Base auth class.
 */
class Base
{
	public const DEFAULT_AUTH = 'Pass';
	/**
	 * Request instance.
	 *
	 * @var \App\Request
	 */
	protected $request;
	/**
	 * User ID.
	 *
	 * @var int
	 */
	protected $userId;
	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $errorMessage = '';

	/**
	 * Gets message.
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->errorMessage;
	}

	public function __construct(int $userId, \App\Request $request)
	{
		$this->userId = $userId;
		$this->request = $request;
	}

	public static function getInstance(int $userId, string $method, \App\Request $request)
	{
		$methods = ['PLL_PASSWORD' => 'Pass', 'PLL_LDAP' => 'Ldap', 'PLL_PASSWORD_2FA' => 'PassTwoFactor'];
		$method = '\\' . __NAMESPACE__ . '\\' . ($methods[$method] ?? 'Base');
		return class_exists($method) ? new $method($userId, $request) : new self($userId, $request);
	}

	/**
	 * Verify authorization.
	 *
	 * @return mixed
	 */
	public function verify()
	{
		$defaultAuth = '\\' . __NAMESPACE__ . '\\' . self::DEFAULT_AUTH;
		$instance = new $defaultAuth($this->userId, $this->request);
		$instance->verify();
		$this->errorMessage = $instance->getMessage();
		return false;
	}

	/**
	 * Get authorizations detail.
	 *
	 * @return array
	 */
	public function getAuthMethods(): array
	{
		if (\App\Cache::has(__FUNCTION__, $cacheName)) {
			return \App\Cache::get(__FUNCTION__, $cacheName);
		}
		$dataReader = (new \App\Db\Query())->from('yetiforce_auth')->createCommand()->query();
		$auth = [];
		while ($row = $dataReader->read()) {
			$auth[$row['type']][$row['param']] = $row['value'];
		}
		$dataReader->close();
		\App\Cache::save(__FUNCTION__, $cacheName, $auth);

		return $auth;
	}

	/**
	 * Get authorization method detail.
	 *
	 * @param string $method
	 *
	 * @return array
	 */
	protected function getAuthMethod(string $method): array
	{
		return $this->getAuthMethods()[$method] ?? [];
	}

	/**
	 * Checks active authentication.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		return false;
	}
}
