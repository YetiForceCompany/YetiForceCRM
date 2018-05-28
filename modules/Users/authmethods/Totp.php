<?php

/**
 * TOTP authentication method class.
 * TOTP - Time-based One-time Password.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Users_Totp_Authmethod
{
	/**
	 * Clock tolerance example 2 = 2*30sec.
	 */
	const CLOCK_TOLERANCE = 2;
	/**
	 *  User authentication mode possible values.
	 */
	const ALLOWED_USER_AUTHY_MODE = ['TOTP_OFF', 'TOTP_OPTIONAL', 'TOTP_OBLIGATORY'];
	/**
	 * @var int - User id
	 */
	private $userId;
	/**
	 * @var string - Secret code
	 */
	private $secret;

	/**
	 * Users_Totp_Authmethod constructor.
	 *
	 * @param int $userId - Id of user
	 */
	public function __construct($userId)
	{
		$this->userId = $userId;
	}

	/**
	 * Generate otaauth url for QR codes.
	 *
	 * @link https://github.com/google/google-authenticator/wiki/Key-Uri-Format
	 *
	 * @param string      $secret - REQUIRED: The secret parameter is an arbitrary key value encoded in Base32 according to RFC 3548. The padding specified in RFC 3548 section 2.2 is not required and should be omitted.
	 * @param string      $name   - The name is used to identify which account a key is associated with.
	 * @param string|null $issuer - STRONGLY RECOMMENDED: The issuer parameter is a string value indicating the provider or service this account is associated with, URL-encoded according to RFC 3986.
	 *
	 * @return string - otpauth url
	 */
	public function getOtpAuthUrl($secret, $name, $issuer = null)
	{
		if (is_null($issuer)) {
			$arr = parse_url($PORTAL_URL);
			$issuer = $arr['host'] ?? '';
		}
		$url = "otpauth://totp/{$name}?secret={$secret}";
		if (!empty($issuer)) {
			$url .= "&issuer={$issuer}";
		}
		//$period - OPTIONAL only if type is totp: The period parameter defines a period that a TOTP code will be valid for, in seconds. The default value is 30.
		$period = 30 * static::CLOCK_TOLERANCE;
		$url .= "&period={$period}";
		return $url;
	}

	/**
	 * Creating a secret code for TOTP.
	 *
	 * @return string
	 */
	public function createSecret()
	{
		$googleAuthenticator = new PHPGangsta_GoogleAuthenticator();
		$this->secret = $googleAuthenticator->createSecret();
		return $this->secret;
	}

	/**
	 * Create QR code.
	 *
	 * @param string $otpAuthUrl
	 * @param string $type       - acceptable types [HTML, SVG, PNG]
	 *
	 * @throws \App\Exceptions\NotAllowedMethod
	 *
	 * @return \Milon\Barcode\path|string - HTML code
	 */
	private function createQrCode($otpAuthUrl, $type = 'HTML')
	{
		$qrCodeGenerator = new \Milon\Barcode\DNS2D();
		$qrCodeGenerator->setStorPath(__DIR__ . '/cache/');
		switch ($type) {
			case 'HTML':
				return $qrCodeGenerator->getBarcodeHTML($otpAuthUrl, 'QRCODE');
				break;
			case 'SVG':
				return $qrCodeGenerator->getBarcodeSVG($otpAuthUrl, 'QRCODE');
				break;
			case 'PNG':
				return '<img src="data:image/png;base64,' .
					$qrCodeGenerator->getBarcodePNG($otpAuthUrl, 'QRCODE') .
					'" alt="QR code" />';
				break;
		}
		throw new \App\Exceptions\NotAllowedMethod('LBL_NOT_EXIST: ' . $type);
	}

	/**
	 * Create QR code for user.
	 *
	 * @param string $type - acceptable types [HTML, SVG, PNG]
	 *
	 * @throws \App\Exceptions\NotAllowedMethod
	 *
	 * @return \Milon\Barcode\path|string
	 */
	public function createQrCodeForUser($type = 'HTML')
	{
		return $this->createQrCode($this->getOtpAuthUrl($this->secret, \App\User::getUserModel($this->userId)->getDetail('user_name')), $type);
	}

	/**
	 * 2FA - verification of the code from the user.
	 *
	 * @param string $secret
	 * @param string $userCode
	 *
	 * @return bool
	 */
	public static function verifyCode($secret, $userCode)
	{
		return (new PHPGangsta_GoogleAuthenticator())->verifyCode($secret, (string) $userCode, static::CLOCK_TOLERANCE);
	}
}
