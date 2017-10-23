<?php

/**
 * Search view test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Gui_Search extends \Tests\GuiBase
{

	/**
	 * Testing the record list
	 */
	public function testIndex()
	{
		$this->url('/index.php?module=Search&parent=Settings&view=Index');
		$this->assertEquals('Search', $this->byId('module')->value());
		$this->assertEquals('Index', $this->byId('view')->value());
		$this->assertEquals('Settings', $this->byId('parent')->value());
	}
}
