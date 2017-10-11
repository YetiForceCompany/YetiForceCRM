<?php

/**
 * List view test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Gui_ListView extends Gui_Base
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
}
