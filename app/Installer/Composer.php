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
		'yetiforce/csrf-magic',
		'yetiforce/debugbar',
		'ckeditor/ckeditor'
	];
	/**
	 * List of redundant files.
	 *
	 * @var array
	 */
	public static $clearFiles = [
		'.github',
		'.git',
		'.gitattributes',
		'.gitignore',
		'.styleci.yml',
		'.travis.yml',
		'samples',
		'docs',
		'bin'
	];

	/**
	 * Post update and post install function.
	 *
	 * @param \Composer\Script\Event $event
	 */
	public static function install(\Composer\Script\Event $event)
	{
		$event->getComposer();
		if (isset($_SERVER['SENSIOLABS_EXECUTION_NAME'])) {
			return true;
		}
		$rootDir = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR;
		$types = ['js', 'css', 'woff', 'woff2', 'ttf', 'png', 'gif', 'jpg'];
		foreach (static::$publicPackage as $package) {
			$src = 'vendor' . DIRECTORY_SEPARATOR . $package;
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isFile() && in_array($item->getExtension(), $types)) {
					if (!file_exists($rootDir . $item->getPathname())) {
						if (!is_dir($rootDir . $item->getPath())) {
							mkdir($rootDir . $item->getPath(), 0755, true);
						}
						if (!is_writable($rootDir . $item->getPath())) {
							continue;
						}
						copy($item->getRealPath(), $rootDir . $item->getPathname());
					}
				}
			}
		}
		static::clear();
	}

	/**
	 * Delete redundant files.
	 */
	public static function clear()
	{
		$rootDir = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
		foreach (new \DirectoryIterator($rootDir) as $level1) {
			if ($level1->isDir() && !$level1->isDot()) {
				foreach (new \DirectoryIterator($level1->getPathname()) as $level2) {
					if ($level2->isDir() && !$level2->isDot()) {
						foreach (new \DirectoryIterator($level2->getPathname()) as $level3) {
							if (!$level3->isDot() && \in_array($level3->getFilename(), static::$clearFiles)) {
								\vtlib\Functions::recurseDelete($level3->getPathname(), true);
							}
						}
					}
				}
			}
		}
	}
}
