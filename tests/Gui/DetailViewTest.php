<?php

/**
 * Detail view test class.
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
final class Gui_DetailViewTest extends \Tests\GuiBase
{
	/**
	 * Testing the record list.
	 *
	 * @return void
	 */
	public function testActions(): void
	{
		$accountModel = \Tests\Base\C_RecordActions::createAccountRecord();
		$this->url('index.php?module=Accounts&view=Detail&record=' . $accountModel->getId());
		$this->findError();
		foreach ($this->driver->findElements(WebDriverBy::cssSelector('.js-tabdrop li')) as $element) {
			$element->click();
			$this->findError();
		}
	}
}
