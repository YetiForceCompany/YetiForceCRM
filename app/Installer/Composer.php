<?php

namespace App\Installer;

/**
 * Composer installer.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Composer
{
	/**
	 * List of public packages.
	 *
	 * @var atring[]
	 */
	public static $publicPackage = [
		'yetiforce/csrf-magic' => 'move',
		'yetiforce/debugbar' => 'move',
		'yetiforce/yetiforcepdf/lib/Fonts' => 'copy',
		'ckeditor/ckeditor' => 'move',
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
		'.gitkeep',
		'.editorconfig',
		'.styleci.yml',
		'.travis.yml',
		'mkdocs.yml',
		'.coveralls.yml',
		'.scrutinizer.yml',
		'.php_cs.dist',
		'build.xml',
		'phpunit.xml.dist',
		'phpunit.xml',
		'changelog.phpexcel.md',
		'changelog.md',
		'changes.md',
		'contributing.md',
		'readme.md',
		'security.md',
		'upgrade.md',
		'docs',
		'demo',
		'examples',
		'extras',
		'install',
		'js-test',
		'maintenance',
		'migrations',
		'news',
		'phorum',
		'readme',
		'sample',
		'samples',
		'todo',
		'test',
		'tests',
		'whatsnew',
		'wysiwyg',
		'views',
		'_translationstatus.txt',
		'composer_release_notes.txt',
		'change_log.txt',
		'cldr-version.txt',
		'inheritance_release_notes.txt',
		'metadata-version.txt',
		'modx.txt',
		'news.txt',
		'new_features.txt',
		'readme.txt',
		'smarty_2_bc_notes.txt',
		'smarty_3.0_bc_notes.txt',
		'smarty_3.1_notes.txt',
		'.sami.php',
		'get_oauth_token.php',
		'test-settings.sample.php',
		'test-settings.travis.php',
		'install.fr.utf8',
		'jquery.min.js',
		'release1-update.php',
		'release2-tag.php',
		'phpdoc.ini',
		'crowdin.yml',
		'sonar-project.properties',
	];

	public static $clearFilesModule = [
		'dg/rss-php' => [
			'example-atom.php',
			'example-rss.php'
		],
		'ezyang/htmlpurifier' => [
			'plugins'
		],
		'illuminate/support' => [
			'debug'
		],
		'phpoffice/phpspreadsheet' => [
			'bin'
		],
		'rmccue/requests' => [
			'bin'
		],
		'sabre/dav' => [
			'bin'
		],
		'sabre/event' => [
			'bin'
		],
		'sabre/http' => [
			'bin'
		],
		'sabre/vobject' => [
			'bin'
		],
		'sabre/xml' => [
			'bin'
		],
		'sensiolabs/security-checker' => [
			'security-checker',
			'box.json',
		],
		'sonata-project/google-authenticator' => [
			'makefile'
		],
		'symfony/console' => [
			'resources'
		],
		'yetiforce/yii2' => [
			'yii'
		],
	];

	/**
	 * Post update and post install function.
	 *
	 * @param \Composer\Script\Event $event
	 */
	public static function install(\Composer\Script\Event $event)
	{
		static::clear();
		$event->getComposer();
		if (isset($_SERVER['SENSIOLABS_EXECUTION_NAME'])) {
			return true;
		}
		$rootDir = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR;
		$types = ['js', 'css', 'woff', 'woff2', 'ttf', 'png', 'gif', 'jpg', 'json'];
		foreach (static::$publicPackage as $package => $method) {
			$src = 'vendor' . DIRECTORY_SEPARATOR . $package;
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isFile() && in_array($item->getExtension(), $types) && !file_exists($rootDir . $item->getPathname())) {
					if (!is_dir($rootDir . $item->getPath())) {
						mkdir($rootDir . $item->getPath(), 0755, true);
					}
					if (!is_writable($rootDir . $item->getPath())) {
						continue;
					}
					if ($method === 'move') {
						\rename($item->getRealPath(), $rootDir . $item->getPathname());
					} elseif ($method === 'copy') {
						\copy($item->getRealPath(), $rootDir . $item->getPathname());
					}
				}
			}
		}
	}

	/**
	 * Delete redundant files.
	 */
	public static function clear()
	{
		$rootDir = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
		$objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootDir), \RecursiveIteratorIterator::SELF_FIRST);
		$deleted = [];
		foreach ($objects as $object) {
			if ($object->getFilename() === '.' || $object->getFilename() === '..') {
				continue;
			}
			if ((\in_array(strtolower($object->getFilename()), self::$clearFiles)) && (is_dir($object->getPathname()) || file_exists($object->getPathname()))) {
				$deleted[] = $object->getPathname();
			}
		}
		$deletedCount = 0;
		$deleted = array_unique($deleted);
		arsort($deleted);
		foreach ($deleted as $delete) {
			\vtlib\Functions::recurseDelete($delete, true);
			$deletedCount++;
		}
		foreach (new \DirectoryIterator($rootDir) as $level1) {
			if ($level1->isDir() && !$level1->isDot()) {
				foreach (new \DirectoryIterator($level1->getPathname()) as $level2) {
					if ($level2->isDir() && !$level2->isDot()) {
						foreach (new \DirectoryIterator($level2->getPathname()) as $level3) {
							if (isset(self::$clearFilesModule[$level1->getFileName() . '/' . $level2->getFilename()]) && !$level3->isDot() && \in_array(strtolower($level3->getFilename()), self::$clearFilesModule[$level1->getFileName() . '/' . $level2->getFilename()])) {
								\vtlib\Functions::recurseDelete($level3->getPathname(), true);
								$deletedCount++;
							}
						}
					}
				}
			}
		}
		echo "Cleaned files: $deletedCount" . PHP_EOL;
	}
}
