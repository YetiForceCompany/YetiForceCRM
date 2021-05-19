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

/**
 * @internal
 * @coversNothing
 */
final class Gui_ListViewTest extends \Tests\GuiBase
{
	/**
	 * Testing the record list all modules.
	 *
	 * @return void
	 */
	public function testAllModules(): void
	{
		foreach (vtlib\Functions::getAllModules() as $module) {
			$this->url("index.php?module={$module['name']}&view=List");
			$this->findError();
			$this->logs = $module['name'];
			static::assertSame($module['name'], $this->driver->findElement(WebDriverBy::id('module'))->getAttribute('value'));
			static::assertSame('List', $this->driver->findElement(WebDriverBy::id('view'))->getAttribute('value'));
			// try {
				// 	$this->driver->findElement(WebDriverBy::id($module['name'] . '_listView_row_1'))->click();
				// 	$this->findError();
				// } catch (\Throwable $th) {
				// 	echo " {$module['name']} Skipped, no records\n";
				// 	continue;
				// }
				// try {
				// 	$this->driver->findElement(WebDriverBy::className($module['name'] . '_detailViewBasic_action_BTN_RECORD_EDIT'))->click();
				// 	$this->findError();
				// } catch (\Throwable $th) {
				// 	echo "{$module['name']} Skipped, no edit btn\n";
				// }
		}
	}

	/**
	 * Testing the record list.
	 *
	 * @return void
	 */
	public function testActions(): void
	{
		$this->url('index.php?module=Accounts&view=List');
		$this->driver->findElement(WebDriverBy::name('accountname'))->sendKeys('demo');
		$this->driver->executeScript('Vtiger_List_Js.triggerListSearch()');
		$this->findError();
		$this->driver->findElement(WebDriverBy::cssSelector('.js-change-order[data-columnname="accountname"]'))->click();
		$this->findError();
		$this->driver->findElement(WebDriverBy::className('Accounts_listViewHeader_action_BTN_PERMISSION_INSPECTOR '))->click();
		$this->findError();
		$this->driver->findElement(WebDriverBy::className('Accounts_listViewHeader_action_LBL_SHOW_MAP'))->click();
		$this->findError();
		$this->driver->findElement(WebDriverBy::className('Accounts_listViewHeader_action_LBL_SEND_NOTIFICATION'))->click();
		$this->findError();
		$this->driver->findElement(WebDriverBy::className('Accounts_listViewHeader_action_LBL_SEND_NOTIFICATION'))->click();
		$this->findError();
		$this->driver->findElement(WebDriverBy::id('menubar_quickCreate_Accounts'))->click();
		$this->findError();
		$this->url('index.php?module=Accounts&view=ListPreview');
		$this->findError();
		$this->url('index.php?module=Accounts&view=DashBoard');
		$this->findError();
	}
}
