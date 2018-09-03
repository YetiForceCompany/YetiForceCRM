<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ******************************************************************************* */

require_once 'include/database/PearDatabase.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';
require_once 'modules/PickList/PickListUtils.php';

class Vtiger_DependencyPicklist
{
	/**
	 * Returns information about dependent picklists.
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public static function getDependentPicklistFields($module = '')
	{
		$query = (new \App\Db\Query())->select(['sourcefield', 'targetfield', 'tabid'])
			->from('vtiger_picklist_dependency');
		if (!empty($module)) {
			$query->where(['tabid' => \App\Module::getModuleId($module)]);
		}
		$dataReader = $query->distinct()->createCommand()->query();
		$dependentPicklists = [];
		while ($row = $dataReader->read()) {
			$sourceField = $row['sourcefield'];
			$targetField = $row['targetfield'];
			$moduleModel = Vtiger_Module_Model::getInstance($row['tabid']);
			$sourceFieldModel = Vtiger_Field_Model::getInstance($sourceField, $moduleModel);
			$targetFieldModel = Vtiger_Field_Model::getInstance($targetField, $moduleModel);
			if (!$sourceFieldModel || !$targetFieldModel) {
				continue;
			}
			$sourceFieldLabel = $sourceFieldModel->getFieldLabel();
			$targetFieldLabel = $targetFieldModel->getFieldLabel();
			$moduleName = $moduleModel->getName();
			$dependentPicklists[] = [
				'sourcefield' => $sourceField,
				'sourcefieldlabel' => \App\Language::translate($sourceFieldLabel, $moduleName),
				'targetfield' => $targetField,
				'targetfieldlabel' => \App\Language::translate($targetFieldLabel, $moduleName),
				'module' => $moduleName,
			];
		}
		$dataReader->close();

		return $dependentPicklists;
	}

	public static function savePickListDependencies($module, $dependencyMap)
	{
		$db = App\Db::getInstance();
		$tabId = \App\Module::getModuleId($module);
		$sourceField = $dependencyMap['sourcefield'];
		$targetField = $dependencyMap['targetfield'];
		$valueMapping = $dependencyMap['valuemapping'];
		$countValueMapping = count($valueMapping);
		for ($i = 0; $i < $countValueMapping; ++$i) {
			$mapping = $valueMapping[$i];
			$sourceValue = $mapping['sourcevalue'];
			$targetValues = $mapping['targetvalues'];
			$serializedTargetValues = \App\Json::encode($targetValues);
			$optionalsourcefield = $mapping['optionalsourcefield'] ?? '';
			$optionalsourcevalues = $mapping['optionalsourcevalues'] ?? '';
			if (!empty($optionalsourcefield)) {
				$criteria = [];
				$criteria['fieldname'] = $optionalsourcefield;
				$criteria['fieldvalues'] = $optionalsourcevalues;
				$serializedCriteria = \App\Json::encode($criteria);
			} else {
				$serializedCriteria = null;
			}
			//to handle Accent Sensitive search in MySql
			//reference Links http://dev.mysql.com/doc/refman/5.0/en/charset-convert.html , http://stackoverflow.com/questions/500826/how-to-conduct-an-accent-sensitive-search-in-mysql
			$dependencyId = (new App\Db\Query())->select(['id'])->from('vtiger_picklist_dependency')
				->where(['tabid' => $tabId, 'sourcefield' => $sourceField, 'targetfield' => $targetField, 'sourcevalue' => $sourceValue])
				->scalar();
			if ($dependencyId) {
				App\Db::getInstance()->createCommand()->update('vtiger_picklist_dependency', [
					'targetvalues' => $serializedTargetValues,
					'criteria' => $serializedCriteria,
				], ['id' => $dependencyId])->execute();
			} else {
				$db->createCommand()->insert('vtiger_picklist_dependency', [
					'id' => $db->getUniqueID('vtiger_picklist_dependency'),
					'tabid' => $tabId,
					'sourcefield' => $sourceField,
					'targetfield' => $targetField,
					'sourcevalue' => $sourceValue,
					'targetvalues' => $serializedTargetValues,
					'criteria' => $serializedCriteria,
				])->execute();
			}
		}
		\App\Cache::delete('picklistDependencyFields', $module);
		\App\Cache::delete('getPicklistDependencyDatasource', $module);
	}

	public static function deletePickListDependencies($module, $sourceField, $targetField)
	{
		App\Db::getInstance()->createCommand()->delete('vtiger_picklist_dependency', [
			'tabid' => \App\Module::getModuleId($module),
			'sourcefield' => $sourceField,
			'targetfield' => $targetField,
		])->execute();
		\App\Cache::delete('picklistDependencyFields', $module);
		\App\Cache::delete('getPicklistDependencyDatasource', $module);
	}

	public static function getPickListDependency($module, $sourceField, $targetField)
	{
		$dependencyMap['sourcefield'] = $sourceField;
		$dependencyMap['targetfield'] = $targetField;
		$dataReader = (new App\Db\Query())->from('vtiger_picklist_dependency')->where(['tabid' => \App\Module::getModuleId($module), 'sourcefield' => $sourceField, 'targetfield' => $targetField])
			->createCommand()->query();
		$valueMapping = [];
		while ($row = $dataReader->read()) {
			$valueMapping[] = [
				'sourcevalue' => $row['sourcevalue'],
				'targetvalues' => \App\Json::decode(html_entity_decode($row['targetvalues'])),
			];
		}
		$dataReader->close();
		$dependencyMap['valuemapping'] = $valueMapping;

		return $dependencyMap;
	}

	public static function getJSPicklistDependencyDatasource($module)
	{
		$picklistDependencyDatasource = \App\Fields\Picklist::getPicklistDependencyDatasource($module);

		return \App\Json::encode($picklistDependencyDatasource);
	}

	public static function checkCyclicDependency($module, $sourceField, $targetField)
	{
		// If another parent field exists for the same target field - 2 parent fields should not be allowed for a target field
		return (new App\Db\Query())->from('vtiger_picklist_dependency')
			->where(['tabid' => \App\Module::getModuleId($module), 'targetfield' => $targetField, 'sourcefield' => $sourceField, 'targetfield' => $targetField])
			->exists();
	}
}
