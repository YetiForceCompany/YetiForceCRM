<?php
/**
 * Config test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
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
