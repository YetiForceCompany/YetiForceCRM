<?php
/**
 * Request Utils basic file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Request Utils basic class.
 */
class RequestUtil
{
	/** @var stdClass Browser cache variable. */
	protected static $browserCache;

	/** @var bool Cache https check variable. */
	protected static $httpsCache;

	/** @var bool Net connection cache. */
	protected static $connectionCache;

	/** @var string Cache request id variable. */
	protected static $requestId;

	/**
	 * IP fields names variable.
	 *
	 * @var string[]
	 */
	protected static $ipFields = [
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_CF_CONNECTING_IP',
	];

	public static function getRemoteIP($onlyIP = false)
	{
		$address = Request::_getServer('REMOTE_ADDR');
		if ($onlyIP) {
			return empty($address) ? '' : $address;
		}
		// append the NGINX X-Real-IP header, if set
		if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
			$remoteIp[] = 'X-Real-IP: ' . Request::_getServer('HTTP_X_REAL_IP');
		}
		foreach (self::$ipFields as $key) {
			if (isset($_SERVER[$key])) {
				$remoteIp[] = "$key: " . Request::_getServer($key);
			}
		}
		if (!empty($remoteIp)) {
			$address .= '(' . implode(',', $remoteIp) . ')';
		}
		return empty($address) ? '' : $address;
	}

	/**
	 * Get browser details.
	 *
	 * @return object
	 */
	public static function getBrowserInfo(): object
	{
		if (empty(self::$browserCache)) {
			$browserAgent = strtolower(\App\Request::_getServer('HTTP_USER_AGENT', ''));

			$browser = new \stdClass();
			$browser->ver = 0;
			$browser->win = false !== strpos($browserAgent, 'win');
			$browser->mac = false !== strpos($browserAgent, 'mac');
			$browser->linux = false !== strpos($browserAgent, 'linux');
			$browser->unix = false !== strpos($browserAgent, 'unix');
			$browser->webkit = false !== strpos($browserAgent, 'applewebkit');
			$browser->opera = false !== strpos($browserAgent, 'opera') || ($browser->webkit && false !== strpos($browserAgent, 'opr/'));
			$browser->ns = false !== strpos($browserAgent, 'netscape');
			$browser->chrome = !$browser->opera && false !== strpos($browserAgent, 'chrome');
			$browser->ie = !$browser->opera && (false !== strpos($browserAgent, 'compatible; msie') || false !== strpos($browserAgent, 'trident/'));
			$browser->safari = !$browser->opera && !$browser->chrome && ($browser->webkit || false !== strpos($browserAgent, 'safari'));
			$browser->mz = !$browser->ie && !$browser->safari && !$browser->chrome && !$browser->ns && !$browser->opera && false !== strpos($browserAgent, 'mozilla');

			if (false !== strpos($browserAgent, 'msie')) {
				$browser->name = 'Internet explorer';
			} elseif (false !== strpos($browserAgent, 'trident')) { //For Supporting IE 11
				$browser->name = 'Internet explorer';
			} elseif (false !== strpos($browserAgent, 'firefox')) {
				$browser->name = 'Mozilla Firefox';
			} elseif (false !== strpos($browserAgent, 'chrome')) {
				$browser->name = 'Google Chrome';
			} elseif (false !== strpos($browserAgent, 'opera mini')) {
				$browser->name = 'Opera Mini';
			} elseif (false !== strpos($browserAgent, 'opera')) {
				$browser->name = 'Opera';
			} elseif (false !== strpos($browserAgent, 'safari')) {
				$browser->name = 'Safari';
			} else {
				$browser->name = 'unknow';
			}

			if ($browser->opera) {
				if (preg_match('/(opera|opr)\/([0-9.]+)/', $browserAgent, $regs)) {
					$browser->ver = (float) $regs[2];
				}
			} elseif (preg_match('/(chrome|msie|version|khtml)(\s*|\/)([0-9.]+)/', $browserAgent, $regs)) {
				$browser->ver = (float) $regs[3];
			} elseif (preg_match('/rv:([0-9.]+)/', $browserAgent, $regs)) {
				$browser->ver = (float) $regs[1];
			}

			if (preg_match('/ ([a-z]{2})-([a-z]{2})/', $browserAgent, $regs)) {
				$browser->lang = $regs[1];
			} else {
				$browser->lang = 'en';
			}
			$browser->https = self::isHttps();
			$sp = strtolower(Request::_getServer('SERVER_PROTOCOL'));
			$protocol = substr($sp, 0, strpos($sp, '/')) . (($browser->https) ? 's' : '');
			$port = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 0;
			$port = ((!$browser->https && 80 === $port) || ($browser->https && 443 === $port)) ? '' : ':' . $port;
			$host = Request::_getServer('HTTP_X_FORWARDED_HOST', Request::_getServer('HTTP_HOST', ''));
			$host = $host ?? Request::_getServer('SERVER_NAME') . $port;
			$dirPath = explode('/', Request::_getServer('SCRIPT_NAME'));
			array_pop($dirPath);
			$dirPath = implode('/', $dirPath);
			$browser->url = $protocol . '://' . $host . Request::_getServer('REQUEST_URI');
			$browser->siteUrl = $protocol . '://' . $host . $dirPath . '/';
			$browser->requestUri = ltrim(Request::_getServer('REQUEST_URI'), '/');
			self::$browserCache = $browser;
		}
		return self::$browserCache;
	}

	/**
	 * Check net connection.
	 *
	 * @return bool
	 */
	public static function isNetConnection(): bool
	{
		if (!\App\Config::performance('ACCESS_TO_INTERNET')) {
			return false;
		}
		if (isset(self::$connectionCache)) {
			return self::$connectionCache;
		}
		return self::$connectionCache = 'www.google.com' !== gethostbyname('www.google.com');
	}

	/**
	 * Check that the connection is https.
	 *
	 * @return bool
	 */
	public static function isHttps(): bool
	{
		if (!isset(self::$httpsCache)) {
			self::$httpsCache = (!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']))
				|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']));
		}
		return self::$httpsCache;
	}

	/**
	 * Get the IP address corresponding to a given Internet host name.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getIpByName(string $name): string
	{
		if (!self::isNetConnection()) {
			return false;
		}
		if (\App\Cache::has(__METHOD__, $name)) {
			return \App\Cache::get(__METHOD__, $name);
		}
		$ip = gethostbyname($name);
		if ($ip === $name) {
			$ip = '';
		}
		return \App\Cache::save(__METHOD__, $name, $ip);
	}

	/**
	 * Get request id.
	 *
	 * @return string
	 */
	public static function requestId(): string
	{
		if (empty(self::$requestId)) {
			self::$requestId = sprintf('%08x', abs(crc32($_SERVER['REMOTE_ADDR'] . $_SERVER['REQUEST_TIME_FLOAT'] . $_SERVER['REMOTE_PORT'])));
		}
		return self::$requestId;
	}
}
