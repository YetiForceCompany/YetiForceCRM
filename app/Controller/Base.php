<?php

namespace App\Controller;

/**
 * Abstract base controller class.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class Base
{
	/**
	 * Request instance.
	 *
	 * @var \App\Request
	 */
	public $request;
	/**
	 * Check login required permission variable.
	 *
	 * @var bool
	 */
	public $loginRequired = true;

	/**
	 * Construct.
	 */
	public function init()
	{
		\App\Session::start();
		$this->setHeaders();
		if (\Config\Performance::$CHANGE_LOCALE) {
			\App\Language::initLocale();
		}
	}

	/**
	 * Function to check permission.
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	abstract public function checkPermission();

	/**
	 * Process function.
	 */
	abstract public function process();

	/**
	 * Pre process function.
	 */
	public function preProcess()
	{
	}

	/**
	 * Post process function.
	 */
	public function postProcess()
	{
	}

	/**
	 * Gets user ID.
	 *
	 * @return int User ID
	 */
	protected function getLoggedUserId(): int
	{
		return \App\Session::get('authenticated_user_id');
	}

	/**
	 * Sets headers.
	 */
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
		if (\Config\Security::$CSP_ACTIVE) {
			// 'nonce-" . App\Session::get('CSP_TOKEN') . "'
			$allowed = \implode(' ', \App\Config::security('PURIFIER_ALLOWED_DOMAINS'));
			header("content-security-policy: default-src 'self' blob:; img-src 'self' data: a.tile.openstreetmap.org b.tile.openstreetmap.org c.tile.openstreetmap.org $allowed; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' blob:; form-action 'self' ;connect-src 'self' ws: wss:;");
		}
		if ($keys = \Config\Security::$HPKP_KEYS) {
			header('public-key-pins: pin-sha256="' . implode('"; pin-sha256="', $keys) . '"; max-age=10000;');
		}
		header_remove('x-powered-by');
		header_remove('server');
	}
}
