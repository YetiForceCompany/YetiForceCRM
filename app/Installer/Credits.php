<?php

namespace App\Installer;

/**
 * Get info about libraries.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		'bootstrap-tabdrop' => 'Apache-2.0',
		'color-convert' => 'MIT',
		'@fortawesome/fontawesome-free' => 'MIT',
		'fontawesome-web' => 'MIT',
		'ckeditor/ckeditor' => 'MPL-1.1+',
		'block-ui' => 'MIT',
		'jquery-slimscroll' => 'MIT',
		'html5shiv' => 'MIT',
		'jquery-lazy' => 'MIT',
		'nette/php-generator' => 'BSD-3-Clause',
		'nette/utils' => 'BSD-3-Clause',
		'@mdi/font' => 'MIT',
		'domhandler' => 'BSD-2-Clause',
		'domutils' => 'BSD-2-Clause',
		'@vue/compiler-sfc' => 'MIT',
	];

	/**
	 * Information about forks CRM.
	 *
	 * @return array
	 */
	public static function getBasicLibraries(): array
	{
		return [
			'YetiForce' => [
				'name' => 'YetiForce',
				'version' => \App\Version::get(),
				'license' => 'YetiForce Public License 5.0',
				'homepage' => 'https://yetiforce.com/en/yetiforce/license',
				'notPackageFile' => true,
				'showLicenseModal' => true,
			],
			'Vtiger' => [
				'name' => 'Vtiger',
				'version' => '6.4.0 rev. 14548',
				'license' => 'VPL 1.1', 'homepage' => 'https://www.vtiger.com/',
				'notPackageFile' => true,
				'showLicenseModal' => true,
				'description' => 'LBL_VTIGER_DESCRIPTION',
			],
			'Sugar' => [
				'name' => 'Sugar CRM',
				'version' => '',
				'license' => 'SPL-1.1.2',
				'homepage' => 'https://www.sugarcrm.com/',
				'notPackageFile' => true,
				'showLicenseModal' => true,
				'description' => 'LBL_SUGAR_DESCRIPTION',
			],
			'ChatSound' => [
				'name' => 'Notification Sounds - Time Is Now',
				'version' => '',
				'license' => 'CC-BY-4.0',
				'homepage' => 'https://notificationsounds.com/notification-sounds/time-is-now-585',
				'notPackageFile' => true,
				'showLicenseModal' => false,
				'description' => 'LBL_CHAT_SOUND_DESCRIPTION',
			], ];
	}

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
					$libraryDir = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR;
					$libraries[$package['name']] = self::getLibraryValues($package['name'], $libraryDir);
					if (!empty($package['version'])) {
						$libraries[$package['name']]['version'] = $package['version'];
					}
					if (!empty($package['license'])) {
						if (\count($package['license']) > 1) {
							$libraries[$package['name']]['license'] = implode(', ', $package['license']);
							$libraries[$package['name']]['licenseError'] = true;
						} else {
							$libraries[$package['name']]['license'] = $package['license'][0];
							$libraries[$package['name']]['showLicenseModal'] = self::checkIfLicenseFileExists($package['license'][0]);
						}
					}
					if (isset(static::$licenses[$package['name']])) {
						$libraries[$package['name']]['license'] = static::$licenses[$package['name']] . ' [' . $libraries[$package['name']]['license'] . ']';
						$libraries[$package['name']]['showLicenseModal'] = self::checkIfLicenseFileExists(static::$licenses[$package['name']]);
						$libraries[$package['name']]['licenseError'] = false;
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
		return self::getYarnLibraries(
			ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'public_html' . \DIRECTORY_SEPARATOR . 'libraries' . \DIRECTORY_SEPARATOR . '.yarn-integrity',
			ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'public_html' . \DIRECTORY_SEPARATOR . 'libraries' . \DIRECTORY_SEPARATOR
		);
	}

	/**
	 * Get libraries based on .yarn-integrity file.
	 *
	 * @param string $integrityFile
	 * @param string $srcDir
	 *
	 * @return array
	 */
	public static function getYarnLibraries(string $integrityFile, string $srcDir): array
	{
		$libraries = [];
		if (file_exists($integrityFile)) {
			$yarnFile = \App\Json::decode(file_get_contents($integrityFile), true);
			if ($yarnFile && $yarnFile['lockfileEntries']) {
				foreach ($yarnFile['lockfileEntries'] as $nameWithVersion => $page) {
					$isPrefix = 0 === strpos($nameWithVersion, '@');
					$name = $isPrefix ? '@' : '';
					$tempName = explode('@', $isPrefix ? ltrim($nameWithVersion, '@') : $nameWithVersion);
					$name .= array_shift($tempName);
					if (\is_dir($srcDir . $name)) {
						$libraries[$name] = self::getLibraryValues($name, $srcDir);
						if (empty($libraries[$name]['homepage'])) {
							$libraries[$name]['homepage'] = "https://yarnpkg.com/en/package/{$name}";
						}
					}
				}
			}
		}
		return $libraries;
	}

	/**
	 * Function gets vue libraries name.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function getVueLibs(): array
	{
		$libraries = [];
		$file = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'app_data' . \DIRECTORY_SEPARATOR . 'libraries.json';
		if (file_exists($file)) {
			foreach (\App\Json::read($file) as $name => $libDetails) {
				$libraries[$name] = $libDetails;
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
	public static function getLibraryValues($name, $dir): array
	{
		$library = ['name' => $name, 'version' => '', 'license' => '', 'homepage' => ''];
		$existJsonFiles = true;
		foreach (self::$jsonFiles as $file) {
			$packageFile = $dir . $name . \DIRECTORY_SEPARATOR . $file;
			if (file_exists($packageFile)) {
				$existJsonFiles = false;
				$packageFileContent = \App\Json::decode(file_get_contents($packageFile), true);
				if (!empty($packageFileContent['version']) && empty($library['version'])) {
					$library['version'] = $packageFileContent['version'];
				}
				if (!empty($packageFileContent['homepage']) && empty($library['homepage'])) {
					$library['homepage'] = $packageFileContent['homepage'];
				}
			}
		}
		$license = self::getLicenseInformation($dir, $name);
		if (!empty($license['license'])) {
			$library['licenseError'] = $license['error'];
			$library['license'] = $license['license'];
			$library['licenseToDisplay'] = $license['licenseToDisplay'];
			$library['showLicenseModal'] = $license['showLicenseModal'];
		}
		if ($existJsonFiles) {
			$library['packageFileMissing'] = true;
		}
		return $library;
	}

	/**
	 * Function return license information for library.
	 *
	 * @param string $dir
	 * @param string $libraryName
	 *
	 * @return array
	 */
	public static function getLicenseInformation($dir, $libraryName)
	{
		$licenseError = false;
		$returnLicense = '';
		$licenseToDisplay = '';
		$showLicenseModal = true;
		foreach (self::$jsonFiles as $file) {
			$packageFile = $dir . $libraryName . \DIRECTORY_SEPARATOR . $file;
			if (file_exists($packageFile)) {
				$packageFileContent = \App\Json::decode(file_get_contents($packageFile), true);
				$license = $packageFileContent['license'] ?? $packageFileContent['licenses'] ?? '';
				if ($license) {
					if (\is_array($license)) {
						if (isset($license['type'])) {
							$returnLicense = $license['type'];
						} elseif (isset($license[0]['type'])) {
							$returnLicense = implode(', ', array_column($license, 'type'));
						} else {
							$returnLicense = implode(', ', $license);
						}
						if (\count($license) > 1) {
							$licenseError = true;
						}
					} else {
						$licenseError = self::validateLicenseName($license);
						$returnLicense = $license;
					}
					if (isset(static::$licenses[$libraryName]) && $returnLicense) {
						$returnLicense = static::$licenses[$libraryName] . " [{$returnLicense}]";
						$licenseToDisplay = static::$licenses[$libraryName];
						$licenseError = false;
						$showLicenseModal = file_exists($dir . '..' . \DIRECTORY_SEPARATOR . 'licenses' . \DIRECTORY_SEPARATOR . $licenseToDisplay . '.txt');
						break;
					}
					if ($returnLicense) {
						$showLicenseModal = file_exists($dir . '..' . \DIRECTORY_SEPARATOR . 'licenses' . \DIRECTORY_SEPARATOR . $returnLicense . '.txt');
						break;
					}
				} else {
					if (isset(static::$licenses[$libraryName])) {
						$returnLicense = static::$licenses[$libraryName];
						$showLicenseModal = file_exists($dir . '..' . \DIRECTORY_SEPARATOR . 'licenses' . \DIRECTORY_SEPARATOR . $returnLicense . '.txt');
					}
				}
			} else {
				if (isset(static::$licenses[$libraryName])) {
					$returnLicense = static::$licenses[$libraryName];
					$showLicenseModal = file_exists($dir . '..' . \DIRECTORY_SEPARATOR . 'licenses' . \DIRECTORY_SEPARATOR . $returnLicense . '.txt');
				}
			}
		}
		return ['license' => $returnLicense, 'error' => $licenseError, 'licenseToDisplay' => $licenseToDisplay, 'showLicenseModal' => $showLicenseModal];
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
		$result = false;
		if (!\is_array($license)) {
			$license = [$license];
		}
		foreach ($license as $value) {
			if (stripos($value, 'and') || stripos($value, ' or ') || null === $value) {
				$result = true;
			}
		}
		return $result;
	}

	/**
	 * Function checks if license file existsF.
	 *
	 * @param string $license
	 *
	 * @return bool
	 */
	public static function checkIfLicenseFileExists($license)
	{
		$filePath = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'licenses' . \DIRECTORY_SEPARATOR . $license . '.txt';
		return file_exists($filePath);
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
		return ['static' => self::getBasicLibraries(), 'vendor' => self::getVendorLibraries(), 'public' => self::getPublicLibraries(), 'vue' => self::getVueLibs()];
	}
}
