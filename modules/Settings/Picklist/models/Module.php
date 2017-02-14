<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_Picklist_Module_Model extends Vtiger_Module_Model
{

	public function getPickListTableName($fieldName)
	{
		return 'vtiger_' . $fieldName;
	}

	public function getFieldsByType($type)
	{
		$presence = array('0', '2');

		$fieldModels = parent::getFieldsByType($type);
		$fields = array();
		foreach ($fieldModels as $fieldName => $fieldModel) {
			if ((!in_array($fieldModel->get('displaytype'), [1, 10]) && $fieldName != 'salutationtype') || !in_array($fieldModel->get('presence'), $presence)) {
				continue;
			}
			$fields[$fieldName] = Settings_Picklist_Field_Model::getInstanceFromFieldObject($fieldModel);
		}
		return $fields;
	}

	public function addPickListValues($fieldModel, $newValue, $rolesSelected = [])
	{
		$db = App\Db::getInstance();
		$pickListFieldName = $fieldModel->getName();
		$tableName = 'vtiger_' . $pickListFieldName;
		if ($db->isTableExists($tableName . '_seq')) {
			$id = $db->getUniqueID($tableName);
		} else {
			$id = $db->getUniqueID($tableName, $pickListFieldName . 'id', false);
		}
		$picklistValueId = $db->getUniqueID('vtiger_picklistvalues');
		$sequence = (new \App\Db\Query())->from($tableName)->max('sortorderid');
		$columnNames = $db->getTableSchema($tableName)->getColumnNames();
		if ($fieldModel->isRoleBased()) {
			if (in_array('color', $columnNames)) {
				$db->createCommand()->batchInsert($tableName, $columnNames, [[$id, $newValue, 1, $picklistValueId, ++$sequence, '#E6FAD8']])->execute();
			} else {
				$db->createCommand()->batchInsert($tableName, $columnNames, [[$id, $newValue, 1, $picklistValueId, ++$sequence]])->execute();
			}
		} else {
			if (in_array('color', $columnNames)) {
				$db->createCommand()->batchInsert($tableName, $columnNames, [[$id, $newValue, ++$sequence, 1, '#E6FAD8']])->execute();
			} else {
				$db->createCommand()->batchInsert($tableName, $columnNames, [[$id, $newValue, ++$sequence, 1]])->execute();
			}
		}
		if ($fieldModel->isRoleBased() && !empty($rolesSelected)) {
			$picklistid = (new \App\Db\Query())->select(['picklistid'])
				->from('vtiger_picklist')
				->where(['name' => $pickListFieldName])
				->scalar();
			//add the picklist values to the selected roles
			foreach ($rolesSelected as $roleid) {
				$sortid = (new \App\Db\Query())->from('vtiger_role2picklist')
						->leftJoin("vtiger_$pickListFieldName", "vtiger_$pickListFieldName.picklist_valueid = vtiger_role2picklist.picklistvalueid")
						->where(['roleid' => $roleid, 'picklistid' => $picklistid])
						->max('sortid') + 1;
				$db->createCommand()->insert('vtiger_role2picklist', [
					'roleid' => $roleid,
					'picklistvalueid' => $picklistValueId,
					'picklistid' => $picklistid,
					'sortid' => $sortid
				])->execute();
			}
		}
		return ['picklistValueId' => $picklistValueId, 'id' => $id];
	}

	public function renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName, $id)
	{
		$db = App\Db::getInstance();
		$dataReader = (new \App\Db\Query())->select(['tablename', 'columnname'])
				->from('vtiger_field')
				->where(['fieldname' => $pickListFieldName, 'presence' => [0, 2]])
				->createCommand()->query();
		//As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
		//so we are checking for both the values
		$primaryKey = App\Fields\Picklist::getPickListId($pickListFieldName);
		$db->createCommand()->update($this->getPickListTableName($pickListFieldName), [$pickListFieldName => $newValue], [$primaryKey => $id])
			->execute();
		while ($row = $dataReader->read()) {
			$columnName = $row['columnname'];
			$db->createCommand()->update($row['tablename'], [$columnName => $newValue], [$columnName => $oldValue])->execute();
		}
		$db->createCommand()->update('vtiger_field', ['defaultvalue' => $newValue], ['defaultvalue' => $oldValue, 'columnname' => $columnName])->execute();
		$db->createCommand()->update('vtiger_picklist_dependency', ['sourcevalue' => $newValue], ['sourcevalue' => $oldValue, 'sourcefield' => $pickListFieldName])->execute();
		$eventHandler = new App\EventHandler();
		$eventHandler->setParams([
			'fieldname' => $pickListFieldName,
			'oldvalue' => $oldValue,
			'newvalue' => $newValue,
			'module' => $moduleName,
			'id' => $id,
		]);
		$eventHandler->trigger('PicklistAfterRename');
		return true;
	}

	public function remove($pickListFieldName, $valueToDeleteId, $replaceValueId, $moduleName)
	{
		$db = PearDatabase::getInstance();
		$adb = App\Db::getInstance();
		if (!is_array($valueToDeleteId)) {
			$valueToDeleteId = array($valueToDeleteId);
		}
		$primaryKey = App\Fields\Picklist::getPickListId($pickListFieldName);

		$pickListValues = array();
		$valuesOfDeleteIds = "SELECT $pickListFieldName FROM " . $this->getPickListTableName($pickListFieldName) . " WHERE $primaryKey IN (" . generateQuestionMarks($valueToDeleteId) . ")";
		$pickListValuesResult = $db->pquery($valuesOfDeleteIds, array($valueToDeleteId));
		$num_rows = $db->num_rows($pickListValuesResult);
		for ($i = 0; $i < $num_rows; $i++) {
			$pickListValues[] = decode_html($db->query_result($pickListValuesResult, $i, $pickListFieldName));
		}

		$replaceValueQuery = $db->pquery("SELECT $pickListFieldName FROM " . $this->getPickListTableName($pickListFieldName) . " WHERE $primaryKey IN (" . generateQuestionMarks($replaceValueId) . ")", array($replaceValueId));
		$replaceValue = decode_html($db->query_result($replaceValueQuery, 0, $pickListFieldName));

		//As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
		//so we are checking for both the values

		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, $this);
		//if role based then we need to delete all the values in role based picklist
		if ($fieldModel->isRoleBased()) {
			$picklistValueIdToDelete = array();
			$query = sprintf('SELECT picklist_valueid FROM %s WHERE %s IN (%s)', $this->getPickListTableName($pickListFieldName), $primaryKey, generateQuestionMarks($valueToDeleteId));
			$result = $db->pquery($query, $valueToDeleteId);
			$num_rows = $db->num_rows($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$picklistValueIdToDelete[] = $db->query_result($result, $i, 'picklist_valueid');
			}
			$db->delete('vtiger_role2picklist', 'picklistvalueid IN (' . generateQuestionMarks($picklistValueIdToDelete) . ')', $picklistValueIdToDelete);
		}
		$db->delete($this->getPickListTableName($pickListFieldName), $primaryKey . ' IN (' . generateQuestionMarks($valueToDeleteId) . ')', $valueToDeleteId);
		$adb->createCommand()->delete('vtiger_picklist_dependency', ['sourcevalue' => $pickListValues, 'sourcefield' => $pickListFieldName])
			->execute();

		$dataReader = (new \App\Db\Query())->select(['tablename', 'columnname'])
				->from('vtiger_field')
				->where(['fieldname' => $pickListFieldName, 'presence' => [0, 2]])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			$adb->createCommand()->update($tableName, [$columnName => $replaceValue], [$columnName => $pickListValues])
				->execute();
		}
		$adb->createCommand()->update('vtiger_field', ['defaultvalue' => $replaceValue], ['defaultvalue' => $pickListValues, 'columnname' => $columnName])
			->execute();
		$eventHandler = new App\EventHandler();
		$eventHandler->setParams([
			'fieldname' => $pickListFieldName,
			'valuetodelete' => $pickListValues,
			'replacevalue' => $replaceValue,
			'module' => $moduleName
		]);
		$eventHandler->trigger('PicklistAfterDelete');
		return true;
	}

	public function enableOrDisableValuesForRole($picklistFieldName, $valuesToEnables, $valuesToDisable, $roleIdList)
	{
		$db = App\Db::getInstance();
		$picklistid = (new App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')
				->where(['name' => $picklistFieldName])->scalar();
		$primaryKey = App\Fields\Picklist::getPickListId($picklistFieldName);
		$pickListValueList = array_merge($valuesToEnables, $valuesToDisable);
		$dataReader = (new App\Db\Query())->select(['picklist_valueid', $picklistFieldName, $primaryKey])
				->from($this->getPickListTableName($picklistFieldName))
				->where([$primaryKey => $pickListValueList])
				->createCommand()->query();
		$pickListValueDetails = [];
		while ($row = $dataReader->read()) {
			$pickListValueDetails[decode_html($row[$primaryKey])] = [
				'picklistvalueid' => $row['picklist_valueid'],
				'picklistid' => $picklistid
			];
		}
		$insertValueList = [];
		$deleteValueList = ['or'];
		foreach ($roleIdList as $roleId) {
			foreach ($valuesToEnables as $picklistValue) {
				$valueDetail = $pickListValueDetails[$picklistValue];
				if (empty($valueDetail)) {
					$valueDetail = $pickListValueDetails[Vtiger_Util_Helper::toSafeHTML($picklistValue)];
				}
				$pickListValueId = $valueDetail['picklistvalueid'];
				$picklistId = $valueDetail['picklistid'];
				$insertValueList [] = [$roleId, $pickListValueId, $picklistId];
				$deleteValueList [] = ['roleid' => $roleId, 'picklistvalueid' => $pickListValueId];
			}
			foreach ($valuesToDisable as $picklistValue) {
				$valueDetail = $pickListValueDetails[$picklistValue];
				if (empty($valueDetail)) {
					$valueDetail = $pickListValueDetails[Vtiger_Util_Helper::toSafeHTML($picklistValue)];
				}
				$pickListValueId = $valueDetail['picklistvalueid'];
				$deleteValueList [] = ['roleid' => $roleId, 'picklistvalueid' => $pickListValueId];
			}
		}
		if ($deleteValueList) {
			$db->createCommand()->delete('vtiger_role2picklist', $deleteValueList)->execute();
		}
		$db->createCommand()->batchInsert('vtiger_role2picklist', ['roleid', 'picklistvalueid', 'picklistid'], $insertValueList)->execute();
	}

	public function updateSequence($pickListFieldName, $picklistValues)
	{
		$db = PearDatabase::getInstance();

		$primaryKey = App\Fields\Picklist::getPickListId($pickListFieldName);
		$set = ' CASE ';
		foreach ($picklistValues as $values => $sequence) {
			$set .= ' WHEN ' . $primaryKey . '=' . $values . ' THEN ' . $sequence . '';
		}
		$set .= ' END';
		$expression = new \yii\db\Expression($set);
		\App\Db::getInstance()->createCommand()->update($this->getPickListTableName($pickListFieldName), ['sortorderid' => $expression])->execute();
	}

	public static function getPicklistSupportedModules()
	{
		$dataReader = (new App\Db\Query())->select(['vtiger_tab.tabid', 'vtiger_tab.tablabel', 'tabname' => 'vtiger_tab.name'])
				->from('vtiger_tab')
				->innerJoin('vtiger_field', 'vtiger_tab.tabid = vtiger_field.tabid')
				->where([
					'and',
					['uitype' => [15, 33, 16]],
					['NOT IN', 'vtiger_field.tabid', [29, 10]],
					['<>', 'vtiger_tab.presence', 1],
					['vtiger_field.presence' => [0, 2]],
					['<>', 'vtiger_field.columnname', 'taxtype']
				])->orderBy(['vtiger_tab.tabid' => SORT_ASC])
				->distinct()
				->createCommand()->query();
		$modulesModelsList = [];
		while ($row = $dataReader->read()) {
			$moduleLabel = $row['tablabel'];
			$moduleName = $row['tabname'];
			$instance = new self();
			$instance->name = $moduleName;
			$instance->label = $moduleLabel;
			$modulesModelsList[] = $instance;
		}
		return $modulesModelsList;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name
	 * @param mixed id or name of the module
	 */
	public static function getInstance($value)
	{
		$instance = false;
		$moduleObject = parent::getInstance($value);
		if ($moduleObject) {
			$instance = self::getInstanceFromModuleObject($moduleObject);
		}
		return $instance;
	}

	/**
	 * Function to get the instance of Vtiger Module Model from a given vtlib\Module object
	 * @param vtlib\Module $moduleObj
	 * @return Vtiger_Module_Model instance
	 */
	public static function getInstanceFromModuleObject(vtlib\Module $moduleObj)
	{
		$objectProperties = get_object_vars($moduleObj);
		$moduleModel = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$moduleModel->$properName = $propertyValue;
		}
		return $moduleModel;
	}
}
