<?php
namespace App;

/**
 * Request Utils basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class RequestUtil
{

	public static function getRemoteIP($onlyIP = false)
	{
		$address = $_SERVER['REMOTE_ADDR'];
		// append the NGINX X-Real-IP header, if set
		if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
			$remoteIp[] = 'X-Real-IP: ' . $_SERVER['HTTP_X_REAL_IP'];
		}
		// append the X-Forwarded-For header, if set
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$remoteIp[] = 'X-Forwarded-For: ' . $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		if ($onlyIP === false && !empty($remoteIp)) {
			$address .= '(' . implode(',', $remoteIp) . ')';
		}
		return $address;
	}

	protected static $browerCache = false;

	public static function getBrowserInfo()
	{
		if (static::$browerCache === false) {
			$browserAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

			$browser = new \stdClass;
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

			if (strpos($browserAgent, 'msie') !== false)
				$browser->name = 'Internet explorer';
			elseif (strpos($browserAgent, 'trident') !== false) //For Supporting IE 11
				$browser->name = 'Internet explorer';
			elseif (strpos($browserAgent, 'firefox') !== false)
				$browser->name = 'Mozilla Firefox';
			elseif (strpos($browserAgent, 'chrome') !== false)
				$browser->name = 'Google Chrome';
			elseif (strpos($browserAgent, 'opera mini') !== false)
				$browser->name = 'Opera Mini';
			elseif (strpos($browserAgent, 'opera') !== false)
				$browser->name = 'Opera';
			elseif (strpos($browserAgent, 'safari') !== false)
				$browser->name = 'Safari';
			else
				$browser->name = 'unknow';

			if ($browser->opera) {
				if (preg_match('/(opera|opr)\/([0-9.]+)/', $browserAgent, $regs)) {
					$browser->ver = (float) $regs[2];
				}
			} else if (preg_match('/(chrome|msie|version|khtml)(\s*|\/)([0-9.]+)/', $browserAgent, $regs)) {
				$browser->ver = (float) $regs[3];
			} else if (preg_match('/rv:([0-9.]+)/', $browserAgent, $regs)) {
				$browser->ver = (float) $regs[1];
			}

			if (preg_match('/ ([a-z]{2})-([a-z]{2})/', $browserAgent, $regs))
				$browser->lang = $regs[1];
			else
				$browser->lang = 'en';

			$browser->https = false;
			if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
				$browser->https = true;
			}
			if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
				$browser->https = true;
			}
			$sp = strtolower($_SERVER['SERVER_PROTOCOL']);
			$protocol = substr($sp, 0, strpos($sp, '/')) . (($browser->https) ? 's' : '');
			$port = (int) $_SERVER['SERVER_PORT'];
			$port = ((!$browser->https && $port === 80) || ($browser->https && $port === 443)) ? '' : ':' . $port;
			$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
			$host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $port;
			$browser->url = $protocol . '://' . $host . $_SERVER['REQUEST_URI'];
			$browser->requestUri = ltrim($_SERVER['REQUEST_URI'], '/');
			static::$browerCache = $browser;
		}
		return static::$browerCache;
	}
}
