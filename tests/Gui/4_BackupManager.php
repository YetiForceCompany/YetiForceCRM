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

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
		self::$testDir = \ROOT_DIRECTORY . 'tests' . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . 'backups';
		$config = new \App\Configurator('backup');
		$config->set('BACKUP_PATH', self::$testDir);
		$config->save();
		self::$testDir .= DIRECTORY_SEPARATOR;
		if (is_dir(self::$testDir) === false) {
			var_dump('Create catalog');
			if (\mkdir(self::$testDir, 0777, true)) {
				var_dump('>> Created catalog');
				self::$fileName = date('Ymd_His') . '.zip';
				self::$catalogName = 'backup_catalog_' . date('Ymd_His');
				$zip = \App\Zip::createFile(self::$testDir . self::$fileName);
				$zip->addFromString('filename.txt', '<minimal content>');
				$zip->close();
				$catalogDir = self::$testDir . self::$catalogName;
				if (is_dir($catalogDir) === false) {
					\mkdir($catalogDir, true);
				}
			}
		}
	}

	/**
	 * Testing is exist catalog on the list.
	 */
	public function testFileAndCatalogExist()
	{
		static::$isLogin = false;
		//$this->login();
		$this->url('index.php?module=Backup&parent=Settings&view=Index');
		var_dump('#@@@@###@@@@###############');
		var_dump(self::$testDir);
		var_dump('#@@@@###@@@@###############');
		var_dump($this->driver->getPageSource());
		$this->logs[] = $this->driver->getPageSource();
		$this->assertSame(self::$catalogName, $this->driver->findElement(WebDriverBy::cssSelector('.listViewContentDiv table:first-child td:first-child'))->getText(), 'Catalog does not exist');
		$this->assertSame(self::$fileName, $this->driver->findElement(WebDriverBy::cssSelector('.listViewContentDiv table:nth-child(2) td:first-child'))->getText(), 'File does not exist');
		$this->assertInstanceOf('\Facebook\WebDriver\Remote\RemoteWebDriver', $this->driver->close(), 'Window close should return WebDriver object');
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		\vtlib\Functions::recurseDelete(self::$testDir, true);
		$config = new \App\Configurator('backup');
		$config->set('BACKUP_PATH', '');
		$config->save();
	}
}
