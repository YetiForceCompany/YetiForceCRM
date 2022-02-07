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

	/**
	 * Function to get the values in structured format.
	 *
	 * @return array values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}
		$values = [];
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		if ($recordExists) {
			$fieldsDependency = \App\FieldsDependency::getByRecordModel('Detail', $recordModel);
		}
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => &$blockModel) {
			$fieldModelList = $blockModel->getFields();
			if ($fieldModelList) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => &$fieldModel) {
					if ($fieldModel->isViewableInDetailView() && (empty($fieldsDependency['hide']['backend']) || !\in_array($fieldName, $fieldsDependency['hide']['backend']))) {
						if ($recordExists) {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						}
						if (!empty($fieldsDependency['hide']['frontend']) && \in_array($fieldName, $fieldsDependency['hide']['frontend'])) {
							$fieldModel->set('hideField', true);
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
					}
				}
			}
		}
		return $this->structuredValues = $values;
	}
}
