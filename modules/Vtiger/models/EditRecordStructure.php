<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Vtiger Edit View Record Structure Model.
 */
class Vtiger_EditRecordStructure_Model extends Vtiger_RecordStructure_Model
{
	/**
	 * Function to get the values in structured format.
	 *
	 * @return array - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}
		$values = [];
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();
		$fieldsDependency = \App\FieldsDependency::getByRecordModel($recordModel->isNew() ? 'Create' : 'Edit', $recordModel);
		$blockModelList = $this->getModule()->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			if ($fieldModelList = $blockModel->getFields()) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if ($fieldModel->isEditable() && (!$fieldsDependency['hide']['backend'] || !\in_array($fieldName, $fieldsDependency['hide']['backend']))) {
						if ('' !== $recordModel->get($fieldName)) {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						} else {
							$defaultValue = $fieldModel->getDefaultFieldValue();
							if ('' !== $defaultValue && !$recordId) {
								$fieldModel->set('fieldvalue', $defaultValue);
							}
						}
						if ($fieldsDependency['hide']['frontend'] && \in_array($fieldName, $fieldsDependency['hide']['frontend'])) {
							$fieldModel->set('hideField', true);
						}
						if ($fieldsDependency['mandatory'] && \in_array($fieldName, $fieldsDependency['mandatory'])) {
							$fieldModel->set('isMandatory', true);
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
						if ($fieldModel->get('tabindex') > Vtiger_Field_Model::$tabIndexLastSeq) {
							Vtiger_Field_Model::$tabIndexLastSeq = $fieldModel->get('tabindex');
						}
					}
				}
			}
		}
		++Vtiger_Field_Model::$tabIndexLastSeq;
		return $this->structuredValues = $values;
	}
}
