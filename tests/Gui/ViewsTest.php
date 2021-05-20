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
final class Gui_ViewsTest extends \Tests\GuiBase
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
		$this->driver->executeScript("$('.js-change-order[data-columnname=\"accountname\"]').click()");
		$this->findError();
		$this->driver->executeScript("$('.Accounts_listViewHeader_action_BTN_PERMISSION_INSPECTOR').click()");
		$this->findError();
		$this->driver->executeScript("$('.Accounts_listViewHeader_action_LBL_SHOW_MAP').click()");
		$this->findError();
		$this->driver->executeScript("$('.Accounts_listViewHeader_action_LBL_SEND_NOTIFICATION').click()");
		$this->findError();
		$this->driver->executeScript("$('.Accounts_listViewHeader_action_LBL_SEND_NOTIFICATION').click()");
		$this->findError();
		$this->driver->executeScript("$('#menubar_quickCreate_Accounts').click()");
		$this->findError();
		// $this->url('index.php?module=Accounts&view=ListPreview');
		// $this->findError();
		$this->url('index.php?module=Accounts&view=DashBoard');
		$this->findError();
	}

	/**
	 * Testing the record edit view.
	 *
	 * @return void
	 */
	public function testEditView(): void
	{
		$accountModel = \Tests\Base\C_RecordActions::createAccountRecord();
		$this->url('index.php?module=Accounts&view=Detail&record=' . $accountModel->getId());
		$this->findError();
		foreach ($this->driver->findElements(WebDriverBy::cssSelector('.js-tabdrop li')) as $element) {
			$element->click();
			$this->findError();
		}
	}

	/**
	 * Testing the record detail view.
	 *
	 * @return void
	 */
	public function testDetailView(): void
	{
		$accountModel = \Tests\Base\C_RecordActions::createAccountRecord();
		$this->url('index.php?module=Accounts&view=Edit&record=' . $accountModel->getId());
		$this->findError();
		$this->driver->findElement(WebDriverBy::cssSelector('[data-type="App\RecordCollectors\Vies"]'))->click();
		$this->findError();
		$this->driver->findElement(WebDriverBy::className('js-form-submit-btn'))->click();
		$this->findError();
	}
}
