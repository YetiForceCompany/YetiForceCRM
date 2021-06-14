<?php
/**
 * Db Fixer test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$this->assertSame(0, $fields['RequiresVerification']);
		$this->assertSame(0, $fields['Updated']);

		$fields = \App\Db\Fixer::maximumFieldsLength(['fieldname' => 'email']);
		$this->assertSame(0, $fields['TypeNotFound']);
		$this->assertSame(0, $fields['RequiresVerification']);
		$this->assertSame(0, $fields['Updated']);
	}
}
