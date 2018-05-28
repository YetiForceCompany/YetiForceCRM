<?php

namespace App\Controller;

/**
 * Abstract base controller class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class Base
{
	/**
	 * Construct.
	 */
	public function __construct()
	{
		self::setHeaders();
	}

	/**
	 * Function to check login required permission.
	 *
	 * @return bool
	 */
	public function loginRequired()
	{
		return true;
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	abstract public function checkPermission(\App\Request $request);

	/**
	 * Process function.
	 *
	 * @param \App\Request $request
	 */
	abstract public function process(\App\Request $request);

	/**
	 * Function to validate request method.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool
	 */
	public function validateRequest(\App\Request $request)
	{
		return $request->validateReadAccess();
	}

	/**
	 * Pre process ajax function.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessAjax(\App\Request $request)
	{
	}

	/**
	 * Pre process function.
	 *
	 * @param \App\Request $request
	 * @param bool         $display
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
	}

	/**
	 * Post process function.
	 *
	 * @param \App\Request $request
	 * @param bool         $display
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
	}

	/**
	 * Post process ajax function.
	 *
	 * @param \App\Request $request
	 */
	public function postProcessAjax(\App\Request $request)
	{
	}

	/**
	 * Set HTTP Headers.
	 */
	public function setHeaders()
	{
		if (headers_sent()) {
			return;
		}
		$browser = \App\RequestUtil::getBrowserInfo();
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		if ($browser->ie && $browser->https) {
			header('Pragma: private');
			header('Cache-Control: private, must-revalidate');
		} else {
			header('Cache-Control: private, no-cache, no-store, must-revalidate, post-check=0, pre-check=0');
			header('Pragma: no-cache');
		}
		header('X-Frame-Options: SAMEORIGIN');
		header('X-XSS-Protection: 1; mode=block');
		header('X-Content-Type-Options: nosniff');
		header('Referrer-Policy: no-referrer');
		header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
		header('Expect-CT: enforce; max-age=3600');
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
		header('X-Robots-Tag: none');
		header('X-Permitted-Cross-Domain-Policies: none');
		if (\AppConfig::security('CSP_ACTIVE')) {
			// 'nonce-" . App\Session::get('CSP_TOKEN') . "'
			header("Content-Security-Policy: default-src 'self' blob:; img-src 'self' data: a.tile.openstreetmap.org b.tile.openstreetmap.org c.tile.openstreetmap.org; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' blob:; form-action 'self' ;connect-src 'self';");
		}
		if ($keys = \AppConfig::security('HPKP_KEYS')) {
			header('Public-Key-Pins: pin-sha256="' . implode('"; pin-sha256="', $keys) . '"; max-age=10000;');
		}
		header_remove('X-Powered-By');
		header_remove('Server');
	}

	/**
	 * Function to check if session is extend.
	 *
	 * @return bool
	 */
	public function isSessionExtend()
	{
		return true;
	}
}
