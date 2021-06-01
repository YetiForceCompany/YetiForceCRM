<?php

/**
 * Integration test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Init;

/**
 * Integration test class.
 */
class C_Integration extends \Tests\Base
{
	/**
	 * Testing add sample user to dav.
	 */
	public function testAddSampleUserToDav()
	{
		$moduleModel = \Settings_Dav_Module_Model::getInstance('Settings:Dav');
		$result = $moduleModel->addKey([
			'CalDav',
			'CardDav',
			'WebDav'
		], 1);
		$this->assertTrue(0 !== $result, $result);
		$this->assertTrue(1 !== $result, $result);
		$this->assertSame(mb_strlen($result), 10, $result);
		$this->assertTrue(1 === ((new \App\Db\Query())->from('dav_users')->where(['key' => $result])->count()));
		$this->assertTrue(1 === ((new \App\Db\Query())->from('dav_principals')->count()));
		$this->assertTrue(1 === ((new \App\Db\Query())->from('dav_addressbooks')->count()));
		$this->assertTrue(1 === ((new \App\Db\Query())->from('dav_calendarinstances')->count()));
		$this->assertTrue(1 === ((new \App\Db\Query())->from('dav_calendars')->count()));
	}
}
