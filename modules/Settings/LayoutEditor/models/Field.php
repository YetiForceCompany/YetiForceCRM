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

class Settings_LayoutEditor_Field_Model extends Vtiger_Field_Model
{

	/**
	 * Function to remove field
	 */
	public function delete()
	{
		$db = \App\Db::getInstance();
		parent::delete();

		$fldModule = $this->getModuleName();
		$id = $this->getId();
		$uitype = $this->get('uitype');
		$typeofdata = $this->get('typeofdata');
		$fieldname = $this->getName();
		$oldfieldlabel = $this->get('label');
		$tablename = $this->get('table');
		$columnName = $this->get('column');
		$fieldtype = explode("~", $typeofdata);
		$tabId = $this->getModuleId();

		$focus = CRMEntity::getInstance($fldModule);

		$deleteColumnName = $tablename . ":" . $columnName . ":" . $fieldname . ":" . $fldModule . "_" . str_replace(" ", "_", $oldfieldlabel) . ":" . $fieldtype[0];
		$columnCvstdfilter = $tablename . ":" . $columnName . ":" . $fieldname . ":" . $fldModule . "_" . str_replace(" ", "_", $oldfieldlabel);
		$selectColumnname = $tablename . ":" . $columnName . ":" . $fldModule . "_" . str_replace(" ", "_", $oldfieldlabel) . ":" . $fieldname . ":" . $fieldtype[0];
		$reportsummaryColumn = $tablename . ":" . $columnName . ":" . str_replace(" ", "_", $oldfieldlabel);
		if ($tablename != 'vtiger_crmentity') {
			$db->createCommand()->dropColumn($tablename, $columnName)->execute();
		}
		//we have to remove the entries in customview and report related tables which have this field ($colName)
		$db->createCommand()->delete('vtiger_cvcolumnlist', ['columnname' => $deleteColumnName])->execute();
		$db->createCommand()->delete('vtiger_cvstdfilter', ['columnname' => $columnCvstdfilter])->execute();
		$db->createCommand()->delete('vtiger_cvadvfilter', ['columnname' => $deleteColumnName])->execute();
		$db->createCommand()->delete('vtiger_selectcolumn', ['columnname' => $selectColumnname])->execute();
		$db->createCommand()->delete('vtiger_relcriteria', ['columnname' => $selectColumnname])->execute();
		$db->createCommand()->delete('vtiger_reportsortcol', ['columnname' => $selectColumnname])->execute();
		$db->createCommand()->delete('vtiger_reportdatefilter', ['datecolumnname' => $columnCvstdfilter])->execute();
		$db->createCommand()->delete('vtiger_reportsummary', ['like', 'columnname', $reportsummaryColumn])->execute();
		//Deleting from convert lead mapping vtiger_table- Jaguar
		if ($fldModule == 'Leads') {
			$db->createCommand()->delete('vtiger_convertleadmapping', ['leadfid' => $id])->execute();
		} elseif ($fldModule == 'Accounts') {
			$mapDelId = ['Accounts' => 'accountfid'];
			$db->createCommand()->update('vtiger_convertleadmapping', [$mapDelId[$fldModule] => 0], [$mapDelId[$fldModule] => $id])->execute();
		}

		//HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
		if ($this->getFieldDataType() == 'picklist' || $this->getFieldDataType() == 'multipicklist') {
			$query = (new \App\Db\Query())->from('vtiger_field')
				->where(['columnname' => $columnName])
				->andWhere(['in', 'uitype', [15, 16, 33]]);
			$dataReader = $query->createCommand()->query();
			if (!$dataReader->count()) {
				$db->createCommand()->dropTable('vtiger_' . $columnName)->execute();
				//To Delete Sequence Table 
				if ($db->isTableExists('vtiger_' . $columnName . '_seq')) {
					$db->createCommand()->dropTable('vtiger_' . $columnName . '_seq')->execute();
				}
				$db->createCommand()->delete('vtiger_picklist', ['name' => $columnName]);
			}
			$db->createCommand()->delete('vtiger_picklist_dependency', ['and', "tabid = $tabId", ['or', "sourcefield = '$columnname'", "targetfield = '$columnname'"]])->execute();
		}
	}

	/**
	 * Function to Move the field
	 * @param <Array> $fieldNewDetails
	 * @param <Array> $fieldOlderDetails
	 */
	public function move($fieldNewDetails, $fieldOlderDetails)
	{
		$db = \App\Db::getInstance();
		$newBlockId = $fieldNewDetails['blockId'];
		$olderBlockId = $fieldOlderDetails['blockId'];

		$newSequence = $fieldNewDetails['sequence'];
		$olderSequence = $fieldOlderDetails['sequence'];

		if ($olderBlockId == $newBlockId) {
			if ($newSequence > $olderSequence) {
				$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence - 1')], ['and', 'sequence > :olderSequence', 'sequence <= :newSequence', 'block = :olderBlockId'], [':olderSequence' => $olderSequence, ':newSequence' => $newSequence, ':olderBlockId' => $olderBlockId])->execute();
			} else if ($newSequence < $olderSequence) {
				$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence + 1')], ['and', 'sequence < :olderSequence', 'sequence >= :newSequence', 'block = :olderBlockId'], [':olderSequence' => $olderSequence, ':newSequence' => $newSequence, ':olderBlockId' => $olderBlockId])->execute();
			}
			$db->createCommand()->update('vtiger_field', ['sequence' => $newSequence], ['fieldid' => $this->getId()])->execute();
		} else {
			$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence - 1')], ['and', 'sequence > :olderSequence', 'block = :olderBlockId'], [':olderSequence' => $olderSequence, ':olderBlockId' => $olderBlockId])->execute();
			$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence - 1')], ['and', 'sequence >= :newSequence', 'block = :newBlockId'], [':newSequence' => $newSequence, ':newBlockId' => $newBlockId])->execute();

			$db->createCommand()->update('vtiger_field', ['sequence' => $newSequence, 'block' => $newBlockId], ['fieldid' => $this->getId()])->execute();
		}
	}

	public static function makeFieldActive($fieldIdsList = array(), $blockId)
	{
		$maxSequence = (new \App\Db\Query())->from('vtiger_field')->where(['block' => $blockId, 'presence' => [0, 2]])->max('sequence');

		$caseExpression = 'CASE';
		foreach ($fieldIdsList as $fieldId) {
			$caseExpression .= " WHEN fieldid = $fieldId THEN " . ($maxSequence + 1);
		}
		$caseExpression .= ' ELSE sequence END';
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', [
				'presence' => 2,
				'sequence' => new \yii\db\Expression($caseExpression),
				], ['fieldid' => $fieldIdsList])->execute();
	}

	/**
	 * Function which specifies whether the field can have mandatory switch to happen
	 * @return boolean - true if we can make a field mandatory and non mandatory , false if we cant change previous state
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
		if (in_array($this->get('uitype'), $mandatoryRestrictedUitypes) || ($this->get('displaytype') == 2 && $this->get('uitype') != 306)) {
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
		if ($this->get('presence') == 0 || $this->get('uitype') == 306 || $this->isMandatoryOptionDisabled()) {
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
		if ($this->isMandatoryOptionDisabled() || $this->isReferenceField() || $this->get('uitype') == 69) {
			return true;
		}
		return false;
	}

	/**
	 * Function to check whether summary field option is disable or not
	 * @return boolean true/false
	 */
	public function isSummaryFieldOptionDisabled()
	{
		return $this->get('uitype') === 70;
	}

	/**
	 * Function to check field is editable or not
	 * @return boolean true/false
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
	 * @param string $value - fieldname or fieldid
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

	/**
	 * Function to get all fields list for all blocks
	 * @param array List of block ids
	 * @param Vtiger_Module_Model $moduleInstance
	 * @return array List of Field models Settings_LayoutEditor_Field_Model
	 */
	public static function getInstanceFromBlockIdList($blockId, $moduleInstance = false)
	{
		if (!is_array($blockId)) {
			$blockId = [$blockId];
		}
		$query = (new \App\Db\Query())->from('vtiger_field')->where(['block' => $blockId, 'displaytype' => [1, 2, 4, 5, 9, 10]])->orderBy('sequence');
		$dataReader = $query->createCommand()->query();
		$fieldModelsList = [];
		while ($row = $dataReader->read()) {
			$fieldModel = new self();
			$fieldModel->initialize($row);
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

	public static function getInstanceFromFieldId($fieldId, $moduleTabId = false)
	{
		if (is_string($fieldId)) {
			$fieldId = [$fieldId];
		}
		$query = (new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $moduleTabId, 'fieldid' => $fieldId])->orderBy('sequence');
		$dataReader = $query->createCommand()->query();
		$fieldModelList = [];
		while ($row = $dataReader->read()) {
			$fieldModel = new self();
			$fieldModel->initialize($row);
			$fieldModelList[] = $fieldModel;
		}
		return $fieldModelList;
	}
}
