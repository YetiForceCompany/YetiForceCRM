<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * ********************************************************************************** */

class Users_EditRecordStructure_Model extends Vtiger_EditRecordStructure_Model
{
	/**
	 * Function to get the values in stuctured format.
	 *
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = [];
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if ($fieldModelList) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					$fieldModel->set('rocordId', $recordId);
					if (empty($recordId) && (99 == $fieldModel->get('uitype') || 106 == $fieldModel->get('uitype'))) {
						$fieldModel->set('editable', true);
					}
					if (156 == $fieldModel->get('uitype') && true === $currentUserModel->isAdminUser() && $currentUserModel->getId() !== $recordId) {
						$fieldModel->set('editable', true);
						$fieldValue = false;
						if ('on' === $recordModel->get($fieldName)) {
							$fieldValue = true;
						}
						$recordModel->set($fieldName, $fieldValue);
					}
					if ('is_owner' === $fieldName) {
						$fieldModel->set('editable', false);
					} elseif ('reports_to_id' === $fieldName && !$currentUserModel->isAdminUser()) {
						continue;
					}
					if ($fieldModel->isEditable() && 'is_owner' != $fieldName) {
						if ('' !== $recordModel->get($fieldName)) {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						} else {
							$defaultValue = $fieldModel->getDefaultFieldValue();
							if ('time_zone' === $fieldName && empty($defaultValue)) {
								$defaultValue = \App\Config::main('default_timezone');
							}
							if ('' !== $defaultValue && !$recordId) {
								$fieldModel->set('fieldvalue', $defaultValue);
							}
						}
						if (!$recordId && 99 == $fieldModel->get('uitype')) {
							$fieldModel->set('editable', true);
							$fieldModel->set('fieldvalue', '');
							$values[$blockLabel][$fieldName] = $fieldModel;
							if ($fieldModel->get('tabindex') > Vtiger_Field_Model::$tabIndexLastSeq) {
								Vtiger_Field_Model::$tabIndexLastSeq = $fieldModel->get('tabindex');
							}
						} elseif (99 != $fieldModel->get('uitype')) {
							$values[$blockLabel][$fieldName] = $fieldModel;
							if ($fieldModel->get('tabindex') > Vtiger_Field_Model::$tabIndexLastSeq) {
								Vtiger_Field_Model::$tabIndexLastSeq = $fieldModel->get('tabindex');
							}
						}
					}
				}
			}
		}
		$this->structuredValues = $values;
		++Vtiger_Field_Model::$tabIndexLastSeq;
		return $values;
	}
}
