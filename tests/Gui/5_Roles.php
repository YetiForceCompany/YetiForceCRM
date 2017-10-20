<?php

/**
 * List view test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Gui_Roles extends \Tests\GuiBase
{

	/**
	 * Testing the record list
	 */
	public function testList()
	{
		$this->url('/index.php?module=Roles&parent=Settings&view=Index');
		$this->assertEquals('Roles', $this->byId('module')->value());
		$this->assertEquals('Index', $this->byId('view')->value());
		$this->assertEquals('Settings', $this->byId('parent')->value());
	}
}
