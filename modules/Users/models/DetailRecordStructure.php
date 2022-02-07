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

class Users_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model
{
	/** {@inheritdoc} */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}
		$values = [];
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();
		$fieldsDependency = \App\FieldsDependency::getByRecordModel('Detail', $recordModel);
		$blockModelList = $this->getModule()->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					$fieldModel->set('recordId', $recordId);
					if (156 == $fieldModel->get('uitype') && true === $currentUserModel->isAdminUser()) {
						$fieldModel->set('editable', $currentUserModel->getId() !== $recordId);
						$fieldValue = false;
						if ('on' === $recordModel->get($fieldName) || true === $recordModel->get($fieldName)) {
							$fieldValue = true;
						}
						$recordModel->set($fieldName, $fieldValue);
					}
					if ($fieldModel->isViewableInDetailView() && (empty($fieldsDependency['hide']['backend']) || !\in_array($fieldName, $fieldsDependency['hide']['backend']))) {
						if ($recordId) {
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
