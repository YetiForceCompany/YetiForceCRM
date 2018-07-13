<?php
/**
 * 2FA test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Settings;

class Authy2FA extends \Tests\Base
{
	/**
	 * Test method "verifyCode".
	 */
	public function test2FAverifyCode()
	{
		$auth = new \Google\Authenticator\GoogleAuthenticator();
		$secret = $auth->generateSecret();
		$this->assertFalse(\Users_Totp_Authmethod::verifyCode($secret, '123000'), 'The "verifyCode" method does not work');
		$this->assertTrue(\Users_Totp_Authmethod::verifyCode($secret, $auth->getCode($secret)), 'The "verifyCode" method does not work');
	}
}
