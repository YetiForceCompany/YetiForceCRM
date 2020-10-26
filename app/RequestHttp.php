<?php

/**
 * Request http class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Class RequestHttp.
 */
class RequestHttp
{
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
			'timeout' => 10,
			'connect_timeout' => 2,
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
			'connection_timeout' => 10,
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
