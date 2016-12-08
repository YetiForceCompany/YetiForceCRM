<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ******************************************************************************* */

require_once 'include/utils/utils.php';
require_once 'modules/PickList/PickListUtils.php';

class Vtiger_DependencyPicklist
{

	static function getDependentPicklistFields($module = '')
	{
		$adb = PearDatabase::getInstance();

		if (empty($module)) {
			$result = $adb->pquery('SELECT DISTINCT sourcefield, targetfield, tabid FROM vtiger_picklist_dependency', array());
		} else {
			$tabId = \App\Module::getModuleId($module);
			$result = $adb->pquery('SELECT DISTINCT sourcefield, targetfield, tabid FROM vtiger_picklist_dependency WHERE tabid=?', array($tabId));
		}
		$noofrows = $adb->num_rows($result);

		$dependentPicklists = array();
		if ($noofrows > 0) {
			$fieldlist = array();
			for ($i = 0; $i < $noofrows; ++$i) {
				$fieldTabId = $adb->query_result($result, $i, 'tabid');
				$sourceField = $adb->query_result($result, $i, 'sourcefield');
				$targetField = $adb->query_result($result, $i, 'targetfield');

				if (\vtlib\Functions::getModuleFieldId($fieldTabId, $sourceField) === false || \vtlib\Functions::getModuleFieldId($fieldTabId, $targetField) === false) {
					continue;
				}

				$fieldResult = $adb->pquery('SELECT fieldlabel FROM vtiger_field WHERE fieldname = ?', array($sourceField));
				$sourceFieldLabel = $adb->query_result($fieldResult, 0, 'fieldlabel');

				$fieldResult = $adb->pquery('SELECT fieldlabel FROM vtiger_field WHERE fieldname = ?', array($targetField));
				$targetFieldLabel = $adb->query_result($fieldResult, 0, 'fieldlabel');
				$forModule = \App\Module::getModuleName($fieldTabId);
				$dependentPicklists[] = array(
					'sourcefield' => $sourceField,
					'sourcefieldlabel' => vtranslate($sourceFieldLabel, $forModule),
					'targetfield' => $targetField,
					'targetfieldlabel' => vtranslate($targetFieldLabel, $forModule),
					'module' => $forModule
				);
			}
		}
		return $dependentPicklists;
	}

	static function getAvailablePicklists($module)
	{
		$adb = PearDatabase::getInstance();

		$tabId = \App\Module::getModuleId($module);

		$query = "select vtiger_field.fieldlabel,vtiger_field.fieldname" .
			" FROM vtiger_field inner join vtiger_picklist on vtiger_field.fieldname = vtiger_picklist.name" .
			" where displaytype=1 and vtiger_field.tabid=? and vtiger_field.uitype in ('15','16') " .
			" and vtiger_field.presence in (0,2) ORDER BY vtiger_picklist.picklistid ASC";

		$result = $adb->pquery($query, array($tabId));
		$noofrows = $adb->num_rows($result);

		$fieldlist = array();
		if ($noofrows > 0) {
			for ($i = 0; $i < $noofrows; ++$i) {
				$fieldlist[$adb->query_result($result, $i, "fieldname")] = $adb->query_result($result, $i, "fieldlabel");
			}
		}
		return $fieldlist;
	}

	static function savePickListDependencies($module, $dependencyMap)
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

			$optionalsourcefield = $mapping['optionalsourcefield'];
			$optionalsourcevalues = $mapping['optionalsourcevalues'];

			if (!empty($optionalsourcefield)) {
				$criteria = array();
				$criteria["fieldname"] = $optionalsourcefield;
				$criteria["fieldvalues"] = $optionalsourcevalues;
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
	}

	static function deletePickListDependencies($module, $sourceField, $targetField)
	{
		App\Db::getInstance()->createCommand()->delete('vtiger_picklist_dependency', [
			'tabid' => \App\Module::getModuleId($module),
			'sourcefield' => $sourceField,
			'targetfield' => $targetField
		])->execute();
	}

	static function getPickListDependency($module, $sourceField, $targetField)
	{
		$dependencyMap['sourcefield'] = $sourceField;
		$dependencyMap['targetfield'] = $targetField;
		$dataReader = (new App\Db\Query())->from('vtiger_picklist_dependency')->where(['tabid' => \App\Module::getModuleId($module), 'sourcefield' => $sourceField, 'targetfield' => $targetField])
			->createCommand()->query();
		$valueMapping = [];
		while ($row = $dataReader->read()) {
			$valueMapping[] = [
				'sourcevalue' => $row['sourcevalue'],
				'targetvalues' => \App\Json::decode(html_entity_decode($row['targetvalues']))
			];
		}
		$dependencyMap['valuemapping'] = $valueMapping;
		return $dependencyMap;
	}

	static function getPicklistDependencyDatasource($module)
	{
		$adb = PearDatabase::getInstance();

		$tabId = \App\Module::getModuleId($module);

		$result = $adb->pquery('SELECT * FROM vtiger_picklist_dependency WHERE tabid=?', array($tabId));
		$noofrows = $adb->num_rows($result);

		$picklistDependencyDatasource = array();
		for ($i = 0; $i < $noofrows; ++$i) {
			$pickArray = array();
			$sourceField = $adb->query_result($result, $i, 'sourcefield');
			$targetField = $adb->query_result($result, $i, 'targetfield');
			$sourceValue = decode_html($adb->query_result($result, $i, 'sourcevalue'));
			$targetValues = decode_html($adb->query_result($result, $i, 'targetvalues'));
			$unserializedTargetValues = \App\Json::decode(html_entity_decode($targetValues));
			$criteria = decode_html($adb->query_result($result, $i, 'criteria'));
			$unserializedCriteria = \App\Json::decode(html_entity_decode($criteria));

			if (!empty($unserializedCriteria) && $unserializedCriteria['fieldname'] != null) {
				$conditionValue = array(
					"condition" => array($unserializedCriteria['fieldname'] => $unserializedCriteria['fieldvalues']),
					"values" => $unserializedTargetValues
				);
				$picklistDependencyDatasource[$sourceField][$sourceValue][$targetField][] = $conditionValue;
			} else {
				$picklistDependencyDatasource[$sourceField][$sourceValue][$targetField] = $unserializedTargetValues;
			}
			if (empty($picklistDependencyDatasource[$sourceField]['__DEFAULT__'][$targetField])) {
				foreach (App\Fields\Picklist::getPickListValues($targetField) as $picklistValue) {
					$pickArray[] = decode_html($picklistValue);
				}
				$picklistDependencyDatasource[$sourceField]['__DEFAULT__'][$targetField] = $pickArray;
			}
		}
		return $picklistDependencyDatasource;
	}

	static function getJSPicklistDependencyDatasource($module)
	{
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($module);
		return \App\Json::encode($picklistDependencyDatasource);
	}

	static function checkCyclicDependency($module, $sourceField, $targetField)
	{
		// If another parent field exists for the same target field - 2 parent fields should not be allowed for a target field
		return (new App\Db\Query())->from('vtiger_picklist_dependency')
				->where(['tabid' => \App\Module::getModuleId($module), 'targetfield' => $targetField, 'sourcefield' => $sourceField, 'targetfield' => $targetField])
				->exists();
	}

	static function getDependentPickListModules()
	{
		$adb = PearDatabase::getInstance();

		$query = 'SELECT distinct vtiger_field.tabid, vtiger_tab.tablabel, vtiger_tab.name as tabname FROM vtiger_field
						INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid
						INNER JOIN vtiger_picklist ON vtiger_picklist.name = vtiger_field.fieldname
					WHERE uitype IN (15,16)
						AND vtiger_field.tabid != 29
						AND vtiger_field.displaytype = 1
						AND vtiger_field.presence in (0,2)
					GROUP BY vtiger_field.tabid HAVING count(*) > 1';
		// END
		$result = $adb->pquery($query, array());
		while ($row = $adb->fetch_array($result)) {
			$modules[$row['tablabel']] = $row['tabname'];
		}
		ksort($modules);
		return $modules;
	}
}

?>
