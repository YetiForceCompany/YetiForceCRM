<?php
/**
 * SystemWarnings test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	}
}
