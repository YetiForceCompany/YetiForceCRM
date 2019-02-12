<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o
 * *********************************************************************************** */

/**
 * Vtiger Detail View Record Structure Model.
 */
class Vtiger_DetailRecordStructure_Model extends Vtiger_RecordStructure_Model
{
	/**
	 * Function to get the fields in the header.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function getFieldInHeader()
	{
		$moduleModel = $this->getModule();
		$fieldsInHeader = [];
		$recordModel = $this->getRecord();
		foreach ($moduleModel->getFields() as $fieldName => $fieldModel) {
			if ($fieldModel->isHeaderField() && $fieldModel->isViewableInDetailView() && $recordModel) {
				$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
				$fieldsInHeader[$fieldModel->getHeaderValue('type')][$fieldModel->getName()] = $fieldModel;
			}
		}
		return $fieldsInHeader;
	}

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
		foreach ($blockModelList as $blockLabel => &$blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => &$fieldModel) {
					if ($fieldModel->isViewableInDetailView()) {
						if ($recordExists) {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
					}
				}
			}
		}
		$this->structuredValues = $values;

		return $values;
	}
}
