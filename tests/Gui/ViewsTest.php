<?php

/**
 * List view test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	public function testListView(): void
	{
		$this->url('index.php?module=Accounts&view=List');
		static::assertSame('Accounts', $this->driver->findElement(WebDriverBy::id('module'))->getAttribute('value'));

		$this->driver->findElement(WebDriverBy::name('accountname'))->sendKeys('YetiForce');
		$this->driver->executeScript('Vtiger_List_Js.triggerListSearch()');

		static::assertSame('[[["accountname","a","YetiForce"]]]', \vtlib\Functions::getQueryParams($this->driver->getCurrentURL())['search_params'] ?? '');
		$this->findError();

		$this->driver->executeScript("$('.js-change-order[data-columnname=\"accountname\"]').click()");
		static::assertSame('ASC', \vtlib\Functions::getQueryParams($this->driver->getCurrentURL())['orderby']['accountname'] ?? '');
		$this->findError();

		$this->driver->executeScript("$('.Accounts_listViewHeader_action_BTN_PERMISSION_INSPECTOR').click()");
		$this->findError();

		$this->driver->executeScript("$('.Accounts_listViewHeader_action_LBL_SHOW_MAP').click()");
		$this->findError();

		$this->driver->executeScript("$('.Accounts_listViewHeader_action_LBL_SEND_NOTIFICATION').click()");
		$this->findError();

		$this->driver->executeScript("$('#menubar_quickCreate_Accounts').click()");
		$this->findError();
	}

	/*
	 * Testing the list views.
	 *
	 * @return void
	 */
	// public function testListViews(): void
	// {
	// 	$this->url('index.php?module=Accounts&view=ListPreview');
	// 	static::assertCount(1, \count($this->driver->findElement(WebDriverBy::className('detailViewContainer'))));
	// }

	/*
	 * Testing the record edit view.
	 *
	 * @return void
	 */
	// public function testEditView(): void
	// {
	// 	$accountModel = \Tests\Base\C_RecordActions::createAccountRecord();
	// 	$this->url('index.php?module=Accounts&view=Detail&record=' . $accountModel->getId());
	// 	static::assertSame('Accounts', $this->driver->findElement(WebDriverBy::id('module'))->getAttribute('value'));
	// 	static::assertSame('Detail', $this->driver->findElement(WebDriverBy::id('view'))->getAttribute('value'));
	// 	static::assertSame($accountModel->getId(), $this->driver->findElement(WebDriverBy::tagName('recordId'))->getAttribute('value'));
	// 	$this->findError();

	// 	foreach ($this->driver->findElements(WebDriverBy::cssSelector('.js-tabdrop li')) as $element) {
	// 		$element->click();
	// 		$this->findError();
	// 	}
	// }

	/*
	 * Testing the record detail view.
	 *
	 * @return void
	 */
	// public function testDetailView(): void
	// {
	// 	$accountModel = \Tests\Base\C_RecordActions::createAccountRecord();
	// 	$this->url('index.php?module=Accounts&view=Edit&record=' . $accountModel->getId());
	// 	$this->findError();
	// 	static::assertSame('Accounts', $this->driver->findElement(WebDriverBy::id('module'))->getAttribute('value'));
	// 	static::assertSame('Edit', $this->driver->findElement(WebDriverBy::tagName('fromView'))->getAttribute('value'));
	// 	static::assertSame($accountModel->getId(), $this->driver->findElement(WebDriverBy::tagName('recordId'))->getAttribute('value'));

	// 	$this->driver->findElement(WebDriverBy::cssSelector('[data-type="App\RecordCollectors\Vies"]'))->click();
	// 	$this->findError();

	// 	$this->driver->findElement(WebDriverBy::className('js-form-submit-btn'))->click();
	// 	$this->findError();
	// }
}
