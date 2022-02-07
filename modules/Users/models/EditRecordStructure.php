<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Users_EditRecordStructure_Model extends Vtiger_EditRecordStructure_Model
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
		$isOtherUser = $recordId !== App\User::getCurrentUserRealId();
		$fieldsDependency = \App\FieldsDependency::getByRecordModel($recordModel->isNew() ? 'Create' : 'Edit', $recordModel);
		$blockModelList = $this->getModule()->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if ($fieldModelList) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					$fieldModel->set('recordId', $recordId);
					if (empty($recordId) && (99 == $fieldModel->get('uitype') || 106 == $fieldModel->get('uitype'))) {
						$fieldModel->set('editable', true);
					}
					if (156 == $fieldModel->get('uitype') && true === $currentUserModel->isAdminUser() && $isOtherUser) {
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
					} elseif ('force_password_change' === $fieldName) {
						$fieldModel->set('editable', false);
					}
					if ($fieldModel->isEditable() && 'is_owner' !== $fieldName && (!$fieldsDependency['hide']['backend'] || !\in_array($fieldName, $fieldsDependency['hide']['backend']))) {
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
						if ($fieldsDependency['hide']['frontend'] && \in_array($fieldName, $fieldsDependency['hide']['frontend'])) {
							$fieldModel->set('hideField', true);
						}
						if ($fieldsDependency['mandatory'] && \in_array($fieldName, $fieldsDependency['mandatory'])) {
							$fieldModel->set('isMandatory', true);
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
		++Vtiger_Field_Model::$tabIndexLastSeq;
		return $this->structuredValues = $values;
	}
}
