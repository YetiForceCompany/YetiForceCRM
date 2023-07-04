<?php
/**
 * Db Fixer test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function testFixerDb(): void
	{
		$getMissingModulesInfo = \App\Db\Fixer::baseModuleTools(true);
		$getMissingActionsInfo = \App\Db\Fixer::baseModuleActions(true);
		$getMissingFieldInfo = \App\Db\Fixer::profileField(true);

		$this->assertSame(0, $getMissingModulesInfo['count'], 'Missing modules: '. print_r($getMissingModulesInfo['names'] ?: 'No data', true));
		$this->assertSame(0, $getMissingActionsInfo['count'],'Missing actions: '. print_r($getMissingActionsInfo['names'] ?: 'No data', true));
		$this->assertSame(0, $getMissingFieldInfo['count'],'Missing fields: '. print_r($getMissingFieldInfo['names'] ?: 'No data', true));
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
