<?php

namespace App\Installer;

/**
 * Composer installer.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		'maximebf/debugbar' => 'move',
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
		'.dependabot',
		'.git',
		'.npm',
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
		'doc',
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
		'tutorial',
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
		'whitesource.config.json',
		'changelog.htm',
		'FAQ.htm',
	];
	/**
	 * Clear vendor files.
	 *
	 * @var array
	 */
	public static $clearVendorFiles = [
		'ezyang/htmlpurifier' => [
			'plugins',
		],
		'illuminate/support' => [
			'debug',
		],
		'phpoffice/phpspreadsheet' => [
			'bin',
		],
		'sabre/dav' => [
			'bin',
		],
		'sabre/event' => [
			'bin',
		],
		'sabre/http' => [
			'bin',
		],
		'sabre/vobject' => [
			'bin',
		],
		'sabre/xml' => [
			'bin',
		],
		'sonata-project/google-authenticator' => [
			'makefile',
		],
		'symfony/console' => [
			'resources',
		],
		'yetiforce/yii2' => [
			'yii',
		],
		'twig/twig' => [
			'doc',
			'drupal_test.sh',
		],
	];
	/**
	 * Copy directories.
	 *
	 * @var array
	 */
	public static $copyDirectories = [
	];

	/**
	 * Post update and post install function.
	 *
	 * @param \Composer\Script\Event $event
	 */
	public static function install(\Composer\Script\Event $event): void
	{
		$rootDir = realpath(__DIR__ . '/../../');
		if (!\defined('ROOT_DIRECTORY')) {
			\define('ROOT_DIRECTORY', $rootDir);
		}
		echo str_repeat('=', 50) . PHP_EOL;
		static::clear();
		$event->getComposer();
		if (isset($_SERVER['SENSIOLABS_EXECUTION_NAME'])) {
			return;
		}
		$publicDir = $rootDir . \DIRECTORY_SEPARATOR . 'public_html' . \DIRECTORY_SEPARATOR;
		$types = ['js', 'css', 'woff', 'woff2', 'ttf', 'png', 'gif', 'jpg', 'json', 'svg'];
		$list = '';
		foreach (static::$publicPackage as $package => $method) {
			$src = 'vendor' . \DIRECTORY_SEPARATOR . $package;
			$i = 0;
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isFile() && \in_array($item->getExtension(), $types) && !file_exists($publicDir . $item->getPathname())) {
					if (!is_dir($publicDir . $item->getPath())) {
						mkdir($publicDir . $item->getPath(), 0755, true);
					}
					if (!is_writable($publicDir . $item->getPath())) {
						continue;
					}
					if ('move' === $method) {
						\rename($item->getRealPath(), $publicDir . $item->getPathname());
					} elseif ('copy' === $method) {
						\copy($item->getRealPath(), $publicDir . $item->getPathname());
					}
					++$i;
				}
			}
			$list .= PHP_EOL . "{$package}[{$method}]: $i";
		}
		echo str_repeat('-', 50) . PHP_EOL;
		echo "Copy to public_html: $list" . PHP_EOL;
		echo str_repeat('-', 50) . PHP_EOL;
		self::customCopy();
		echo str_repeat('-', 50) . PHP_EOL;
		self::parseCreditsVue();
		echo str_repeat('=', 50) . PHP_EOL;
	}

	/**
	 * Parse credits vue.
	 */
	public static function parseCreditsVue(): void
	{
		$rootDir = realpath(__DIR__ . '/../../') . \DIRECTORY_SEPARATOR;
		$dirLibraries = $rootDir . 'public_html' . \DIRECTORY_SEPARATOR . 'src' . \DIRECTORY_SEPARATOR . 'node_modules' . \DIRECTORY_SEPARATOR;
		$dataEncode = Credits::getYarnLibraries($dirLibraries . '.yarn-integrity', $dirLibraries);
		if ($dataEncode) {
			\App\Json::save($rootDir . 'app_data' . \DIRECTORY_SEPARATOR . 'libraries.json', $dataEncode);
			echo 'Generated file app_data/libraries.json | ' . \count($dataEncode) . PHP_EOL;
		} else {
			echo str_repeat('+', 50) . PHP_EOL;
			echo 'The problem occured when generating app_data/libraries.json file!!!' . PHP_EOL;
			echo 'It is required to run yarn first and then the composer.' . PHP_EOL;
			echo 'Example: https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/tests/setup/dependency.sh' . PHP_EOL;
			echo str_repeat('+', 50) . PHP_EOL;
		}
	}

	/**
	 * Custom copy.
	 */
	public static function customCopy(): void
	{
		$list = '';
		foreach (static::$copyDirectories as $src => $dest) {
			if (!file_exists(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $dest)) {
				mkdir(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $dest, 0755, true);
			}
			$i = \vtlib\Functions::recurseCopy($src, $dest);
			$list .= PHP_EOL . "{$src}  >>>  {$dest} | Files: $i";
		}
		echo "Copy custom directories: $list" . PHP_EOL;
	}

	/**
	 * Delete redundant files.
	 */
	public static function clear(): void
	{
		if ('TEST' === getenv('INSTALL_MODE')) {
			return;
		}
		$rootDir = realpath(__DIR__ . '/../../') . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR;
		$objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootDir), \RecursiveIteratorIterator::SELF_FIRST);
		$deleted = [];
		foreach ($objects as $object) {
			if ('.' === $object->getFilename() || '..' === $object->getFilename()) {
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
			++$deletedCount;
		}
		foreach (new \DirectoryIterator($rootDir) as $level1) {
			if ($level1->isDir() && !$level1->isDot()) {
				foreach (new \DirectoryIterator($level1->getPathname()) as $level2) {
					if ($level2->isDir() && !$level2->isDot()) {
						foreach (new \DirectoryIterator($level2->getPathname()) as $level3) {
							if (isset(self::$clearVendorFiles[$level1->getFileName() . '/' . $level2->getFilename()]) && !$level3->isDot() && \in_array(strtolower($level3->getFilename()), self::$clearVendorFiles[$level1->getFileName() . '/' . $level2->getFilename()])) {
								\vtlib\Functions::recurseDelete($level3->getPathname(), true);
								++$deletedCount;
							}
						}
					}
				}
			}
		}
		echo "Cleaned files: $deletedCount" . PHP_EOL;
	}
}
