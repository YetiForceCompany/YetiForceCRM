<?php
/**
 * SystemWarnings test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * SystemWarnings test class.
 */
class SystemWarnings extends \Tests\Base
{
	/**
	 * Record search test.
	 */
	public function testSystemWarnings()
	{
		$folders = \App\SystemWarnings::getFolders();
		$this->assertNotEmpty($folders);
		$warnings = \App\SystemWarnings::getWarnings('all');
		$this->assertNotEmpty($warnings);
		$count = \App\SystemWarnings::getWarningsCount();
		$this->assertEquals($count, \count($warnings));
	}
}
