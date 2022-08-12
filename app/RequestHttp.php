<?php

/**
 * Request http utils file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Request http utils class.
 */
class RequestHttp
{
	/** @var int Total timeout of the request in seconds. Use 0 to wait indefinitely (the default behavior). */
	public static $timeout = 10;

	/** @var int The number of seconds to wait while trying to connect to a server. Use 0 to wait indefinitely (the default behavior). */
	public static $connectTimeout = 2;

	/**
	 * Get GuzzleHttp client instance.
	 *
	 * @param array $config
	 *
	 * @return \GuzzleHttp\Client
	 */
	public static function getClient(array $config = []): \GuzzleHttp\Client
	{
		return new \GuzzleHttp\Client(\App\Utils::merge(self::getOptions(), $config));
	}

	/**
	 * Get default configuration for GuzzleHttp Client.
	 *
	 * @return array
	 */
	public static function getOptions(): array
	{
		$caPathOrFile = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
		$options = [
			'headers' => [
				'User-Agent' => 'YetiForceCRM/' . Version::get(),
			],
			'timeout' => self::$timeout,
			'connect_timeout' => self::$connectTimeout,
			'verify' => \is_file($caPathOrFile) ? $caPathOrFile : false,
		];
		if (!empty(\Config\Security::$proxyConnection)) {
			$proxy = '';
			if (!empty(\Config\Security::$proxyProtocol)) {
				$proxy .= \Config\Security::$proxyProtocol . '://';
			}
			if (!empty(\Config\Security::$proxyLogin)) {
				$proxy .= \Config\Security::$proxyLogin;
				if (!empty(\Config\Security::$proxyPassword)) {
					$proxy .= ':' . \Config\Security::$proxyPassword;
				}
				$proxy .= '@';
			}
			$proxy .= \Config\Security::$proxyHost;
			if (!empty(\Config\Security::$proxyPort)) {
				$proxy .= ':' . \Config\Security::$proxyPort;
			}
			$options['proxy'] = $proxy;
		}
		return $options;
	}

	/**
	 * Get default configuration for SoapClient.
	 *
	 * @return array
	 */
	public static function getSoapOptions(): array
	{
		$options = [
			'trace' => 1,
			'exceptions' => 1,
			'connection_timeout' => self::$timeout,
			'user_agent' => 'YetiForceCRM/' . Version::get(),
		];
		if (!empty(\Config\Security::$proxyConnection)) {
			$options['proxy_host'] = \Config\Security::$proxyHost;
			if (!empty(\Config\Security::$proxyPort)) {
				$options['proxy_port'] = \Config\Security::$proxyPort;
			}
			if (!empty(\Config\Security::$proxyLogin)) {
				$options['proxy_login'] = \Config\Security::$proxyLogin;
			}
			if (!empty(\Config\Security::$proxyPassword)) {
				$options['proxy_password'] = \Config\Security::$proxyPassword;
			}
		}
		return $options;
	}
}
