<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * Mass Edit Record Structure Model.
 */
class Vtiger_MassEditRecordStructure_Model extends Vtiger_EditRecordStructure_Model
{
	/** {@inheritdoc} */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}
		$values = [];
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			foreach ($blockModel->getFields() as $fieldName => $fieldModel) {
				if ($fieldModel->isEditable() && $fieldModel->isMassEditable() && $fieldModel->isViewable() && $this->isFieldRestricted($fieldModel)) {
					if ($recordExists) {
						$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
					}
					$values[$blockLabel][$fieldName] = $fieldModel;
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}

	/**
	 * Function that return Field Restricted are not.
	 *
	 * 	@params Field Model
	 *  @returns boolean true or false
	 *
	 * @param mixed $fieldModel
	 */
	public function isFieldRestricted($fieldModel)
	{
		if ('image' == $fieldModel->getFieldDataType()) {
			return false;
		}
		return true;
	}
}
