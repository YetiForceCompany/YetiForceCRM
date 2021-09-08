<?php

/**
 * Init integration test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Init;

/**
 * Init integration test class.
 *
 * @internal
 * @coversNothing
 */
final class IntegrationTest extends \Tests\Base
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
		], 1);
		static::assertTrue(0 !== $result, $result);
		static::assertTrue(1 !== $result, $result);
		static::assertSame(mb_strlen($result), 10, $result);
		static::assertTrue(1 === ((new \App\Db\Query())->from('dav_users')->where(['key' => $result])->count()));
		static::assertTrue(1 === ((new \App\Db\Query())->from('dav_principals')->count()));
		static::assertTrue(1 === ((new \App\Db\Query())->from('dav_addressbooks')->count()));
		static::assertTrue(1 === ((new \App\Db\Query())->from('dav_calendarinstances')->count()));
		static::assertTrue(1 === ((new \App\Db\Query())->from('dav_calendars')->count()));
	}
}
