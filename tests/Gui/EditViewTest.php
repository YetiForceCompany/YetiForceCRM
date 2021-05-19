<?php

/**
 * Edit view test class.
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
final class Gui_EditViewTest extends \Tests\GuiBase
{
	/**
	 * Testing the record list.
	 *
	 * @return void
	 */
	public function testActions(): void
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
