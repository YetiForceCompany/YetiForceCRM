<?php

namespace App\Installer;

/**
 * Get info about libraries.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Credits
{
	/**
	 * Function gets libraries from vendor.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function getVendorLibraries()
	{
		$libraries = [];
		$composerLock = \App\Json::decode(file_get_contents(ROOT_DIRECTORY . '/composer.lock'), true);
		if ($composerLock && $composerLock['packages']) {
			foreach ($composerLock['packages'] as $package) {
				$libraries[$package['name']]['name'] = $package['name'];
				$libraries[$package['name']]['version'] = $package['version'];
				$libraries[$package['name']]['license'] = $package['license'];
			}
		}
		return $libraries;
	}

	/**
	 * Function gets libraries name from public_html.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function getPublicLibraries()
	{
		$libraries = [];
		$dir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR;
		$yarnFile = \App\Json::decode(file_get_contents($dir . '.yarn-integrity'), true);
		if ($yarnFile && $yarnFile['lockfileEntries']) {
			foreach ($yarnFile['lockfileEntries'] as $nameWithVersion) {
				$name = reset(explode('@', $nameWithVersion));
				$libraries[$name]['name'] = $name;
				$packageFile = $dir . $name . DIRECTORY_SEPARATOR . 'package.json';
				if (file_exists($packageFile)) {
					$packageFileContent = \App\Json::decode(file_get_contents($packageFile), true);
					$libraries[$name]['version'] = $packageFileContent['version'];
					$libraries[$name]['license'] = $packageFileContent['license'];
				}
			}
		}
		return $libraries;
	}
}
