<?php

namespace App\Installer;

/**
 * Get info about libraries.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Credits
{
	/**
	 * Information about libraries license.
	 *
	 * @var array
	 */
	public static $licenses = [
		'ckeditor/ckeditor' => 'MPL-1.1+',
		'block-ui' => 'MIT',
		'dompurify' => 'Apache-2.0',
		'html5shiv' => 'MIT',
		'jquery-slimscroll' => 'MIT',
		'optimist' => 'MIT',
		'updated-jqplot' => 'MIT',
		'color-convert' => 'MIT',
		'humanize' => 'MIT',
		'microplugin'=>'Apache-2.0'
	];
	/**
	 * Information about forks CRM.
	 *
	 * @var array
	 */
	public static $libraries = ['Vtiger' => ['name' => 'Vtiger', 'version' => '6.4.0 rev. 14548', 'license' => 'VPL 1.1', 'homepage' => 'https://www.vtiger.com/'], 'Sugar' => ['name' => 'Sugar CRM', 'version' => '', 'license' => 'SPL', 'homepage' => 'https://www.sugarcrm.com/']];

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
		if (file_exists(ROOT_DIRECTORY . '/composer.lock')) {
			$composerLock = \App\Json::decode(file_get_contents(ROOT_DIRECTORY . '/composer.lock'), true);
			if ($composerLock && $composerLock['packages']) {
				foreach ($composerLock['packages'] as $package) {
					$libraries[$package['name']]['name'] = $package['name'];
					$libraries[$package['name']]['version'] = $package['version'];
					if (isset(static::$licenses[$package['name']])) {
						$libraries[$package['name']]['license'] = static::$licenses[$package['name']];
					} elseif (count($package['license']) > 1) {
						$libraries[$package['name']]['license'] = implode(', ', $package['license']);
						$libraries[$package['name']]['licenseError'] = true;
					} else {
						$libraries[$package['name']]['license'] = $package['license'][0];
					}
					$libraries[$package['name']]['homepage'] = $package['homepage'];
				}
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
		if (file_exists($dir . '.yarn-integrity')) {
			$yarnFile = \App\Json::decode(file_get_contents($dir . '.yarn-integrity'), true);
			if ($yarnFile && $yarnFile['lockfileEntries']) {
				foreach ($yarnFile['lockfileEntries'] as $nameWithVersion => $page) {
					$isPrefix = strpos($nameWithVersion, '@') === 0;
					$name = $isPrefix ? '@' : '';
					$name .= array_shift(explode('@', $isPrefix ? ltrim($nameWithVersion, '@') : $nameWithVersion));
					$libraries[$name]['name'] = $name;
					$libraries[$name]['homepage'] = $page;
					$packageFile = $dir . $name . DIRECTORY_SEPARATOR . 'package.json';
					if (file_exists($packageFile)) {
						$packageFileContent = \App\Json::decode(file_get_contents($packageFile), true);
						$libraries[$name]['version'] = $packageFileContent['version'];
						$license = self::getLicenseForPublic($packageFileContent, $name);
						$libraries[$name]['licenseError'] = $license['error'];
						if (!empty($license['license'])) {
							$libraries[$name]['license'] = $license['license'];
						}
					} else {
						$libraries[$name]['packageFileMissing'] = true;
					}
					if (empty($libraries[$name]['license'])) {
						$packageFile = $dir . $name . DIRECTORY_SEPARATOR . 'composer.json';
						if (file_exists($packageFile)) {
							$package = \App\Json::decode(file_get_contents($packageFile), true);
							if (count($package['license']) > 1) {
								$libraries[$name]['license'] = implode(', ', $package['license']);
								$libraries[$name]['licenseError'] = true;
							} else {
								$libraries[$name]['license'] = $package['license'][0];
							}
						}
					}
					if (empty($libraries[$name]['license'])) {
						$packageFile = $dir . $name . DIRECTORY_SEPARATOR . 'bower.json';
						if (file_exists($packageFile)) {
							$content = \App\Json::decode(file_get_contents($packageFile), true);
							if (!empty($content['license'])) {
								$libraries[$name]['license'] = $content['license'];
							}
						}
					}
				}
			}
		}
		return $libraries;
	}

	/**
	 * Function return license for public library.
	 *
	 * @param array  $license
	 * @param string $libraryName
	 *
	 * @return array
	 */
	public static function getLicenseForPublic($packageFileContent, $libraryName)
	{
		$licenseError = false;
		$returnLicense = '';
		$license = $packageFileContent['licenses'] ?? $packageFileContent['license'];
		if (isset(static::$licenses[$libraryName])) {
			$returnLicense = static::$licenses[$libraryName];
		} elseif (is_array($license)) {
			if (count($license[0]) > 1) {
				$returnLicense =implode(',', array_column($license, 'type'));
			} else {
				$returnLicense = implode(',', $license);
			}
			$licenseError = true;
		} else {
			$returnLicense = $license;
		}
		return ['license'=> $returnLicense, 'error' =>$licenseError];
	}

	/**
	 * Function returns information abouts libraries.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function getCredits()
	{
		return ['static' => static::$libraries, 'vendor' => self::getVendorLibraries(), 'public' => self::getPublicLibraries()];
	}
}
