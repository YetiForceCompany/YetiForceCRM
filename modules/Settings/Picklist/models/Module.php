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
		$db = PearDatabase::getInstance();
		$pickListFieldName = $fieldModel->getName();
		$id = $db->getUniqueID("vtiger_$pickListFieldName");
		$picklistValueId = $db->getUniqueID('vtiger_picklistvalues');
		$tableName = 'vtiger_' . $pickListFieldName;
		$maxSeqQuery = sprintf('SELECT max(sortorderid) as maxsequence FROM %s', $tableName);
		$result = $db->query($maxSeqQuery);
		$sequence = $db->getSingleValue($result);
		$columnNames = $db->getColumnNames($tableName);

		if ($fieldModel->isRoleBased()) {
			if (in_array('color', $columnNames)) {
				$sql = 'INSERT INTO ' . $tableName . ' VALUES (?,?,?,?,?,?)';
				$result = $db->pquery($sql, [$id, $newValue, 1, $picklistValueId, ++$sequence, '#E6FAD8']);
			} else {
				$sql = 'INSERT INTO ' . $tableName . ' VALUES (?,?,?,?,?)';
				$result = $db->pquery($sql, [$id, $newValue, 1, $picklistValueId, ++$sequence]);
			}
		} else {
			if (in_array('color', $columnNames)) {
				$sql = 'INSERT INTO ' . $tableName . ' VALUES (?,?,?,?,?)';
				$db->pquery($sql, [$id, $newValue, ++$sequence, 1, '#E6FAD8']);
			} else {
				$sql = 'INSERT INTO ' . $tableName . ' VALUES (?,?,?,?)';
				$db->pquery($sql, [$id, $newValue, ++$sequence, 1]);
			}
		}

		if ($fieldModel->isRoleBased() && !empty($rolesSelected)) {
			$sql = 'SELECT picklistid FROM vtiger_picklist WHERE `name` = ?;';
			$result = $db->pquery($sql, [$pickListFieldName]);
			$picklistid = $db->getSingleValue($result);
			//add the picklist values to the selected roles
			for ($j = 0; $j < count($rolesSelected); $j++) {
				$roleid = $rolesSelected[$j];

				$sql = "SELECT max(sortid)+1 as sortid
                       FROM vtiger_role2picklist left join vtiger_$pickListFieldName
                           on vtiger_$pickListFieldName.picklist_valueid=vtiger_role2picklist.picklistvalueid
                       WHERE roleid=? and picklistid=?";
				$sortid = $db->getSingleValue($db->pquery($sql, [$roleid, $picklistid]));

				$db->insert('vtiger_role2picklist', [
					'roleid' => $roleid,
					'picklistvalueid' => $picklistValueId,
					'picklistid' => $picklistid,
					'sortid' => $sortid
				]);
			}
		}
		return ['picklistValueId' => $picklistValueId, 'id' => $id];
	}

	public function renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName, $id)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT tablename,columnname FROM vtiger_field WHERE fieldname=? and presence IN (0,2)';
		$result = $db->pquery($query, array($pickListFieldName));
		$num_rows = $db->num_rows($result);

		//As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
		//so we are checking for both the values
		$primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);

		$db->update($this->getPickListTableName($pickListFieldName), [$pickListFieldName => $newValue], $primaryKey . ' = ?', [$id]);
		for ($i = 0; $i < $num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			$db->update($tableName, [$columnName => $newValue], $columnName . ' = ?', [$oldValue]);
		}

		$query = "UPDATE vtiger_field SET defaultvalue=? WHERE defaultvalue=? && columnname=?";
		$db->pquery($query, array($newValue, $oldValue, $columnName));

		vimport('include/utils/CommonUtils.php');

		$db->update('vtiger_picklist_dependency', ['sourcevalue' => $newValue], 'sourcevalue = ? && sourcefield = ?', [$oldValue, $pickListFieldName]);

		$em = new VTEventsManager($db);
		$data = array();
		$data['fieldname'] = $pickListFieldName;
		$data['oldvalue'] = $oldValue;
		$data['newvalue'] = $newValue;
		$data['module'] = $moduleName;
		$em->triggerEvent('vtiger.picklist.afterrename', $data);

		return true;
	}

	public function remove($pickListFieldName, $valueToDeleteId, $replaceValueId, $moduleName)
	{
		$db = PearDatabase::getInstance();
		if (!is_array($valueToDeleteId)) {
			$valueToDeleteId = array($valueToDeleteId);
		}
		$primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);

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
		$encodedValueToDelete = array();
		foreach ($pickListValues as $key => $value) {
			$encodedValueToDelete[$key] = Vtiger_Util_Helper::toSafeHTML($value);
		}
		$mergedValuesToDelete = array_merge($pickListValues, $encodedValueToDelete);

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

		vimport('include/utils/CommonUtils.php');
		$tabId = \includes\Modules::getModuleId($moduleName);
		$params = [];
		array_push($params, $pickListValues);
		array_push($params, $pickListFieldName);
		$db->delete('vtiger_picklist_dependency', 'sourcevalue IN (' . generateQuestionMarks($pickListValues) . ') && sourcefield = ?', $params);

		$query = 'SELECT tablename,columnname FROM vtiger_field WHERE fieldname=? && presence in (0,2)';
		$result = $db->pquery($query, array($pickListFieldName));
		$num_row = $db->num_rows($result);

		for ($i = 0; $i < $num_row; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			$db->update($tableName, [$columnName => $replaceValue], $columnName . ' IN (' . generateQuestionMarks($pickListValues) . ')', $pickListValues);
		}
		$params = [];
		array_push($params, $pickListValues);
		array_push($params, $columnName);
		$db->update('vtiger_field', ['defaultvalue' => $replaceValue], 'defaultvalue IN (' . generateQuestionMarks($pickListValues) . ') && columnname=?', $params);

		$em = new VTEventsManager($db);
		$data = array();
		$data['fieldname'] = $pickListFieldName;
		$data['valuetodelete'] = $pickListValues;
		$data['replacevalue'] = $replaceValue;
		$data['module'] = $moduleName;
		$em->triggerEvent('vtiger.picklist.afterdelete', $data);

		return true;
	}

	public function enableOrDisableValuesForRole($picklistFieldName, $valuesToEnables, $valuesToDisable, $roleIdList)
	{
		$db = PearDatabase::getInstance();
		//To disable die On error since we will be doing insert without chekcing
		$dieOnErrorOldValue = $db->dieOnError;
		$db->dieOnError = false;

		$sql = "select picklistid from vtiger_picklist where name=?";
		$result = $db->pquery($sql, array($picklistFieldName));
		$picklistid = $db->query_result($result, 0, "picklistid");

		$primaryKey = Vtiger_Util_Helper::getPickListId($picklistFieldName);

		$pickListValueList = array_merge($valuesToEnables, $valuesToDisable);
		$pickListValueDetails = array();
		$query = sprintf('SELECT picklist_valueid, %s, %s FROM %s WHERE %s IN (%s)', $picklistFieldName, $primaryKey, $this->getPickListTableName($picklistFieldName), $primaryKey, generateQuestionMarks($pickListValueList));
		$params = array();
		array_push($params, $pickListValueList);

		$result = $db->pquery($query, $params);
		$num_rows = $db->num_rows($result);

		for ($i = 0; $i < $num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);

			$pickListValueDetails[decode_html($row[$primaryKey])] = array('picklistvalueid' => $row['picklist_valueid'],
				'picklistid' => $picklistid);
		}
		$insertValueList = array();
		$deleteValueList = array();
		foreach ($roleIdList as $roleId) {
			foreach ($valuesToEnables as $picklistValue) {
				$valueDetail = $pickListValueDetails[$picklistValue];
				if (empty($valueDetail)) {
					$valueDetail = $pickListValueDetails[Vtiger_Util_Helper::toSafeHTML($picklistValue)];
				}
				$pickListValueId = $valueDetail['picklistvalueid'];
				$picklistId = $valueDetail['picklistid'];
				$insertValueList[] = '("' . $roleId . '","' . $pickListValueId . '","' . $picklistId . '")';
			}

			foreach ($valuesToDisable as $picklistValue) {
				$valueDetail = $pickListValueDetails[$picklistValue];
				if (empty($valueDetail)) {
					$valueDetail = $pickListValueDetails[Vtiger_Util_Helper::toSafeHTML($picklistValue)];
				}
				$pickListValueId = $valueDetail['picklistvalueid'];
				$picklistId = $valueDetail['picklistid'];
				$deleteValueList[] = ' ( roleid = "' . $roleId . '" && ' . 'picklistvalueid = "' . $pickListValueId . '") ';
			}
		}
		$query = 'INSERT IGNORE INTO vtiger_role2picklist (roleid,picklistvalueid,picklistid) VALUES ' . implode(',', $insertValueList);
		$result = $db->pquery($query, array());
		if ($deleteValueList) {
			$db->delete('vtiger_role2picklist', implode(' || ', $deleteValueList));
		}
		//retaining to older value
		$db->dieOnError = $dieOnErrorOldValue;
	}

	public function updateSequence($pickListFieldName, $picklistValues)
	{
		$db = PearDatabase::getInstance();

		$primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);

		$query = sprintf('UPDATE %s SET sortorderid = CASE ', $this->getPickListTableName($pickListFieldName));
		foreach ($picklistValues as $values => $sequence) {
			$query .= ' WHEN ' . $primaryKey . '="' . $values . '" THEN "' . $sequence . '"';
		}
		$query .= ' END';
		$db->pquery($query, array());
	}

	public static function getPicklistSupportedModules()
	{
		$db = PearDatabase::getInstance();

		// vtlib customization: Ignore disabled modules.
		$query = "SELECT DISTINCT 
					vtiger_tab.tablabel,
					vtiger_tab.name AS tabname 
				  FROM
					vtiger_tab 
					INNER JOIN vtiger_field 
					  ON vtiger_tab.tabid = vtiger_field.tabid 
				  WHERE uitype IN (15, 33, 16) 
					AND vtiger_field.tabid NOT IN (29, 10) 
					AND vtiger_tab.presence != 1 
					AND vtiger_field.presence IN (0, 2) 
					AND vtiger_field.`columnname` !=  'taxtype'
				  ORDER BY vtiger_tab.tabid ASC ";
		// END
		$result = $db->pquery($query, array());

		$modulesModelsList = array();
		while ($row = $db->fetch_array($result)) {
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
