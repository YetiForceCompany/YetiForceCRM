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
		$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid'=>$moduleId, 'fieldname'=>'email'])->scalar();
		$this->assertTrue(\App\Field::getFieldPermission('Leads', 'email', true), 'Expected read perms(strings)');
		$this->assertTrue(\App\Field::getFieldPermission('Leads', 'email', false), 'Expected write perms(strings)');
		$this->assertTrue(\App\Field::getFieldPermission('Leads', 'email', false), 'Expected write perms(strings,cached)');
		$this->assertTrue(\App\Field::getFieldPermission($moduleId, $fieldId, true), 'Expected read perms(ids)');
		$this->assertTrue(\App\Field::getFieldPermission($moduleId, $fieldId, false), 'Expected write perms(ids)');
		$this->assertFalse(\App\Field::getFieldPermission($moduleId, 'NxField', true), 'Expected no read perms(field not exists)');
		$this->assertFalse(\App\Field::getFieldPermission($moduleId, 'NxField', false), 'Expected no write perms(field not exists)');
	}

	/**
	 * Testing getColumnPermission function.
	 */
	public function testGetColumnPermission()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		$fieldColumn = (new \App\Db\Query())->select(['columnname'])->from('vtiger_field')->where(['tabid' => $moduleId, 'fieldname' => 'vat_id'])->scalar();
		$this->assertTrue(\App\Field::getColumnPermission('Leads', $fieldColumn, true), 'Expected read perms(string)');
		$this->assertTrue(\App\Field::getColumnPermission($moduleId, $fieldColumn, true), 'Expected read perms(ids)');
		$this->assertTrue(\App\Field::getColumnPermission($moduleId, $fieldColumn, false), 'Expected write perms(ids)');
		$this->assertFalse(\App\Field::getColumnPermission($moduleId, 'NxColumn', true), 'Expected no read perms(field not exists)');
		$this->assertFalse(\App\Field::getColumnPermission($moduleId, 'NxColumn', false), 'Expected write perms(field not exists)');
	}

	/**
	 * Relations modules ids provider.
	 *
	 * @return array
	 */
	public function relationModulesProvider()
	{
		return (new \App\Db\Query())->select(['tabid', 'related_tabid'])->from('vtiger_relatedlists')->all();
	}

	/**
	 * @dataProvider relationModulesProvider
	 */
	public function testGetRelatedFieldForModulePair($relatedId, $moduleId)
	{
		$result = \App\Field::getRelatedFieldForModule(\App\Module::getModuleName($moduleId), \App\Module::getModuleName($relatedId));
		$this->assertInternalType('array', $result, 'Relation list should be array type');
	}

	public function testGetRelatedFieldForModuleAll()
	{
		$this->assertNotEmpty(\App\Field::getRelatedFieldForModule(), 'All relations list should be not empty');
	}

	/**
	 * Relations ids provider.
	 *
	 * @return array
	 */
	public function relationsProvider()
	{
		return (new \App\Db\Query())->select(['relation_id'])->from('vtiger_relatedlists')->all();
	}

	/**
	 * @dataProvider relationsProvider
	 *
	 * @param int $relationId
	 */
	public function testGetFieldsFromRelation($relationId)
	{
		$result = \App\Field::getFieldsFromRelation($relationId);
		$this->assertInternalType('array', $result, 'Expected result type array for relation: ' . $relationId);
	}

	/**
	 * Testing getFieldInfo.
	 */
	public function testGetFieldInfo()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid' => $moduleId, 'fieldname' => 'email'])->scalar();
		$fieldInfo = \App\Field::getFieldInfo($fieldId);
		$this->assertInternalType('array', $fieldInfo, 'Expected field info array');
		$this->assertNotEmpty($fieldInfo, 'Field info should be not empty');
		$this->assertSame($fieldId, $fieldInfo['fieldid'], 'Expected fieldid in fieldinfo same as reference');
		$this->assertSame($moduleId, $fieldInfo['tabid'], 'Expected moduleid in fieldinfo same as reference');
		$fieldInfoByName = \App\Field::getFieldInfo('email', $moduleId);
		$this->assertInternalType('array', $fieldInfoByName, 'Expected field info array');
		$this->assertNotEmpty($fieldInfoByName, 'Field info should be not empty');
		$this->assertSame($fieldId, $fieldInfoByName['fieldid'], 'Expected fieldid in fieldinfo same as reference');
		$this->assertSame($moduleId, $fieldInfoByName['tabid'], 'Expected moduleid in fieldinfo same as reference');
	}
}
