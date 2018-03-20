<?php

namespace App\Installer;

/**
 * Composer installer.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Composer
{
	/**
	 * List of public packages.
	 *
	 * @var atring[]
	 */
	public static $publicPackage = [
	];

	/**
	 * Post update and post install function.
	 *
	 * @param \Composer\Script\Event $event
	 */
	public static function install(\Composer\Script\Event $event)
	{
		$rootDir = realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR;
		foreach (static::$publicPackage as $package) {
			$src = 'vendor' . DIRECTORY_SEPARATOR . $package;
			foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isFile() && ($item->getExtension() === 'js' || $item->getExtension() === 'css')) {
					if (!file_exists($rootDir . 'public_html' . DIRECTORY_SEPARATOR . $item->getPathname())) {
						if (!is_dir($rootDir . 'public_html' . DIRECTORY_SEPARATOR . $item->getPath())) {
							mkdir($rootDir . 'public_html' . DIRECTORY_SEPARATOR . $item->getPath(), null, true);
						}
						copy($item->getRealPath(), $rootDir . 'public_html' . DIRECTORY_SEPARATOR . $item->getPathname());
					}
				}
			}
		}
	}
}
