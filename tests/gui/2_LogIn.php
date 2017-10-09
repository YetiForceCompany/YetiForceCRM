<?php

/**
 * LogiIn test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class GuiLogIn extends GuiBase
{

	/**
	 * Testing login page display
	 */
	public function testLoadPage()
	{
		$this->url('index.php');
		$this->byId('username')->value('admin');
		$this->byId('password')->value('admin');
		$this->byTag('form')->submit();
		$this->assertEquals('Home', $this->byId('module')->value());
		$this->assertEquals('DashBoard', $this->byId('view')->value());
	}
}
