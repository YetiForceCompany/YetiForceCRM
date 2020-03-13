<?php
/**
 * YetiForce test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

/**
 * Class YetiForce tests.
 */
class YetiForce extends \Tests\Base
{
	/**
	 * Testing watchdog getAll method.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testWatchdogGetAll()
	{
		$this->assertCount(\count(\App\YetiForce\Watchdog::$variables), \App\YetiForce\Watchdog::getAll());
	}
}
