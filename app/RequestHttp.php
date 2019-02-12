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
	public static function getOptions()
	{
		$caPathOrFile = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
		return [
			'headers' => [
				'User-Agent' => 'YetiForceCRM/' . Version::get(),
			],
			'timeout' => 10,
			'verify' => \is_file($caPathOrFile) ? $caPathOrFile : false,
		];
	}
}
