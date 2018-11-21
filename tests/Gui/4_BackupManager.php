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
		self::$testDir = App\Fields\File::getTmpPath() . 'backups';
		$config = new \App\Configurator('backup');
		$config->set('BACKUP_PATH', self::$testDir);
		$config->save();
		self::$testDir .= DIRECTORY_SEPARATOR;
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
	public function testFileAndCatalogExist()
	{
		$this->url('index.php?module=Backup&parent=Settings&view=Index');
		$this->assertSame(self::$catalogName, $this->findElBy('cssSelector', '.listViewContentDiv table:first-child td:first-child')->getText(), 'Catalog does not exist');
		$this->assertSame(self::$fileName, $this->driver->findElement(WebDriverBy::cssSelector('.listViewContentDiv table:nth-child(2) td:first-child'))->getText(), 'File does not exist');
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
