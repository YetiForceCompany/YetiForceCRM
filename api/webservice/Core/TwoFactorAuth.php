<?php
/**
 * Two Factor Authorization file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core;

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

/**
 * Two Factor Authorization class.
 */
class TwoFactorAuth
{
	/** @var \Api\Core\BaseAction Action instance */
	private $action;

	/**
	 * Constructor.
	 *
	 * @param \Api\Core\BaseAction $action
	 */
	public function __construct(BaseAction $action)
	{
		$this->action = $action;
	}

	/**
	 * Check if authorization is active.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		$auth = $this->action->getUserData('auth');
		if (empty($auth) || empty($auth['authy_methods']) || '-' === $auth['authy_methods']) {
			return false;
		}
		return true;
	}

	/**
	 * Check auth secret key.
	 *
	 * @return string
	 */
	public function check(): string
	{
		$params = $this->action->getUserData('custom_params');
		if (empty($params['authy_secret_key'])) {
			return '2FA TOTP secret not generated';
		}
		return '';
	}

	/**
	 * Generate secret key.
	 *
	 * @return string
	 */
	public function generate(): array
	{
		$key = (new GoogleAuthenticator())->generateSecret();
		$this->action->updateUser([
			'custom_params' => [
				'authy_secret_key' => $key
			]
		]);
		return [
			'authMethods' => 'TOTP',
			'secretKey' => $key,
		];
	}

	/**
	 * Verification of the required data entry.
	 *
	 * @return string
	 */
	public function hasRequiresAdditionalData(): string
	{
		if ($this->action->controller->request->isEmpty('code')) {
			return 'ERR_NO_2FA_TOTP_CODE';
		}
		return '';
	}

	/**
	 * Verify secret key.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return void
	 */
	public function verify(): void
	{
		$params = $this->action->getUserData('custom_params');
		if (!(new GoogleAuthenticator())->checkCode($params['authy_secret_key'], (string) $this->action->controller->request->get('code'))) {
			throw new \Exception('Incorrect 2FA TOTP code');
		}
	}
}
