<?php

/**
 * Backup Manager test class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
use Facebook\WebDriver\WebDriverBy;

class Gui_BackupManager extends \Tests\GuiBase
{
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

	public function setUp()
	{
		static::$isLogin = false;
		parent::setUp();
	}

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
		self::$testDir = 'tests/data/backups/';
		$config = new \App\Configurator('backup');
		$config->set('BACKUP_PATH', self::$testDir);
		$config->save();
		if (is_dir(self::$testDir) === false) {
			if (mkdir(self::$testDir)) {
				self::$fileName = date('Ymd_His') . '.zip';
				self::$catalogName = 'backup_catalog_' . date('Ymd_His');
				$zip = \App\Zip::createFile(self::$testDir . self::$fileName);
				$zip->addFromString('filename.txt', '<minimal content>');
				$zip->close();
				$catalogDir = self::$testDir . self::$catalogName;
				if (is_dir($catalogDir) === false) {
					mkdir($catalogDir);
				}
			}
		}
	}

	/**
	 * Testing is exist catalog on the list.
	 */
	public function testCatalogExist()
	{
		$this->url('index.php?module=Backup&parent=Settings&view=Index');
		$this->assertSame(self::$catalogName, $this->driver->findElement(WebDriverBy::cssSelector('.catalog-list-records > td.catalog-record-name'))->getText());
		$this->assertInstanceOf('\Facebook\WebDriver\Remote\RemoteWebDriver', $this->driver->close(), 'Window close should return WebDriver object');
	}

	/**
	 * Testing is file exist on the list.
	 */
	public function testFileExist()
	{
		$this->url('index.php?module=Backup&parent=Settings&view=Index');
		$this->assertSame(self::$fileName, $this->driver->findElement(WebDriverBy::cssSelector('.file-list-records > td.file-record-name'))->getText());
		$this->assertInstanceOf('\Facebook\WebDriver\Remote\RemoteWebDriver', $this->driver->close(), 'Window close should return WebDriver object');
	}

	/**
	 * Testing is not exist file on the list.
	 */
	public function testFileNotExist()
	{
		$this->url('index.php?module=Backup&parent=Settings&view=Index');
		$this->assertNotSame('RandomFileName', $this->driver->findElement(WebDriverBy::cssSelector('.file-list-records > td.file-record-name'))->getText());
		$this->assertInstanceOf('\Facebook\WebDriver\Remote\RemoteWebDriver', $this->driver->close(), 'Window close should return WebDriver object');
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		$catalogIterator = new RecursiveDirectoryIterator(self::$testDir, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($catalogIterator,
			RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($files as $file) {
			if ($file->isDir()) {
				(int) rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}
		rmdir(self::$testDir);
		$config = new \App\Configurator('backup');
		$config->set('BACKUP_PATH', '');
		$config->save();
	}
}
