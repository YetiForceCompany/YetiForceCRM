<?php

/**
 * Ldap authorization method class.
 *
 * @package   Auth
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Auth;

/**
 * Ldap authorization class.
 */
class PassTwoFactor extends Pass
{
	/**
	 * {@inheritdoc}
	 */
	public function verify()
	{
		$result = false;
		if ('2fa' === \App\Session::get('LoginAuthMethod')) {
			$result = $this->verifyTwoFactor();
		} elseif (parent::verify()) {
			\App\Session::set('LoginAuthMethod', '2fa');
			$result = '2fa';
		}
		return $result;
	}

	/**
	 * Verify Two Factor authentication.
	 *
	 * @return bool
	 */
	public function verifyTwoFactor(): bool
	{
		$userModel = \App\User::getUserModel($this->userId);
		$method = $userModel->getDetail('authy_secret_totp');
		$result = false;
		if ($instance = self::getMethodInstance($method, $this->userId, $this->request)) {
			$result = $instance->verify();
		}
		return $result;
	}

	/**
	 * Gets method instance.
	 *
	 * @param string       $method
	 * @param int          $userId
	 * @param \App\Request $request
	 */
	public static function getMethodInstance(string $method, int $userId, \App\Request $request)
	{
		$methods = ['PLL_AUTHY_TOTP' => 'Totp'];
		$className = __NAMESPACE__ . '\\Mathods\\' . ($methods[$method] ?? '');
		$instance = null;
		if (class_exists($className)) {
			$instance = new $className($userId, $request);
		}
		return $instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isActive()
	{
		return true;
	}
}
