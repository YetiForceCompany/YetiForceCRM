<?php

/**
 * TOTP authentication method class.
 * TOTP - Time-based One-time Password.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Users_Totp_AuthMethod
{
	/**
	 *  User authentication mode possible values.
	 */
	const ALLOWED_USER_AUTHY_MODE = ['TOTP_OFF', 'TOTP_OPTIONAL', 'TOTP_OBLIGATORY'];
	const TOTP_OFF = 'TOTP_OFF';
	const TOTP_OPTIONAL = 'TOTP_OPTIONAL';
	const TOTP_OBLIGATORY = 'TOTP_OBLIGATORY';
}
