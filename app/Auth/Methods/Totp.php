<?php

/**
 * TOTP - Time-based One-time Password.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

namespace App\Auth\Methods;

/**
 * TOTP authentication method class.
 */
class Totp
{
	/**
	 * User authentication mode possible values.
	 * TOTP_OFF - 2FA TOTP is checking off
	 * TOTP_OPTIONAL - It is defined by the user
	 * TOTP_OBLIGATORY - It is obligatory.
	 */
	public const ALLOWED_USER_AUTHY_MODE = ['TOTP_OFF', 'TOTP_OPTIONAL', 'TOTP_OBLIGATORY'];
	/**
	 * User ID.
	 *
	 * @var int
	 */
	private $userId;
	/**
	 * Request instance.
	 *
	 * @var \App\Request
	 */
	private $request;

	public function __construct(int $userId, \App\Request $request)
	{
		$this->userId = $userId;
		$this->request = $request;
	}

	/**
	 * 2FA - verification of the code from the user.
	 *
	 * @return bool
	 */
	public function verify()
	{
		$userModel = \App\User::getUserModel($this->userId);
		return (new GoogleAuthenticator())->checkCode($userModel->getDetail('authy_secret_totp'), (string) $this->request->getByType('user_code', 'Digital'));
	}
}
