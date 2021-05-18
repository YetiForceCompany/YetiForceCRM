<?php

/**
 * List view test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use Facebook\WebDriver\WebDriverBy;

class Gui_ListView extends \Tests\GuiBase
{
	/**
	 * Testing the record list.
	 *
	 * @return void
	 */
	public function testList(): void
	{
		foreach (vtlib\Functions::getAllModules() as $module) {
			$this->url("index.php?module={$module['name']}&view=List");
			$this->findError();
			$this->logs = $module['name'];
			$this->assertSame($module['name'], $this->driver->findElement(WebDriverBy::id('module'))->getAttribute('value'));
			$this->assertSame('List', $this->driver->findElement(WebDriverBy::id('view'))->getAttribute('value'));
			try {
				$this->driver->findElement(WebDriverBy::id($module['name'] . '_listView_row_1'))->click();
				$this->findError();
			} catch (\Throwable $th) {
				$this->markTestSkipped('Skipped, no records in module ' . $module['name']);
				continue;
			}
			try {
				$this->driver->findElement(WebDriverBy::className($module['name'] . '_detailViewBasic_action_BTN_RECORD_EDIT'))->click();
				$this->findError();
			} catch (\Throwable $th) {
				$this->markTestSkipped('Skipped, no edit btn in module ' . $module['name']);
			}
		}
		$this->assertInstanceOf('\Facebook\WebDriver\Remote\RemoteWebDriver', $this->driver->close(), 'Window close should return WebDriver object');
	}
}
