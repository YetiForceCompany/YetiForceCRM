<?php

/**
 * List view test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Gui_ListView extends \Tests\GuiBase
{

	/**
	 * Testing the record list
	 */
	public function testList()
	{
		foreach (vtlib\Functions::getAllModules() as $module) {
			if ($module['name'] === 'Events') {
				continue;
			}
			$this->url("/index.php?module={$module['name']}&view=List");
			$this->logs = $module['name'];
			$this->assertEquals($module['name'], $this->byId('module')->value());
			$this->assertEquals('List', $this->byId('view')->value());
		}
	}

	public function testView()
	{
		$this->url("/index.php?module=Accounts&view=List");
		$this->byCssSelector('.listViewEntries')->click();

		$this->assertEquals('Accounts', $this->byId('module')->value());
		$this->assertEquals('Detail', $this->byId('view')->value());

		$this->byId('Accounts_detailView_action_BTN_RECORD_EDIT')->click();
		$this->assertEquals('Accounts', $this->byId('module')->value());
		$this->assertEquals('Edit', $this->byId('view')->value());
	}
}
