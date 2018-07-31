<?php
/**
 * Field test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Field extends \Tests\Base
{
	/**
	 * Testing getFieldPermission function.
	 */
	public function testGetFieldPermission()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid'=>\App\Module::getModuleId('Leads'), 'fieldname'=>'email'])->scalar();
		$this->assertTrue(\App\Field::getFieldPermission('Leads', 'email', true), 'Expected read perms(strings)');
		$this->assertTrue(\App\Field::getFieldPermission('Leads', 'email', false), 'Expected write perms(strings)');
		$this->assertTrue(\App\Field::getFieldPermission('Leads', 'email', false), 'Expected write perms(strings,cached)');
		$this->assertTrue(\App\Field::getFieldPermission($moduleId, $fieldId, true), 'Expected read perms(ids)');
		$this->assertTrue(\App\Field::getFieldPermission($moduleId, $fieldId, false), 'Expected write perms(ids)');
		$this->assertFalse(\App\Field::getFieldPermission($moduleId, 'NxField', true), 'Expected no read perms(field not exists)');
		$this->assertFalse(\App\Field::getFieldPermission($moduleId, 'NxField', false), 'Expected no write perms(field not exists)');
	}

	public function testGetColumnPermission()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		$fieldColumn = (new \App\Db\Query())->select(['columnname'])->from('vtiger_field')->where(['tabid' => \App\Module::getModuleId('Leads'), 'fieldname' => 'email'])->scalar();
		$this->assertTrue(\App\Field::getColumnPermission('Leads', $fieldColumn, true), 'Expected read perms(string)');
		$this->assertTrue(\App\Field::getColumnPermission($moduleId, $fieldColumn, true), 'Expected read perms(ids)');
		$this->assertTrue(\App\Field::getColumnPermission($moduleId, $fieldColumn, false), 'Expected write perms(ids)');
		$this->assertFalse(\App\Field::getColumnPermission($moduleId, 'NxColumn', true), 'Expected no read perms(field not exists)');
		$this->assertFalse(\App\Field::getColumnPermission($moduleId, 'NxColumn', false), 'Expected write perms(field not exists)');
	}
}
