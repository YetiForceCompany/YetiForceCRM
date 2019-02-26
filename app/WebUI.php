<?php
/**
 * Web UI file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * WebUi class.
 */
class WebUI
{
	/**
	 * Function to check if the User has logged in.
	 */
	protected function isLoggedIn()
	{
		return \App\Session::has('authenticated_user_id') && !empty($this->getLoggedUserId()) &&
		\App\Config::main('application_unique_key') === \App\Session::get('app_unique_key');
	}

	/**
	 * Gets user ID.
	 *
	 * @return int User ID
	 */
	protected function getLoggedUserId()
	{
		return \App\Session::get('authenticated_user_id');
	}

	/**
	 * Requirements validation.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	protected function requirementsValidation()
	{
		if (version_compare(PHP_VERSION, '7.1', '<')) {
			throw new \App\Exceptions\AppException('Wrong PHP version, recommended version >= 7.1');
		}
	}

	/**
	 * Process.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function process()
	{
		if (\App\Config::main('forceSSL') && !\App\RequestUtil::getBrowserInfo()->https) {
			header("location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", true, 301);
		}
		if (\App\Config::main('forceRedirect')) {
			$request = \App\Request::init();
			$requestUrl = (\App\RequestUtil::getBrowserInfo()->https ? 'https' : 'http') . '://' . $request->getServer('HTTP_HOST') . $request->getServer('REQUEST_URI');
			if (stripos($requestUrl, \App\Config::main('site_URL')) !== 0) {
				header('location: ' . \App\Config::main('site_URL'), true, 301);
			}
		}
		$this->setHeaders();
		$this->requirementsValidation();
		if (!\App\Config::main('application_unique_key', false)) {
			header('location: install/Install.php');
		}
	}

	public function setHeaders()
	{
		if (headers_sent()) {
			return;
		}
		$browser = \App\RequestUtil::getBrowserInfo();
		header('expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('last-modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		if ($browser->ie && $browser->https) {
			header('pragma: private');
			header('cache-control: private, must-revalidate');
		} else {
			header('cache-control: private, no-cache, no-store, must-revalidate, post-check=0, pre-check=0');
			header('pragma: no-cache');
		}
		header('x-frame-options: SAMEORIGIN');
		header('x-xss-protection: 1; mode=block');
		header('x-content-type-options: nosniff');
		header('referrer-policy: no-referrer');
		header('strict-transport-security: max-age=31536000; includeSubDomains; preload');
		header('expect-ct: enforce; max-age=3600');
		header('access-control-allow-methods: GET, POST, PUT, DELETE');
		header('x-robots-tag: none');
		header('x-permitted-cross-domain-policies: none');
		if (\App\Config::security('CSP_ACTIVE')) {
			// 'nonce-" . App\Session::get('CSP_TOKEN') . "'
			$allowed = \implode(' ', \App\Config::security('PURIFIER_ALLOWED_DOMAINS'));
			header("content-security-policy: default-src 'self' blob:; img-src 'self' data: a.tile.openstreetmap.org b.tile.openstreetmap.org c.tile.openstreetmap.org $allowed; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' blob:; form-action 'self' ;connect-src 'self';");
		}
		if ($keys = \App\Config::security('HPKP_KEYS')) {
			header('public-key-pins: pin-sha256="' . implode('"; pin-sha256="', $keys) . '"; max-age=10000;');
		}
		header_remove('x-powered-by');
		header_remove('server');
	}
}
