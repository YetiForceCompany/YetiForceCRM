<?php
/**
 * Config test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * Config test class.
 */
class Config extends \Tests\Base
{
	/**
	 * Testing JS env var setter.
	 */
	public function testSetJsEnv()
	{
		$this->assertNull(\App\Config::setJsEnv('UnitTestsTestVar', 'Test'));
	}

	/**
	 * Testing JS env vars getter.
	 */
	public function testGetJsEnv()
	{
		$this->assertNotEmpty(\App\Config::getJsEnv(), 'Json string expected');
	}
}
