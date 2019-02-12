<?php

namespace App;

/**
 * Request Utils basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class RequestUtil
{
	protected static $browserCache = false;
	private static $ipFields = [
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_CF_CONNECTING_IP',
	];
	/**
	 * Net connection cache.
	 *
	 * @var
	 */
	private static $connectionCache;

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
		foreach (static::$ipFields as $key) {
			if (isset($_SERVER[$key])) {
				$remoteIp[] = "$key: " . Request::_getServer($key);
			}
		}
		if (!empty($remoteIp)) {
			$address .= '(' . implode(',', $remoteIp) . ')';
		}
		return empty($address) ? '' : $address;
	}

	public static function getBrowserInfo()
	{
		if (static::$browserCache === false) {
			$browserAgent = strtolower(\App\Request::_getServer('HTTP_USER_AGENT', ''));

			$browser = new \stdClass();
			$browser->ver = 0;
			$browser->win = strpos($browserAgent, 'win') !== false;
			$browser->mac = strpos($browserAgent, 'mac') !== false;
			$browser->linux = strpos($browserAgent, 'linux') !== false;
			$browser->unix = strpos($browserAgent, 'unix') !== false;
			$browser->webkit = strpos($browserAgent, 'applewebkit') !== false;
			$browser->opera = strpos($browserAgent, 'opera') !== false || ($browser->webkit && strpos($browserAgent, 'opr/') !== false);
			$browser->ns = strpos($browserAgent, 'netscape') !== false;
			$browser->chrome = !$browser->opera && strpos($browserAgent, 'chrome') !== false;
			$browser->ie = !$browser->opera && (strpos($browserAgent, 'compatible; msie') !== false || strpos($browserAgent, 'trident/') !== false);
			$browser->safari = !$browser->opera && !$browser->chrome && ($browser->webkit || strpos($browserAgent, 'safari') !== false);
			$browser->mz = !$browser->ie && !$browser->safari && !$browser->chrome && !$browser->ns && !$browser->opera && strpos($browserAgent, 'mozilla') !== false;

			if (strpos($browserAgent, 'msie') !== false) {
				$browser->name = 'Internet explorer';
			} elseif (strpos($browserAgent, 'trident') !== false) { //For Supporting IE 11
				$browser->name = 'Internet explorer';
			} elseif (strpos($browserAgent, 'firefox') !== false) {
				$browser->name = 'Mozilla Firefox';
			} elseif (strpos($browserAgent, 'chrome') !== false) {
				$browser->name = 'Google Chrome';
			} elseif (strpos($browserAgent, 'opera mini') !== false) {
				$browser->name = 'Opera Mini';
			} elseif (strpos($browserAgent, 'opera') !== false) {
				$browser->name = 'Opera';
			} elseif (strpos($browserAgent, 'safari') !== false) {
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

			$browser->https = false;
			if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
				$browser->https = true;
			}
			if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
				$browser->https = true;
			}
			$sp = strtolower(Request::_getServer('SERVER_PROTOCOL'));
			$protocol = substr($sp, 0, strpos($sp, '/')) . (($browser->https) ? 's' : '');
			$port = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 0;
			$port = ((!$browser->https && $port === 80) || ($browser->https && $port === 443)) ? '' : ':' . $port;
			$host = Request::_getServer('HTTP_X_FORWARDED_HOST', Request::_getServer('HTTP_HOST', ''));
			$host = $host ?? Request::_getServer('SERVER_NAME') . $port;
			$browser->url = $protocol . '://' . $host . Request::_getServer('REQUEST_URI');
			$browser->requestUri = ltrim(Request::_getServer('REQUEST_URI'), '/');
			static::$browserCache = $browser;
		}
		return static::$browserCache;
	}

	/**
	 * Check net connection.
	 *
	 * @return bool
	 */
	public static function isNetConnection()
	{
		if (!\AppConfig::performance('ACCESS_TO_INTERNET')) {
			return false;
		}
		if (isset(static::$connectionCache)) {
			return static::$connectionCache;
		}
		return static::$connectionCache = gethostbyname('www.google.com') !== 'www.google.com';
	}
}
