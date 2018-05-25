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
	 *  User authentication mode possible values.
	 */
	const ALLOWED_USER_AUTHY_MODE = ['TOTP_OFF', 'TOTP_OPTIONAL', 'TOTP_OBLIGATORY'];

	/**
	 * Generate otaauth url for QR codes.
	 *
	 * @link https://github.com/google/google-authenticator/wiki/Key-Uri-Format
	 *
	 * @param      $secret - REQUIRED: The secret parameter is an arbitrary key value encoded in Base32 according to RFC 3548. The padding specified in RFC 3548 section 2.2 is not required and should be omitted.
	 * @param      $name   - The name is used to identify which account a key is associated with.
	 * @param null $issuer - STRONGLY RECOMMENDED: The issuer parameter is a string value indicating the provider or service this account is associated with, URL-encoded according to RFC 3986.
	 *
	 * @return string - otpauth url
	 */
	public static function getOtpAuthUrl($secret, $name, $issuer = null)
	{
		if (is_null($issuer)) {
			$arr = parse_url($PORTAL_URL);
			$issuer = $arr['host'] ?? '';
		}
		$url = "otpauth://totp/{$name}?secret={$secret}";
		if (!empty($issuer)) {
			$url .= "&issuer={$issuer}";
		}
		return $url;
	}

	/**
	 * Creating a secret code for TOTP.
	 *
	 * @return string
	 */
	public static function createSecret()
	{
		$googleAuthenticator = new PHPGangsta_GoogleAuthenticator();
		return $googleAuthenticator->createSecret();
	}

	/**
	 * Create QR code.
	 *
	 * @param        $otpAuthUrl
	 * @param string $type       - acceptable types [HTML, SVG, PNG]
	 *
	 * @throws \App\Exceptions\NotAllowedMethod
	 *
	 * @return \Milon\Barcode\path|string - HTML code
	 */
	public static function createQrCode($otpAuthUrl, $type = 'HTML')
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
}
