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
		foreach (\App\Module::getAllEntityModuleInfo() as $moduleInfo) {
			$this->url("/index.php?module={$moduleInfo['modulename']}&view=List");
			$this->assertEquals($moduleInfo['modulename'], $this->byId('module')->value());
			$this->assertEquals('List', $this->byId('view')->value());
		}
	}
}
