<?php

/**
 * List view test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Gui_Groups extends \Tests\GuiBase
{

	/**
	 * Testing the record list
	 */
	public function testList()
	{
		$this->url('/index.php?module=Groups&parent=Settings&view=List');
		$this->assertEquals('Groups', $this->byId('module')->value());
		$this->assertEquals('List', $this->byId('view')->value());
		$this->assertEquals('Settings', $this->byId('parent')->value());
	}

	/**
	 * Testing the record list order
	 */
	public function testOrder()
	{
		$this->byCssSelector('.listViewHeaderValues[data-columnname="groupname"]')->click();
		$this->assertStringEndsWith('&orderby=groupname&sortorder=ASC', strstr($this->url(), '&orderby=groupname&sortorder='));
		$this->url('/index.php?module=Groups&parent=Settings&view=List');
		$this->byCssSelector('.listViewHeaderValues[data-columnname="description"]')->click();
		$this->assertStringEndsWith('&orderby=description&sortorder=ASC', strstr($this->url(), '&orderby=description&sortorder='));
	}

	/**
	 * Testing the add group
	 */
	public function testAdd()
	{
		$this->url('/index.php?module=Groups&parent=Settings&view=List');
		$this->byCssSelector('.contentsDiv .addButton')->click();

		$this->assertEquals('Edit', $this->byId('view')->value());

		$this->byName('groupname')->value('Test groupname');
		$this->byName('description')->value('Test description');
		$this->select($this->byId('modulesList'))->selectOptionByValue(\App\Module::getModuleId('Contacts'));
		$this->select($this->byId('memberList'))->selectOptionByValue('Users:1');

		try {
			$this->byCssSelector('form')->submit();
		} catch (Exception $exc) {
			
		}
	}
}
