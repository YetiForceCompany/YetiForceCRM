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
	 * Library json files.
	 *
	 * @var string[]
	 */
	public static $jsonFiles = ['package.json', 'composer.json', 'bower.json'];
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
		'microplugin' => 'Apache-2.0',
		'@fortawesome/fontawesome-free-regular' => 'MIT',
		'@fortawesome/fontawesome-free-solid' => 'MIT',
	];
	/**
	 * Information about forks CRM.
	 *
	 * @var array
	 */
	public static $libraries = ['Vtiger' => ['name' => 'Vtiger', 'version' => '6.4.0 rev. 14548', 'license' => 'VPL 1.1', 'homepage' => 'https://www.vtiger.com/', 'notPackageFile' => true], 'Sugar' => ['name' => 'Sugar CRM', 'version' => '', 'license' => 'SPL', 'homepage' => 'https://www.sugarcrm.com/', 'notPackageFile' => true]];

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
					$libraryDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
					$libraries[$package['name']] = self::getLibraryValues($package['name'], $libraryDir);
					if (!empty($package['version'])) {
						$libraries[$package['name']]['version'] = $package['version'];
					}
					if (isset(static::$licenses[$package['name']])) {
						$libraries[$package['name']]['license'] = static::$licenses[$package['name']];
					} elseif (count($package['license']) > 1) {
						$libraries[$package['name']]['license'] = implode(', ', $package['license']);
						$libraries[$package['name']]['licenseError'] = true;
					} else {
						$libraries[$package['name']]['license'] = $package['license'][0];
					}
					if (!empty($package['homepage'])) {
						$libraries[$package['name']]['homepage'] = $package['homepage'] ?? 'https://packagist.org/packages/' . $package['name'];
					} elseif (empty($libraries[$package['name']]['homepage'])) {
						$libraries[$package['name']]['homepage'] = 'https://packagist.org/packages/' . $package['name'];
					}
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
				$libraryDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR;
				foreach ($yarnFile['lockfileEntries'] as $nameWithVersion => $page) {
					$isPrefix = strpos($nameWithVersion, '@') === 0;
					$name = $isPrefix ? '@' : '';
					$name .= array_shift(explode('@', $isPrefix ? ltrim($nameWithVersion, '@') : $nameWithVersion));
					$libraries[$name] = self::getLibraryValues($name, $libraryDir);
				}
			}
		}
		return $libraries;
	}

	/**
	 * Function return library values.
	 *
	 * @param string $name
	 * @param string $dir
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function getLibraryValues($name, $dir)
	{
		$library = ['name' => $name, 'version' => '', 'license' => '', 'homepage' => ''];
		$existJsonFiles = true;
		foreach (self::$jsonFiles as $file) {
			$packageFile = $dir . $name . DIRECTORY_SEPARATOR . $file;
			if (file_exists($packageFile)) {
				$existJsonFiles = false;
				$packageFileContent = \App\Json::decode(file_get_contents($packageFile), true);
				$license = self::getLicenseForPublic($packageFileContent, $name);
				if (!empty($license['license']) && empty($library['license'])) {
					$library['licenseError'] = $license['error'];
					$library['license'] = $license['license'];
				}
				if (!empty($packageFileContent['version']) && empty($library['version'])) {
					$library['version'] = $packageFileContent['version'];
				}
				if (!empty($packageFileContent['homepage']) && empty($library['homepage'])) {
					$library['homepage'] = $packageFileContent['homepage'];
				}
			}
		}
		if ($existJsonFiles) {
			$library['packageFileMissing'] = true;
		}
		return $library;
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
		$license = $packageFileContent['license'] ?? $packageFileContent['licenses'];
		if (isset(static::$licenses[$libraryName])) {
			$returnLicense = static::$licenses[$libraryName];
		} elseif (is_array($license)) {
			if (is_array($license[0])) {
				$returnLicense = implode(',', array_column($license, 'type'));
			} elseif (is_string($license[0])) {
				$licenseError = self::validateLicenseName($license[0]);
				$returnLicense = $license[0];
			} else {
				$returnLicense = implode(',', $license);
				$licenseError = true;
			}
		} else {
			$licenseError = self::validateLicenseName($license);
			$returnLicense = $license;
		}
		return ['license' => $returnLicense, 'error' => $licenseError];
	}

	/**
	 * Function check correct of license.
	 *
	 * @param array|string $license
	 *
	 * @return bool
	 */
	public static function validateLicenseName($license)
	{
		if (!$license) {
			return true;
		}
		$result= false;
		if (!is_array($license)) {
			$license =[$license];
		}
		foreach ($license as $value) {
			if (stripos($value, 'and') || stripos($value, ' or ') || $value === null) {
				$result = true;
			}
		}
		return $result;
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
