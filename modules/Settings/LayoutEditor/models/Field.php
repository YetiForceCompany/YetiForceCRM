<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_LayoutEditor_Field_Model extends Vtiger_Field_Model
{

	public function delete()
	{
		$adb = PearDatabase::getInstance();
		parent::delete();

		$fld_module = $this->getModuleName();
		$id = $this->getId();
		$uitype = $this->get('uitype');
		$typeofdata = $this->get('typeofdata');
		$fieldname = $this->getName();
		$oldfieldlabel = $this->get('label');
		$tablename = $this->get('table');
		$columnname = $this->get('column');
		$fieldtype = explode("~", $typeofdata);
		$tabId = $this->getModuleId();

		$focus = CRMEntity::getInstance($fld_module);

		$deletecolumnname = $tablename . ":" . $columnname . ":" . $fieldname . ":" . $fld_module . "_" . str_replace(" ", "_", $oldfieldlabel) . ":" . $fieldtype[0];
		$column_cvstdfilter = $tablename . ":" . $columnname . ":" . $fieldname . ":" . $fld_module . "_" . str_replace(" ", "_", $oldfieldlabel);
		$select_columnname = $tablename . ":" . $columnname . ":" . $fld_module . "_" . str_replace(" ", "_", $oldfieldlabel) . ":" . $fieldname . ":" . $fieldtype[0];
		$reportsummary_column = $tablename . ":" . $columnname . ":" . str_replace(" ", "_", $oldfieldlabel);
		if ($tablename != 'vtiger_crmentity') {
			$dbquery = 'alter table ' . $adb->quote($tablename, false) . ' drop column ' . $adb->quote($columnname, false);
			$adb->pquery($dbquery, array());
		}
		//we have to remove the entries in customview and report related tables which have this field ($colName)
		$adb->pquery('delete from vtiger_cvcolumnlist where columnname = ? ', array($deletecolumnname));
		$adb->pquery('delete from vtiger_cvstdfilter where columnname = ?', array($column_cvstdfilter));
		$adb->pquery('delete from vtiger_cvadvfilter where columnname = ?', array($deletecolumnname));
		$adb->pquery('delete from vtiger_selectcolumn where columnname = ?', array($select_columnname));
		$adb->pquery('delete from vtiger_relcriteria where columnname = ?', array($select_columnname));
		$adb->pquery('delete from vtiger_reportsortcol where columnname = ?', array($select_columnname));
		$adb->pquery('delete from vtiger_reportdatefilter where datecolumnname = ?', array($column_cvstdfilter));
		$adb->pquery('delete from vtiger_reportsummary where columnname like ?', array('%' . $reportsummary_column . '%'));

		//Deleting from convert lead mapping vtiger_table- Jaguar
		if ($fld_module == 'Leads') {
			$deletequery = 'delete from vtiger_convertleadmapping where leadfid=?';
			$adb->pquery($deletequery, array($id));
		} elseif ($fld_module == 'Accounts' || $fld_module == 'Contacts' || $fld_module == 'Potentials') {
			$map_del_id = array('Accounts' => 'accountfid', 'Contacts' => 'contactfid', 'Potentials' => 'potentialfid');
			$map_del_q = 'update vtiger_convertleadmapping set ' . $map_del_id[$fld_module] . '=0 where ' . $map_del_id[$fld_module] . '=?';
			$adb->pquery($map_del_q, array($id));
		}

		//HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
		if ($this->getFieldDataType() == 'picklist' || $this->getFieldDataType() == 'multipicklist') {
			$result = $adb->pquery('SELECT * FROM `vtiger_field` WHERE `columnname` = ? AND `uitype` IN (?,?,?);', [$columnname, 15, 16, 33]);
			if (!$adb->num_rows($result)) {
				$adb->query('drop table vtiger_' . $columnname);
				//To Delete Sequence Table 
				if (Vtiger_Utils::CheckTable('vtiger_' . $columnname . '_seq')) {
					$adb->query('drop table vtiger_' . $columnname . '_seq');
				}
				$adb->pquery('delete from vtiger_picklist where name=? ', array($columnname));
			}
			$adb->pquery('delete from vtiger_picklist_dependency where `tabid` = ? AND (sourcefield=? or targetfield=?)', array($tabId, $columnname, $columnname));
		}
	}

	/**
	 * Function to Move the field
	 * @param <Array> $fieldNewDetails
	 * @param <Array> $fieldOlderDetails
	 */
	public function move($fieldNewDetails, $fieldOlderDetails)
	{
		$db = PearDatabase::getInstance();

		$newBlockId = $fieldNewDetails['blockId'];
		$olderBlockId = $fieldOlderDetails['blockId'];

		$newSequence = $fieldNewDetails['sequence'];
		$olderSequence = $fieldOlderDetails['sequence'];

		if ($olderBlockId == $newBlockId) {
			if ($newSequence > $olderSequence) {
				$updateQuery = 'UPDATE vtiger_field SET sequence = sequence-1 WHERE sequence > ? AND sequence <= ? AND block = ?';
				$params = array($olderSequence, $newSequence, $olderBlockId);
				$db->pquery($updateQuery, $params);
			} else if ($newSequence < $olderSequence) {
				$updateQuery = 'UPDATE vtiger_field SET sequence = sequence+1 WHERE sequence < ? AND sequence >= ? AND block = ?';
				$params = array($olderSequence, $newSequence, $olderBlockId);
				$db->pquery($updateQuery, $params);
			}
			$query = 'UPDATE vtiger_field SET sequence = ? WHERE fieldid = ?';
			$params = array($newSequence, $this->getId());
			$db->pquery($query, $params);
		} else {
			$updateOldBlockQuery = 'UPDATE vtiger_field SET sequence = sequence-1 WHERE sequence > ? AND block = ?';
			$params = array($olderSequence, $olderBlockId);
			$db->pquery($updateOldBlockQuery, $params);

			$updateNewBlockQuery = 'UPDATE vtiger_field SET sequence = sequence+1 WHERE sequence >= ? AND block = ?';
			$params = array($newSequence, $newBlockId);
			$db->pquery($updateNewBlockQuery, $params);

			$query = 'UPDATE vtiger_field SET sequence = ?, block = ? WHERE fieldid = ?';
			$params = array($newSequence, $newBlockId, $this->getId());
			$db->pquery($query, $params);
		}
	}

	public static function makeFieldActive($fieldIdsList = array(), $blockId)
	{
		$db = PearDatabase::getInstance();
		$maxSequenceQuery = "SELECT MAX(sequence) AS maxsequence FROM vtiger_field WHERE block = ? AND presence IN (0,2) ";
		$res = $db->pquery($maxSequenceQuery, array($blockId));
		$maxSequence = $db->query_result($res, 0, 'maxsequence');

		$query = 'UPDATE vtiger_field SET presence = 2, sequence = CASE';
		foreach ($fieldIdsList as $fieldId) {
			$maxSequence = $maxSequence + 1;
			$query .= ' WHEN fieldid = ? THEN ' . $maxSequence;
		}
		$query .= ' ELSE sequence END';
		$query .= ' WHERE fieldid IN (' . generateQuestionMarks($fieldIdsList) . ')';

		$db->pquery($query, array_merge($fieldIdsList, $fieldIdsList));
	}

	/**
	 * Function which specifies whether the field can have mandatory switch to happen
	 * @return <Boolean> - true if we can make a field mandatory and non mandatory , false if we cant change previous state
	 */
	public function isMandatoryOptionDisabled()
	{
		$moduleModel = $this->getModule();
		$complusoryMandatoryFieldList = $moduleModel->getCumplosoryMandatoryFieldList();
		//uitypes for which mandatory switch is disabled
		$mandatoryRestrictedUitypes = array('4', '70');
		if (in_array($this->getName(), $complusoryMandatoryFieldList)) {
			return true;
		}
		if (in_array($this->get('uitype'), $mandatoryRestrictedUitypes) || $this->get('displaytype') == 2) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the active option is disabled
	 * @return boolean
	 */
	public function isActiveOptionDisabled()
	{
		if ($this->get('presence') == 0 || $this->get('displaytype') == 2 || $this->isMandatoryOptionDisabled()) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the quickcreate option is disabled
	 * @return boolean
	 */
	public function isQuickCreateOptionDisabled()
	{
		$moduleModel = $this->getModule();
		if ($this->get('quickcreate') == 0 || $this->get('quickcreate') == 3 || !$moduleModel->isQuickCreateSupported() || $this->get('uitype') == 69) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the mass edit option is disabled
	 * @return boolean
	 */
	public function isMassEditOptionDisabled()
	{
		if ($this->get('masseditable') == 0 || $this->get('displaytype') != 1 || $this->get('masseditable') == 3 || $this->get('uitype') == 69) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the default value option is disabled
	 * @return boolean
	 */
	public function isDefaultValueOptionDisabled()
	{
		if ($this->isMandatoryOptionDisabled() || $this->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE || $this->get('uitype') == 69) {
			return true;
		}
		return false;
	}

	/**
	 * Function to check whether summary field option is disable or not
	 * @return <Boolean> true/false
	 */
	public function isSummaryFieldOptionDisabled()
	{
		$moduleModel = $this->getModule();
		if ($this->get('uitype') == 70) {
			return true;
		}
		return false;
	}

	/**
	 * Function to check field is editable or not
	 * @return <Boolean> true/false
	 */
	public function isEditable()
	{
		$moduleName = $this->block->module->name;
		if (in_array($moduleName, array('Calendar', 'Events'))) {
			return false;
		}
		return true;
	}

	/**
	 * Function to get instance
	 * @param <String> $value - fieldname or fieldid
	 * @param <type> $module - optional - module instance
	 * @return <Settings_LayoutEditor_Field_Model>
	 */
	public static function getInstance($value, $module = false)
	{
		$fieldObject = parent::getInstance($value, $module);
		$objectProperties = get_object_vars($fieldObject);
		$fieldModel = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$fieldModel->$properName = $propertyValue;
		}
		return $fieldModel;
	}

	public static function getDetailsForMove($fieldIdsList = array())
	{
		if ($fieldIdsList) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT fieldid, sequence, block, fieldlabel FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($fieldIdsList) . ')', $fieldIdsList);
			$numOfRows = $db->num_rows($result);

			for ($i = 0; $i < $numOfRows; $i++) {
				$blockIdsList[$db->query_result($result, $i, 'fieldid')] = array('blockId' => $db->query_result($result, $i, 'block'),
					'sequence' => $db->query_result($result, $i, 'sequence'),
					'label' => $db->query_result($result, $i, 'fieldlabel'));
			}
			return $blockIdsList;
		}
		return false;
	}

	/**
	 * Function to get all fields list for all blocks
	 * @param <Array> List of block ids
	 * @param <Vtiger_Module_Model> $moduleInstance
	 * @return <Array> List of Field models <Settings_LayoutEditor_Field_Model>
	 */
	public static function getInstanceFromBlockIdList($blockId, $moduleInstance = false)
	{
		$db = PearDatabase::getInstance();

		if (!is_array($blockId)) {
			$blockId = array($blockId);
		}

		$query = 'SELECT * FROM vtiger_field WHERE block IN(' . generateQuestionMarks($blockId) . ') AND vtiger_field.displaytype IN (1,2,4,10) ORDER BY sequence';
		$result = $db->pquery($query, $blockId);
		$numOfRows = $db->num_rows($result);

		$fieldModelsList = array();
		for ($i = 0; $i < $numOfRows; $i++) {
			$rowData = $db->query_result_rowdata($result, $i);
			//static is use to refer to the called class instead of defined class
			//http://php.net/manual/en/language.oop5.late-static-bindings.php
			$fieldModel = new self();
			$fieldModel->initialize($rowData);
			if ($moduleInstance) {
				$fieldModel->setModule($moduleInstance);
			}
			$fieldModelsList[] = $fieldModel;
		}
		return $fieldModelsList;
	}

	/**
	 * Function to get the field details
	 * @return <Array> - array of field values
	 */
	public function getFieldInfo()
	{
		$fieldInfo = parent::getFieldInfo();
		$fieldInfo['isQuickCreateDisabled'] = $this->isQuickCreateOptionDisabled();
		$fieldInfo['isSummaryField'] = $this->isSummaryField();
		$fieldInfo['isSummaryFieldDisabled'] = $this->isSummaryFieldOptionDisabled();
		$fieldInfo['isMassEditDisabled'] = $this->isMassEditOptionDisabled();
		$fieldInfo['isDefaultValueDisabled'] = $this->isDefaultValueOptionDisabled();
		return $fieldInfo;
	}

	public static function getInstanceFromFieldId($fieldId, $moduleTabId)
	{
		$db = PearDatabase::getInstance();

		if (is_string($fieldId)) {
			$fieldId = array($fieldId);
		}

		$query = 'SELECT * FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($fieldId) . ') AND tabid=?';
		$result = $db->pquery($query, array($fieldId, $moduleTabId));
		$fieldModelList = array();
		$num_rows = $db->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$fieldModel = new self();
			$fieldModel->initialize($row);
			$fieldModelList[] = $fieldModel;
		}
		return $fieldModelList;
	}
}
