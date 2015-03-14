<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/

class Users_EditRecordStructure_Model extends Vtiger_EditRecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if(!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if ($fieldModelList) {
				$values[$blockLabel] = array();
				foreach($fieldModelList as $fieldName => $fieldModel) {
					if($fieldModel->get('uitype') == 115 && $currentUserModel->isAdminUser() == true) {
						$fieldModel->set('editable', true);
					}
					if(empty($recordId) && ($fieldModel->get('uitype') == 99 || $fieldModel->get('uitype') == 106)) {
							$fieldModel->set('editable', true);
					}
					//Is Admin field is editable when the record user != current user
					if (in_array($fieldModel->get('uitype'), array(156)) && $currentUserModel->getId() !== $recordId) {
						$fieldModel->set('editable', true);
						if ($fieldModel->get('uitype') == 156) {
							$fieldValue = false;
							$defaultValue = $fieldModel->getDefaultFieldValue();
							if ($recordModel->get($fieldName) === 'on') {
								$fieldValue = true;
							}
							$recordModel->set($fieldName, $fieldValue);
						}
					}
					if($fieldModel->isEditable()) {
						if($recordModel->get($fieldName) != '') {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						} else {
							$defaultValue = $fieldModel->getDefaultFieldValue();
							if($fieldName == 'time_zone' && empty($defaultValue))
								$defaultValue = vglobal('default_timezone');
							if(!empty($defaultValue) && !$recordId)
								$fieldModel->set('fieldvalue', $defaultValue);
						}
						
						if(!$recordId && $fieldModel->get('uitype') == 99) {
							$fieldModel->set('editable', true);
							$fieldModel->set('fieldvalue', '');
							$values[$blockLabel][$fieldName] = $fieldModel;
						} else if($fieldModel->get('uitype') != 99){
							$values[$blockLabel][$fieldName] = $fieldModel;
						}
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}
}