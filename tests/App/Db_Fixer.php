<?php
/**
 * Db Fixer test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * Db Fixer test class.
 */
class Db_Fixer extends \Tests\Base
{
	/**
	 * Testing fixer function.
	 */
	public function testFixerDb()
	{
		$this->assertSame(0, \App\Db\Fixer::baseModuleTools());
		$this->assertSame(0, \App\Db\Fixer::baseModuleActions());
		$this->assertSame(0, \App\Db\Fixer::profileField());
		$this->assertSame(0, \App\Db\Fixer::share());

		$fields = \App\Db\Fixer::maximumFieldsLength();
		$this->assertSame(0, $fields['TypeNotFound']);
		$this->assertSame(0, $fields['Updated']);
		// @codeCoverageIgnoreStart
		if (0 != $fields['RequiresVerification']) {
			$this->markTestSkipped('Fields for verification detected:' . $fields['RequiresVerification']);
		}
		/** @codeCoverageIgnoreEnd */
		$fields = \App\Db\Fixer::maximumFieldsLength(['fieldname' => 'email']);
		$this->assertSame(0, $fields['TypeNotFound']);
		$this->assertSame(0, $fields['Updated']);
		// @codeCoverageIgnoreStart
		if (0 != $fields['RequiresVerification']) {
			$this->markTestSkipped('Fields for verification detected:' . $fields['RequiresVerification']);
		}
		// @codeCoverageIgnoreEnd
	}
}
