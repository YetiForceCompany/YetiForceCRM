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
	 * Function to remove field.
	 */
	public function delete()
	{
		$db = \App\Db::getInstance();
		parent::delete();

		$fldModule = $this->getModuleName();
		$id = $this->getId();
		$fieldname = $this->getName();
		$tablename = $this->get('table');
		$columnName = $this->get('column');
		$tabId = $this->getModuleId();
		if ($tablename !== 'vtiger_crmentity') {
			$db->createCommand()->dropColumn($tablename, $columnName)->execute();
		}
		App\Db::getInstance('admin')->createCommand()->delete('a_#__mapped_fields', ['or', ['source' => $id], ['target' => $id]])->execute();
		//we have to remove the entries in customview and report related tables which have this field ($colName)
		$db->createCommand()->delete('vtiger_cvcolumnlist', ['field_name' => $fieldname, 'module_name' => $fldModule])->execute();
		$db->createCommand()->delete('u_#__cv_condition', ['field_name' => $fieldname, 'module_name' => $fldModule])->execute();
		//Deleting from convert lead mapping vtiger_table- Jaguar
		if ($fldModule === 'Leads') {
			$db->createCommand()->delete('vtiger_convertleadmapping', ['leadfid' => $id])->execute();
		} elseif ($fldModule == 'Accounts') {
			$mapDelId = ['Accounts' => 'accountfid'];
			$db->createCommand()->update('vtiger_convertleadmapping', [$mapDelId[$fldModule] => 0], [$mapDelId[$fldModule] => $id])->execute();
		}

		//HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
		if ($this->getFieldDataType() === 'picklist' || $this->getFieldDataType() === 'multipicklist') {
			$query = (new \App\Db\Query())->from('vtiger_field')
				->where(['fieldname' => $fieldname])
				->andWhere(['in', 'uitype', [15, 16, 33]]);
			$dataReader = $query->createCommand()->query();
			if (!$dataReader->count()) {
				$db->createCommand()->dropTable('vtiger_' . $fieldname)->execute();
				//To Delete Sequence Table
				if ($db->isTableExists('vtiger_' . $fieldname . '_seq')) {
					$db->createCommand()->dropTable('vtiger_' . $fieldname . '_seq')->execute();
				}
				$db->createCommand()->delete('vtiger_picklist', ['name' => $fieldname])->execute();
			}
			$db->createCommand()->delete('vtiger_picklist_dependency', ['and', ['tabid' => $tabId], ['or', ['sourcefield' => $fieldname], ['targetfield' => $fieldname]]])->execute();
		}

		//MultiReferenceValue
		if ($this->getUIType() === 305) {
			$fieldParams = \App\Json::decode($this->get('fieldparams'));
			$db->createCommand()->delete('s_#__multireference', ['source_module' => $fldModule, 'dest_module' => $fieldParams['module']])->execute();
		}
	}

	/**
	 * Function to Move the field.
	 *
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
			} elseif ($newSequence < $olderSequence) {
				$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence + 1')], ['and', 'sequence < :olderSequence', 'sequence >= :newSequence', 'block = :olderBlockId'], [':olderSequence' => $olderSequence, ':newSequence' => $newSequence, ':olderBlockId' => $olderBlockId])->execute();
			}
			$db->createCommand()->update('vtiger_field', ['sequence' => $newSequence], ['fieldid' => $this->getId()])->execute();
		} else {
			$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence - 1')], ['and', 'sequence > :olderSequence', 'block = :olderBlockId'], [':olderSequence' => $olderSequence, ':olderBlockId' => $olderBlockId])->execute();
			$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence - 1')], ['and', 'sequence >= :newSequence', 'block = :newBlockId'], [':newSequence' => $newSequence, ':newBlockId' => $newBlockId])->execute();

			$db->createCommand()->update('vtiger_field', ['sequence' => $newSequence, 'block' => $newBlockId], ['fieldid' => $this->getId()])->execute();
		}
	}

	/**
	 * Function to activate field.
	 *
	 * @param int[] $fieldIdsList
	 * @param int   $blockId
	 */
	public static function makeFieldActive($fieldIdsList, $blockId)
	{
		$maxSequence = (new \App\Db\Query())->from('vtiger_field')->where(['block' => $blockId, 'presence' => [0, 2]])->max('sequence');
		$db = \App\Db::getInstance();
		$caseExpression = 'CASE';
		foreach ($fieldIdsList as $fieldId) {
			$caseExpression .= " WHEN fieldid = {$db->quoteValue($fieldId)} THEN {$db->quoteValue($maxSequence + 1)}";
		}
		$caseExpression .= ' ELSE sequence END';
		$db->createCommand()
			->update('vtiger_field', [
				'presence' => 2,
				'sequence' => new \yii\db\Expression($caseExpression),
			], ['fieldid' => $fieldIdsList])->execute();
	}

	/**
	 * Function which specifies whether the field can have mandatory switch to happen.
	 *
	 * @return bool - true if we can make a field mandatory and non mandatory , false if we cant change previous state
	 */
	public function isMandatoryOptionDisabled()
	{
		$moduleModel = $this->getModule();
		$complusoryMandatoryFieldList = $moduleModel->getCumplosoryMandatoryFieldList();
		//uitypes for which mandatory switch is disabled
		$mandatoryRestrictedUitypes = ['4', '70'];
		if (in_array($this->getName(), $complusoryMandatoryFieldList)) {
			return true;
		}
		if (in_array($this->get('uitype'), $mandatoryRestrictedUitypes)) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the active option is disabled.
	 *
	 * @return bool
	 */
	public function isActiveOptionDisabled()
	{
		if ($this->get('presence') == 0 || $this->get('uitype') == 306 || $this->isMandatoryOptionDisabled()) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the quickcreate option is disabled.
	 *
	 * @return bool
	 */
	public function isQuickCreateOptionDisabled()
	{
		$moduleModel = $this->getModule();
		if ($this->get('quickcreate') == 0 || $this->get('quickcreate') == 3 || !$moduleModel->isQuickCreateSupported()) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the mass edit option is disabled.
	 *
	 * @return bool
	 */
	public function isMassEditOptionDisabled()
	{
		if ($this->get('masseditable') == 0 || $this->get('displaytype') != 1 || $this->get('masseditable') == 3) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the default value option is disabled.
	 *
	 * @return bool
	 */
	public function isDefaultValueOptionDisabled()
	{
		if ($this->isMandatoryOptionDisabled() || $this->isReferenceField() || $this->getFieldDataType() === 'image' || $this->getFieldDataType() === 'multiImage') {
			return true;
		}
		return false;
	}

	/**
	 * Function to check whether summary field option is disable or not.
	 *
	 * @return bool true/false
	 */
	public function isSummaryFieldOptionDisabled()
	{
		return $this->get('uitype') === 70;
	}

	/**
	 * Function to check field is editable or not.
	 *
	 * @return bool true/false
	 */
	public function isEditable()
	{
		return !('Calendar' === $this->block->module->name && $this->isActiveOptionDisabled());
	}

	/**
	 * Function to get instance.
	 *
	 * @param string $value  - fieldname or fieldid
	 * @param <type> $module - optional - module instance
	 *
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
	 * Function to get all fields list for all blocks.
	 *
	 * @param array List of block ids
	 * @param Vtiger_Module_Model $moduleInstance
	 *
	 * @return array List of Field models Settings_LayoutEditor_Field_Model
	 */
	public static function getInstanceFromBlockIdList($blockId, $moduleInstance = false)
	{
		if (!is_array($blockId)) {
			$blockId = [$blockId];
		}
		$query = (new \App\Db\Query())->from('vtiger_field')->where(['block' => $blockId])->orderBy('sequence');
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
		$dataReader->close();

		return $fieldModelsList;
	}

	/**
	 * Function to get the field details.
	 *
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
}
