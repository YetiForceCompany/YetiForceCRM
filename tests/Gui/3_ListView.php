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
	 */
	public function testList()
	{
		foreach (vtlib\Functions::getAllModules() as $module) {
			$this->url("index.php?module={$module['name']}&view=List");
			$this->logs = $module['name'];
			$this->assertSame($module['name'], $this->driver->findElement(WebDriverBy::id('module'))->getAttribute('value'));
			$this->assertSame('List', $this->driver->findElement(WebDriverBy::id('view'))->getAttribute('value'));
		}
		$this->assertInstanceOf('\Facebook\WebDriver\Remote\RemoteWebDriver', $this->driver->close(), 'Window close should return WebDriver object');
	}
}
