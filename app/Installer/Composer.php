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
		'.editorconfig',
		'.styleci.yml',
		'.travis.yml',
		'mkdocs.yml',
		'.php_cs.dist',
		'phpunit.xml.dist',
		'build.xml',
		'changelog.phpexcel.md',
		'changes.md',
		'contributing.md',
		'readme.md',
		'SECURITY.md',
		'docs',
		'demo',
		'examples',
		'news',
		'phorum',
		'readme',
		'samples',
		'todo',
		'test',
		'tests',
		'whatsnew',
		'wysiwyg',
		'VERSION',
		'composer_release_notes.txt',
		'change_log.txt',
		'cldr-version.txt',
		'inheritance_release_notes.txt',
		'new_features.txt',
		'metadata-version.txt',
		'smarty_2_bc_notes.txt',
		'smarty_3.0_bc_notes.txt',
		'smarty_3.1_notes.txt',
		'test-settings.sample.php',
		'test-settings.travis.php'
	];

	public static $clearFilesModule = [
		'dg/rss-php' => [
			'example-atom.php',
			'example-rss.php'
		],
		'illuminate/support' => [
			'Debug'
		]
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
						rename($item->getRealPath(), $rootDir . $item->getPathname());
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
		$objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootDir), \RecursiveIteratorIterator::SELF_FIRST);
		foreach ($objects as $name => $object) {
			if ($object->getFilename() === '.' || $object->getFilename() === '..') {
				continue;
			}
			if ((\in_array(strtolower($object->getFilename()), self::$clearFiles)) && (is_dir($object->getFilename() || file_exists($object->getFilename())))) {
				\vtlib\Functions::recurseDelete($object->getPathname(), true, true);
			}
		}
		foreach (new \DirectoryIterator($rootDir) as $level1) {
			if ($level1->isDir() && !$level1->isDot()) {
				foreach (new \DirectoryIterator($level1->getPathname()) as $level2) {
					if ($level2->isDir() && !$level2->isDot()) {
						foreach (new \DirectoryIterator($level2->getPathname()) as $level3) {
							if (isset(self::$clearFilesModule[$level1->getFileName() . '/' . $level2->getFilename()])) {
								if (!$level3->isDot() && \in_array($level3->getFilename(), self::$clearFilesModule[$level1->getFileName() . '/' . $level2->getFilename()])) {
									\vtlib\Functions::recurseDelete($level3->getPathname(), true);
								}
							}
						}
					}
				}
			}
		}
	}
}
