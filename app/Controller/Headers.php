<?php
/**
 * Headers controller file.
 *
 * @package Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller;

/**
 * Headers controller class.
 */
class Headers
{
	/**
	 * Default header values.
	 *
	 * @var string[]
	 */
	protected $headers = [
		'Access-Control-Allow-Methods' => 'GET, POST',
		'Access-Control-Allow-Origin' => '*',
		'Expires' => '-',
		'Last-Modified' => '-',
		'Pragma' => 'no-cache',
		'Cache-Control' => 'private, no-cache, no-store, must-revalidate, post-check=0, pre-check=0',
		'Content-Type' => 'text/html; charset=UTF-8',
		'Referrer-Policy' => 'no-referrer',
		'Expect-Ct' => 'enforce; max-age=3600',
		'X-Frame-Options' => 'sameorigin',
		'X-Xss-Protection' => '1; mode=block',
		'X-Content-Type-Options' => 'nosniff',
		'X-Robots-Tag' => 'none',
		'X-Permitted-Cross-Domain-Policies' => 'none',
	];
	/**
	 * Default CSP header values.
	 *
	 * @var string[]
	 */
	public $csp = [
		'default-src' => '\'self\' blob:',
		'img-src' => '\'self\' data:',
		'font-src' => '\'self\' data:',
		'script-src' => '\'self\' \'unsafe-inline\' blob:',
		'form-action' => '\'self\'',
		'frame-ancestors' => '\'self\'',
		'frame-src' => '\'self\' mailto: tel:',
		'style-src' => '\'self\' \'unsafe-inline\'',
		'connect-src' => '\'self\'',
	];
	/**
	 * Headers to delete.
	 *
	 * @var string[]
	 */
	protected $headersToDelete = ['X-Powered-By', 'Server'];

	/**
	 * Headers instance..
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * Get headers instance.
	 *
	 * @return \self
	 */
	public static function getInstance()
	{
		if (isset(self::$instance)) {
			return self::$instance;
		}
		return self::$instance = new self();
	}

	/**
	 * Construct, loads default headers depending on the browser and environment.
	 */
	public function __construct()
	{
		$browser = \App\RequestUtil::getBrowserInfo();
		$this->headers['Expires'] = gmdate('D, d M Y H:i:s') . ' GMT';
		$this->headers['Last-Modified'] = gmdate('D, d M Y H:i:s') . ' GMT';
		if ($browser->ie) {
			$this->headers['X-Ua-Compatible'] = 'IE=11,edge';
			if ($browser->https) {
				$this->headers['Pragma'] = 'private';
				$this->headers['Cache-Control'] = 'private, must-revalidate';
			}
		}
		if ($browser->https) {
			$this->headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
		}
		if (\App\Config::security('cspHeaderActive')) {
			$this->loadCsp();
		}
		if ($keys = \App\Config::security('hpkpKeysHeader')) {
			$this->headers['Public-Key-Pins'] = 'pin-sha256="' . implode('"; pin-sha256="', $keys) . '"; max-age=10000;';
		}
	}

	/**
	 * Set header.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function setHeader(string $key, string $value)
	{
		$this->headers[$key] = $value;
	}

	/**
	 * Send headers.
	 *
	 * @return void
	 */
	public function send()
	{
		if (headers_sent()) {
			return;
		}
		foreach ($this->getHeaders() as $value) {
			header($value);
		}
		foreach ($this->headersToDelete as $name) {
			header_remove($name);
		}
	}

	/**
	 * Get headers string.
	 *
	 * @return string[]
	 */
	public function getHeaders(): array
	{
		if (\App\Config::security('cspHeaderActive')) {
			$this->headers['Content-Security-Policy'] = $this->getCspHeader();
		}
		$return = [];
		foreach ($this->headers as $name => $value) {
			$return[] = "$name: $value";
		}
		return $return;
	}

	/**
	 * Load CSP directive.
	 *
	 * @return void
	 */
	public function loadCsp()
	{
		if (\Config\Security::$generallyAllowedDomains) {
			$this->csp['default-src'] .= ' ' . \implode(' ', \Config\Security::$generallyAllowedDomains);
		}
		if (\Config\Security::$allowedImageDomains) {
			$this->csp['img-src'] .= ' ' . \implode(' ', \Config\Security::$allowedImageDomains);
		}
		if (\Config\Security::$allowedScriptDomains) {
			$this->csp['script-src'] .= ' ' . \implode(' ', \Config\Security::$allowedScriptDomains);
		}
		if (\Config\Security::$allowedFormDomains) {
			$this->csp['form-action'] .= ' ' . \implode(' ', \Config\Security::$allowedFormDomains);
		}
		if (\Config\Security::$allowedFrameDomains) {
			$this->csp['frame-ancestors'] .= ' ' . \implode(' ', \Config\Security::$allowedFrameDomains);
		}
		if (\Config\Security::$allowedConnectDomains) {
			$this->csp['connect-src'] .= ' ' . \implode(' ', \Config\Security::$allowedConnectDomains);
		}
		if (\Config\Security::$allowedDomainsLoadInFrame) {
			$this->csp['frame-src'] .= ' ' . \implode(' ', \Config\Security::$allowedDomainsLoadInFrame);
		}
	}

	/**
	 * Get CSP headers string.
	 *
	 * @return string
	 */
	public function getCspHeader(): string
	{
		$scp = '';
		foreach ($this->csp as $key => $value) {
			$scp .= "$key $value; ";
		}
		return $scp;
	}

	/**
	 * Generate Content Security Policy token.
	 *
	 * @return void
	 */
	public static function generateCspToken(): void
	{
		\App\Session::set('CSP_TOKEN', hash('sha256', \App\Encryption::generatePassword(10)));
	}
}
