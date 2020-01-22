<?php

/**
 * Backup Manager test class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
use Facebook\WebDriver\WebDriverBy;

class Gui_BackupManager extends \Tests\GuiBase
{
	protected static $isLogin = true;
	/**
	 * Test directory.
	 *
	 * @var string
	 */
	private static $testDir;
	/**
	 * Test filename.
	 *
	 * @var int
	 */
	private static $fileName;
	/**
	 * Test catalog name.
	 *
	 * @var int
	 */
	private static $catalogName;
	/**
	 * Backup dir.
	 *
	 * @var string
	 */
	private static $backupDir;

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass(): void
	{
		static::$backupDir = \App\Utils\Backup::getBackupCatalogPath();
		static::$testDir = App\Fields\File::getTmpPath() . 'backups' . DIRECTORY_SEPARATOR;
		if (!is_dir(static::$testDir) && !mkdir(static::$testDir)) {
			throw new \Exception('I can not create a directory: ' . static::$testDir);
		}
	}

	/**
	 * Testing settings.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function testSetConfig(): void
	{
		$config = new \App\ConfigFile('component', 'Backup');
		$config->set('BACKUP_PATH', static::$testDir);
		$config->create();
		$this->assertSame(\App\Utils\Backup::getBackupCatalogPath(), static::$testDir);
	}

	/**
	 * Test create backup.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testCreateBackup(): void
	{
		static::$fileName = date('Ymd_His') . '.zip';
		static::$catalogName = 'backup_catalog_' . date('Ymd_His');
		$zip = \App\Zip::createFile(static::$testDir . static::$fileName);
		$zip->addFromString('filename.txt', '<minimal content>');
		$zip->close();
		if (!is_dir(static::$testDir . static::$catalogName)) {
			mkdir(static::$testDir . static::$catalogName);
		}
		$this->assertDirectoryExists(static::$testDir);
		$this->assertDirectoryIsReadable(static::$testDir);
		$this->assertDirectoryIsWritable(static::$testDir);
		$this->assertFileExists(static::$testDir . static::$fileName, 'File not exists: ' . static::$testDir . static::$fileName);
	}

	/**
	 * Testing is exist catalog on the list.
	 */
	public function testFileAndCatalogExist(): void
	{
		$this->login();
		$this->url('index.php?module=Backup&parent=Settings&view=Index');
		$this->assertSame(
			static::$catalogName,
			$this->driver->findElement(WebDriverBy::cssSelector('.listViewContentDiv table:first-child td:first-child'))->getText(),
			'Catalog does not exist'
		);
		$this->assertSame(
			static::$fileName,
			$this->driver->findElement(WebDriverBy::cssSelector('.listViewContentDiv div:nth-child(2) td:first-child'))->getText(),
			'File does not exist'
		);
		$this->assertInstanceOf(
			'\Facebook\WebDriver\Remote\RemoteWebDriver',
			$this->driver->close(),
			'Window close should return WebDriver object'
		);
	}

	/**
	 * Test directory.
	 */
	public function testDir(): void
	{
		$this->assertDirectoryExists(static::$testDir);
		$this->assertDirectoryIsReadable(static::$testDir);
		$this->assertDirectoryIsWritable(static::$testDir);
	}

	/**
	 * Configuration restore test.
	 */
	public function testRestoreConfig(): void
	{
		$config = new \App\ConfigFile('component', 'Backup');
		$config->set('BACKUP_PATH', static::$backupDir);
		$config->create();
		$this->assertSame(\App\Utils\Backup::getBackupCatalogPath(), static::$backupDir);
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass(): void
	{
		if (\App\Fields\File::isAllowedDirectory(static::$testDir)) {
			\vtlib\Functions::recurseDelete(static::$testDir, true);
		} else {
			echo 'Problem with directory' . static::$testDir . PHP_EOL;
		}
	}
}
