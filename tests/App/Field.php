<?php
/**
 * Field test class.
 *
 * @package   Tests
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
		$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid' => $moduleId, 'fieldname' => 'email'])->scalar();
		$fieldRoId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid' => $moduleId, 'fieldname' => 'smcreatorid'])->scalar();
		$this->assertTrue(\App\Field::getFieldPermission('Leads', 'email', true), 'Expected read perms(strings)');
		$this->assertTrue(\App\Field::getFieldPermission('Leads', 'email', false), 'Expected write perms(strings)');
		$this->assertTrue(\App\Field::getFieldPermission('Leads', 'email', false), 'Expected write perms(strings,cached)');
		$this->assertTrue(\App\Field::getFieldPermission($moduleId, $fieldId, true), 'Expected read perms(ids)');
		$this->assertTrue(\App\Field::getFieldPermission($moduleId, $fieldId, false), 'Expected write perms(ids)');
		$this->assertFalse(\App\Field::getFieldPermission($moduleId, $fieldRoId, false), 'Expected no write perms(ids)');
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
		$fieldRoColumn = (new \App\Db\Query())->select(['columnname'])->from('vtiger_field')->where(['tabid' => $moduleId, 'fieldname' => 'smcreatorid'])->scalar();
		$this->assertTrue(\App\Field::getColumnPermission('Leads', $fieldColumn, true), 'Expected read perms(string)');
		$this->assertTrue(\App\Field::getColumnPermission($moduleId, $fieldColumn, true), 'Expected read perms(ids)');
		$this->assertTrue(\App\Field::getColumnPermission($moduleId, $fieldColumn, false), 'Expected write perms(ids)');
		$this->assertTrue(\App\Field::getColumnPermission($moduleId, $fieldColumn, false), 'Expected write perms(ids, cached)');
		$this->assertFalse(\App\Field::getColumnPermission($moduleId, $fieldRoColumn, false), 'Expected no write perms(ids)');
		$this->assertFalse(\App\Field::getColumnPermission($moduleId, $fieldRoColumn, false), 'Expected no write perms(ids, cached)');
		$this->assertFalse(\App\Field::getColumnPermission($moduleId, 'NxColumn', true), 'Expected no read perms(field not exists)');
		$this->assertFalse(\App\Field::getColumnPermission($moduleId, 'NxColumn', false), 'Expected write perms(field not exists)');
	}

	/**
	 * Relations modules ids provider.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function relationsProvider()
	{
		return (new \App\Db\Query())->select(['tabid', 'related_tabid', 'relation_id'])->from('vtiger_relatedlists')->all();
	}

	/**
	 * Testing getRelatedFieldForModule function with params from vtiger_relatedlist table.
	 *
	 * @dataProvider relationsProvider
	 *
	 * @param mixed $forModuleId
	 * @param mixed $moduleId
	 * @param mixed $relationId
	 */
	public function testGetRelatedFieldForModulePair($forModuleId, $moduleId, $relationId)
	{
		$result0 = \App\Field::getRelatedFieldForModule(\App\Module::getModuleName($moduleId), \App\Module::getModuleName($forModuleId));
		$this->assertIsArray($result0, 'Relation list should be array type');
		$result1 = \App\Field::getRelatedFieldForModule(false, \App\Module::getModuleName($forModuleId));
		$this->assertIsArray($result1, 'Relation list should be array type');
		$result2 = \App\Field::getRelatedFieldForModule(\App\Module::getModuleName($moduleId), false);
		$this->assertIsArray($result2, 'Relation list should be array type');
	}

	/**
	 * Relations modules list provider.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function relationSelectedModulesProvider()
	{
		return [
			['FInvoice', 'FCorectingInvoice'],
			['DataSetRegister', 'IncidentRegister'],
			['DataSetRegister', 'AuditRegister'],
			['LocationRegister', 'IncidentRegister'],
			['LocationRegister', 'AuditRegister']
		];
	}

	/**
	 * Testing getRelatedFieldForModule function with params from vtiger_relatedlist table.
	 *
	 * @dataProvider relationSelectedModulesProvider
	 *
	 * @param mixed $forModuleName
	 * @param mixed $moduleName
	 */
	public function testGetRelatedFieldForSpecificModulePairs($forModuleName, $moduleName)
	{
		$result = \App\Field::getRelatedFieldForModule($moduleName, $forModuleName);
		$this->assertIsArray($result, 'Relation list should be array type');
		$this->assertNotEmpty($result, 'Relation data should be not empty');
		$this->assertSame(\App\Module::getModuleId($moduleName), $result['tabid'], 'Expected tabid differs from reference');
	}

	/**
	 * Testing getRelatedFieldForModule without params.
	 */
	public function testGetRelatedFieldForModuleAll()
	{
		$this->assertNotEmpty(\App\Field::getRelatedFieldForModule(), 'All relations list should be not empty');
	}

	/**
	 * Testing getFieldsFromRelation.
	 *
	 * @dataProvider relationsProvider
	 *
	 * @param int   $relationId
	 * @param mixed $forModuleId
	 * @param mixed $moduleId
	 */
	public function testGetFieldsFromRelation($forModuleId, $moduleId, $relationId)
	{
		$result = \App\Field::getFieldsFromRelation($relationId);
		$this->assertIsArray($result, 'Expected result type array for relation: ' . $relationId);
		$this->assertSame($result, \App\Field::getFieldsFromRelation($relationId), 'Relation fields from cache should be equal as reference for relation: ' . $relationId);
	}

	/**
	 * Testing getFieldsFromRelation for empty relation id.
	 */
	public function testGetFieldsFromEmptyRelation()
	{
		$result = \App\Field::getFieldsFromRelation('');
		$this->assertIsArray($result, 'Expected result type array for empty relation');
		$this->assertEmpty($result, 'Fields array from empty relation should be empty');
	}

	/**
	 * Testing getFieldInfo.
	 */
	public function testGetFieldInfo()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid' => $moduleId, 'fieldname' => 'email'])->scalar();
		$fieldInfo = \App\Field::getFieldInfo($fieldId);
		$this->assertIsArray($fieldInfo, 'Expected field info array');
		$this->assertNotEmpty($fieldInfo, 'Field info should be not empty');
		$this->assertSame($fieldId, $fieldInfo['fieldid'], 'Expected fieldid in fieldinfo same as reference');
		$this->assertSame($moduleId, $fieldInfo['tabid'], 'Expected moduleid in fieldinfo same as reference');
		$this->assertSame($fieldInfo, \App\Field::getFieldInfo($fieldId), 'Field info from cache should be same as reference');
		$fieldInfoByName = \App\Field::getFieldInfo('email', $moduleId);
		$this->assertIsArray($fieldInfoByName, 'Expected field info array');
		$this->assertNotEmpty($fieldInfoByName, 'Field info should be not empty');
		$this->assertSame($fieldId, $fieldInfoByName['fieldid'], 'Expected fieldid in fieldinfo same as reference');
		$this->assertSame($moduleId, $fieldInfoByName['tabid'], 'Expected moduleid in fieldinfo same as reference');
	}
}
