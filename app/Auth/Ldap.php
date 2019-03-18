<?php

/**
 * Ldap authorization method class.
 *
 * @package   Auth
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Auth;

/**
 * Ldap authorization class.
 */
class Ldap extends Base
{
	/**
	 * Ldap process.
	 *
	 * @param array  $ldapData
	 * @param string $password
	 *
	 * @return null|bool
	 */
	public function verify(\App\Request $request)
	{
		\App\Log::trace('Start LDAP authentication', 'UserAuthentication');
		$userModel = \App\User::getUserModel($this->userId);
		$password = $request->getRaw('password');
		$result = false;
		if (!empty($password) || !$userModel->isActive()) {
			$ldapData = $this->getAuthMethod('ldap');
			$port = empty($ldapData['port']) ? 389 : $ldapData['port'];
			$ds = ldap_connect($ldapData['server'], $port);
			if (!$ds) {
				\App\Log::error('Error LDAP authentication: Could not connect to LDAP server.', 'UserAuthentication');
			}
			$this->setOptions($ds);
			if ('tls' === parse_url($ldapData['server'])['scheme']) {
				ldap_start_tls($ds);
			}
			$result = ldap_bind($ds, $userModel->get('user_name') . $ldapData['domain'], $password);
			if (!$result) {
				\App\Log::error('LDAP authentication: LDAP bind failed. |' . ldap_errno($ds) . '|' . ldap_error($ds), 'UserAuthentication');
			}
		} else {
			$this->fakeTest('', '');
		}
		\App\Log::trace('End LDAP authentication', 'UserAuthentication');
		return $result;
	}

	/**
	 * Fake test.
	 *
	 * @param string $login
	 * @param string $password
	 */
	public function fakeTest(string $login, string $password)
	{
		$ds = ldap_connect('', 9999);
		$this->setOptions($ds);
		@ldap_bind($ds, $login, $password);
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isActive(): bool
	{
		return 'true' === ($this->getAuthMethod('ldap')['active'] ?? false);
	}

	/**
	 * Undocumented function.
	 *
	 * @param resource $ds
	 */
	private function setOptions(&$ds)
	{
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($ds, LDAP_OPT_TIMELIMIT, 5);
		ldap_set_option($ds, LDAP_OPT_TIMEOUT, 5);
		ldap_set_option($ds, LDAP_OPT_NETWORK_TIMEOUT, 5);
	}
}
